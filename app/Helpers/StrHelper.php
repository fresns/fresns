<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use Illuminate\Support\Str;

class StrHelper
{
    /**
     * @param  string  $email
     * @return string
     */
    public static function maskEmail(string $email)
    {
        if (! $email) {
            return;
        }

        $user = strstr($email, '@', true);
        $domain = strstr($email, '@');

        $len = mb_strlen($user);

        $mask = match (true) {
            default => str_repeat('*', 3),
            $len > 3 => str_repeat('*', bcsub($len, 3)),
        };

        $offset = match (true) {
            default => 1,
            $len > 3 => 3,
        };

        $maskUser = substr_replace($user, $mask, $offset);

        return "{$maskUser}{$domain}";
    }

    /**
     * @param  int  $number
     * @return mixed
     */
    public static function maskNumber(int $number)
    {
        $head = substr($number, 0, 2);
        $tail = substr($number, -2);
        $starCount = strlen($number) - 4;
        $star = str_repeat('*', $starCount);

        return $head.$star.$tail;
    }

    /**
     * @param  string  $name
     * @return string
     */
    public static function maskName(string $name)
    {
        $len = mb_strlen($name);
        if ($len < 1) {
            return $name;
        }
        $last = mb_substr($name, -1, 1);
        $lastName = str_repeat('*', $len - 1);

        return $lastName.$last;
    }

    /**
     * @param  int  $length
     * @return int
     */
    public static function generateDigital(int $length = 6)
    {
        return rand(pow(10, ($length - 1)), pow(10, $length) - 1);
    }

    /**
     * @param  string  $string
     */
    public static function stringToUtf8(?string $string = null)
    {
        if (empty($string)) {
            return $string;
        }

        $encoding_list = [
            'ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5',
        ];

        $encode = mb_detect_encoding($string, $encoding_list);

        $string = mb_convert_encoding($string, 'UTF-8', $encode);

        return $string;
    }

    /**
     * @param  string  $uri
     * @param  string  $domain
     */
    public static function qualifyUrl(?string $uri = null, ?string $domain = null)
    {
        if (! $uri) {
            return '';
        }

        if (str_contains($uri, '://')) {
            return $uri;
        }

        if (! $domain) {
            $defaultDomain = ConfigHelper::fresnsConfigByItemKey('backend_domain');

            return sprintf('%s/%s', $defaultDomain, ltrim($uri, '/'));
        }

        return sprintf('%s/%s', rtrim($domain, '/'), ltrim($uri, '/'));
    }

    /**
     * It takes a hostname as a string and returns the domain name as a string.
     *
     * @param string host The hostname you want to get the domain from.
     * @return The domain name of the host.
     */
    public static function extractDomainByHost(string $host)
    {
        if ($host == 'localhost') {
            // localhost
            return 'localhost';
        } elseif (filter_var($host, FILTER_VALIDATE_IP)) {
            // IPv4 and IPv6
            return $host;
        }

        $ianaRoot = [
            // gTLDs
            'com', 'net', 'org', 'edu', 'gov', 'int', 'mil', 'arpa', 'biz', 'info', 'pro', 'name', 'coop', 'travel', 'xxx', 'idv', 'aero', 'museum', 'mobi', 'asia', 'tel', 'post', 'jobs', 'cat',
            // ccTLDs
            'ad', 'ae', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao', 'aq', 'ar', 'as', 'at', 'au', 'aw', 'az', 'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'bj', 'bm', 'bn', 'bo', 'br', 'bs', 'bt', 'bv', 'bw', 'by', 'bz', 'ca', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'cr', 'cu', 'cv', 'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'ee', 'eg', 'eh', 'er', 'es', 'et', 'eu', 'fi', 'fj', 'fk', 'fm', 'fo', 'fr', 'ga', 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu', 'gw', 'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im', 'in', 'io', 'iq', 'ir', 'is', 'it', 'je', 'jm', 'jo', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr', 'kw', 'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'ma', 'mc', 'md', 'me', 'mg', 'mh', 'mk', 'ml', 'mm', 'mn', 'mo', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'nc', 'ne', 'nf', 'ng', 'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz', 'om', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pm', 'pn', 'pr', 'ps', 'pt', 'pw', 'py', 'qa', 're', 'ro', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sj', 'sk', 'sm', 'sn', 'so', 'sr', 'st', 'sv', 'sy', 'sz', 'tc', 'td', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tp', 'tr', 'tt', 'tv', 'tw', 'tz', 'ua', 'ug', 'uk', 'um', 'us', 'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'ye', 'yt', 'yu', 'yr', 'za', 'zm', 'zw',
            // new gTLDs (Business)
            'accountant', 'club', 'coach', 'college', 'company', 'construction', 'consulting', 'contractors', 'cooking', 'corp', 'credit', 'creditcard', 'dance', 'dealer', 'democrat', 'dental', 'dentist', 'design', 'diamonds', 'direct', 'doctor', 'drive', 'eco', 'education', 'energy', 'engineer', 'engineering', 'equipment', 'events', 'exchange', 'expert', 'express', 'faith', 'farm', 'farmers', 'fashion', 'finance', 'financial', 'fish', 'fit', 'fitness', 'flights', 'florist', 'flowers', 'food', 'football', 'forsale', 'furniture', 'game', 'games', 'garden', 'gmbh', 'golf', 'health', 'healthcare', 'hockey', 'holdings', 'holiday', 'home', 'hospital', 'hotel', 'hotels', 'house', 'inc', 'industries', 'insurance', 'insure', 'investments', 'islam', 'jewelry', 'justforu', 'kid', 'kids', 'law', 'lawyer', 'legal', 'lighting', 'limited', 'live', 'llc', 'llp', 'loft', 'ltd', 'ltda', 'managment', 'marketing', 'media', 'medical', 'men', 'money', 'mortgage', 'moto', 'motorcycles', 'music', 'mutualfunds', 'ngo', 'partners', 'party', 'pharmacy', 'photo', 'photography', 'photos', 'physio', 'pizza', 'plumbing', 'press', 'prod', 'productions', 'radio', 'rehab', 'rent', 'repair', 'report', 'republican', 'restaurant', 'room', 'rugby', 'safe', 'sale', 'sarl', 'save', 'school', 'secure', 'security', 'services', 'shoes', 'show', 'soccer', 'spa', 'sport', 'sports', 'spot', 'srl', 'storage', 'studio', 'tattoo', 'taxi', 'team', 'tech', 'technology', 'thai', 'tips', 'tour', 'tours', 'toys', 'trade', 'trading', 'travelers', 'university', 'vacations', 'ventures', 'versicherung', 'versicherung', 'vet', 'wedding', 'wine', 'winners', 'work', 'works', 'yachts', 'zone',
            // new gTLDs (Construction & Real Estate)
            'archi', 'architect', 'casa', 'contruction', 'estate', 'haus', 'house', 'immo', 'immobilien', 'lighting', 'loft', 'mls', 'realty',
            // new gTLDs (Community & Religion)
            'academy', 'arab', 'bible', 'care', 'catholic', 'charity', 'christmas', 'church', 'college', 'community', 'contact', 'degree', 'education', 'faith', 'foundation', 'gay', 'halal', 'hiv', 'indiands', 'institute', 'irish', 'islam', 'kiwi', 'latino', 'mba', 'meet', 'memorial', 'ngo', 'phd', 'prof', 'school', 'schule', 'science', 'singles', 'social', 'swiss', 'thai', 'trust', 'university', 'uno',
            // new gTLDs (E-commerce & Shopping)
            'auction', 'best', 'bid', 'boutique', 'center', 'cheap', 'compare', 'coupon', 'coupons', 'deal', 'deals', 'diamonds', 'discount', 'fashion', 'forsale', 'free', 'gift', 'gold', 'gratis', 'hot', 'jewelry', 'kaufen', 'luxe', 'luxury', 'market', 'moda', 'pay', 'promo', 'qpon', 'review', 'reviews', 'rocks', 'sale', 'shoes', 'shop', 'shopping', 'store', 'tienda', 'top', 'toys', 'watch', 'zero',
            // new gTLDs (Dining)
            'bar', 'bio', 'cafe', 'catering', 'coffee', 'cooking', 'diet', 'eat', 'food', 'kitchen', 'menu', 'organic', 'pizza', 'pub', 'rest', 'restaurant', 'vodka', 'wine',
            // new gTLDs (Travel)
            'abudhabi', 'africa', 'alsace', 'amsterdam', 'barcelona', 'bayern', 'berlin', 'boats', 'booking', 'boston', 'brussels', 'budapest', 'caravan', 'casa', 'catalonia', 'city', 'club', 'cologne', 'corsica', 'country', 'cruise', 'cruises', 'deal', 'deals', 'doha', 'dubai', 'durban', 'earth', 'flights', 'fly', 'fun', 'gent', 'guide', 'hamburg', 'helsinki', 'holiday', 'hotel', 'hoteles', 'hotels', 'ist', 'istanbul', 'joburg', 'koeln', 'land', 'london', 'madrid', 'map', 'melbourne', 'miami', 'moscow', 'nagoya', 'nrw', 'nyc', 'osaka', 'paris', 'party', 'persiangulf', 'place', 'quebec', 'reise', 'reisen', 'rio', 'roma', 'room', 'ruhr', 'saarland', 'stockholm', 'swiss', 'sydney', 'taipei', 'tickets', 'tirol', 'tokyo', 'tour', 'tours', 'town', 'travelers', 'vacations', 'vegas', 'wales', 'wien', 'world', 'yokohama', 'zuerich',
            // new gTLDs (Sports & Hobbies)
            'art', 'auto', 'autos', 'baby', 'band', 'baseball', 'beats', 'beauty', 'beknown', 'bike', 'book', 'boutique', 'broadway', 'car', 'cars', 'club', 'coach', 'contact', 'cool', 'cricket', 'dad', 'dance', 'date', 'dating', 'design', 'dog', 'events', 'family', 'fan', 'fans', 'fashion', 'film', 'final', 'fishing', 'football', 'fun', 'furniture', 'futbol', 'gallery', 'game', 'games', 'garden', 'gay', 'golf', 'guru', 'hair', 'hiphop', 'hockey', 'home', 'horse', 'icu', 'joy', 'kid', 'kids', 'life', 'lifestyle', 'like', 'living', 'lol', 'makeup', 'meet', 'men', 'moda', 'moi', 'mom', 'movie', 'movistar', 'music', 'party', 'pet', 'pets', 'photo', 'photography', 'photos', 'pics', 'pictures', 'play', 'poker', 'rodeo', 'rugby', 'run', 'salon', 'singles', 'ski', 'skin', 'smile', 'soccer', 'social', 'song', 'soy', 'sport', 'sports', 'star', 'style', 'surf', 'tatoo', 'tennis', 'theater', 'theatre', 'tunes', 'vip', 'wed', 'wedding', 'winwinners', 'yoga', 'you',
            // new gTLDs (Network Technology)
            'analytics', 'antivirus', 'app', 'blog', 'call', 'camera', 'channel', 'chat', 'click', 'cloud', 'computer', 'contact', 'data', 'dev', 'digital', 'direct', 'docs', 'domains', 'dot', 'download', 'email', 'foo', 'forum', 'graphics', 'guide', 'help', 'home', 'host', 'hosting', 'idn', 'link', 'lol', 'mail', 'mobile', 'network', 'online', 'open', 'page', 'phone', 'pin', 'search', 'site', 'software', 'webcam',
            // new gTLDs (Other)
            'airforce', 'army', 'black', 'blue', 'box', 'buzz', 'casa', 'cool', 'day', 'discover', 'donuts', 'exposed', 'fast', 'finish', 'fire', 'fyi', 'global', 'green', 'help', 'here', 'how', 'international', 'ira', 'jetzt', 'jot', 'like', 'live', 'kim', 'navy', 'new', 'news', 'next', 'ninja', 'now', 'one', 'ooo', 'pink', 'plus', 'red', 'solar', 'tips', 'today', 'weather', 'wow', 'wtf', 'xyz', 'abogado', 'adult', 'anquan', 'aquitaine', 'attorney', 'audible', 'autoinsurance', 'banque', 'bargains', 'bcn', 'beer', 'bet', 'bingo', 'blackfriday', 'bom', 'boo', 'bot', 'broker', 'builders', 'business', 'bzh', 'cab', 'cal', 'cam', 'camp', 'cancerresearch', 'capetown', 'carinsurance', 'casino', 'ceo', 'cfp', 'circle', 'claims', 'cleaning', 'clothing', 'codes', 'condos', 'connectors', 'courses', 'cpa', 'cymru', 'dds', 'delivery', 'desi', 'directory', 'diy', 'dvr', 'ecom', 'enterprises', 'esq', 'eus', 'fail', 'feedback', 'financialaid', 'frontdoor', 'fund', 'gal', 'gifts', 'gives', 'giving', 'glass', 'gop', 'got', 'gripe', 'grocery', 'group', 'guitars', 'hangout', 'homegoods', 'homes', 'homesense', 'hotels', 'ing', 'ink', 'juegos', 'kinder', 'kosher', 'kyoto', 'lat', 'lease', 'lgbt', 'liason', 'loan', 'loans', 'locker', 'lotto', 'love', 'maison', 'markets', 'matrix', 'meme', 'mov', 'okinawa', 'ong', 'onl', 'origins', 'parts', 'patch', 'pid', 'ping', 'porn', 'progressive', 'properties', 'property', 'protection', 'racing', 'read', 'realestate', 'realtor', 'recipes', 'rentals', 'sex', 'sexy', 'shopyourway', 'shouji', 'silk', 'solutions', 'stroke', 'study', 'sucks', 'supplies', 'supply', 'tax', 'tires', 'total', 'training', 'translations', 'travelersinsurcance', 'ventures', 'viajes', 'villas', 'vin', 'vivo', 'voyage', 'vuelos', 'wang', 'watches',
        ];

        $domainPartData = explode('.', $host);

        $reverseDomainData = array_reverse($domainPartData);

        $suffixDomainData = [$reverseDomainData[0], $reverseDomainData[1]];

        $count = 0;
        foreach ($suffixDomainData as $part) {
            foreach ($ianaRoot as $value) {
                if ($value === $part) {
                    $count++;
                }
            }
        }

        $domain = match ($count) {
            1 => implode('.', array_reverse([$reverseDomainData[0], $reverseDomainData[1]])),
            2 => implode('.', array_reverse([$reverseDomainData[0], $reverseDomainData[1], $reverseDomainData[2]])),
        };

        return $domain ?? 'Unknown Error';
    }

    public static function extractDomainByUrl(string $url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $domain = self::extractDomainByHost($host);

        return $domain ?? 'Unknown Error';
    }

    public static function slug(string $string)
    {
        $text = StrHelper::stringToUtf8($string);

        if (preg_match("/^[A-Za-z\s]+$/", $text)) {
            $slug = Str::slug($text, '-');
        } else {
            $slug = rawurlencode($text);
        }

        return $slug;
    }
}
