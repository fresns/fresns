<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\AppHelper;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\ArchiveUsage;
use App\Models\City;
use App\Models\Comment;
use App\Models\CommentLog;
use App\Models\Domain;
use App\Models\DomainLink;
use App\Models\DomainLinkUsage;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Geotag;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\HashtagUsage;
use App\Models\Mention;
use App\Models\OperationUsage;
use App\Models\Post;
use App\Models\PostLog;
use App\Models\Sticker;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentUtility
{
    // preg regexp
    public static function getRegexpByType($type): string
    {
        $hashtagRegexp = ConfigHelper::fresnsConfigByItemKey('hashtag_regexp');

        // Validate regex patterns
        $spacePattern = ValidationUtility::regexp($hashtagRegexp['space']) ? $hashtagRegexp['space'] : '/#[\p{L}\p{N}\p{M}]+[^\n\p{P}\s]/u';
        $hashPattern = ValidationUtility::regexp($hashtagRegexp['hash']) ? $hashtagRegexp['hash'] : '/#[\p{L}\p{N}\p{M}]+[^\n\p{P}]#/u';

        return match ($type) {
            'space' => $spacePattern,
            'hash' => $hashPattern,
            'url' => '/\b(https?:\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])\b/i',
            'at' => '/@(.*?)\s/',
            'sticker' => '/\[(.*?)\]/',
            'file' => '/\[file:(\w+)\]/',
        };
    }

    // match all extract
    public static function matchAll(string $regexp, string $content, ?int $arrayKey = 1): array
    {
        // Matching information is handled at the end
        $content = $content.' ';

        preg_match_all($regexp, $content, $matches);

        return $matches[$arrayKey] ?? [];
    }

    // str hashtag
    public static function strHashtag(array $data): array
    {
        $data = array_filter($data);

        $result = [];
        foreach ($data as $item) {
            $item = Str::replace(' ', '', $item);
            $item = Str::replace('#', '', $item);

            // hashtag only support 20 lengths
            $length = Str::length($item);
            $hashtagLength = ConfigHelper::fresnsConfigByItemKey('hashtag_length') ?? 20;
            if ($length > $hashtagLength) {
                continue;
            }

            $result[] = $item;
        }

        return $result;
    }

    // Extract all hashtag
    public static function extractAllHashtag(string $content): array
    {
        $newContent = preg_replace_callback(ContentUtility::getRegexpByType('url'), function ($matches) {
            return '';
        }, $content);

        $hashData = ContentUtility::strHashtag(
            ContentUtility::matchAll(ContentUtility::getRegexpByType('hash'), $newContent, 0)
        );

        $spaceData = ContentUtility::strHashtag(
            ContentUtility::matchAll(ContentUtility::getRegexpByType('space'), $newContent, 0)
        );

        // De-duplication of the extracted hashtag
        $data = array_unique([...$spaceData, ...$hashData]);

        return $data;
    }

    // Extract config hashtag
    public static function extractConfigHashtag(string $content): array
    {
        $newContent = preg_replace_callback(ContentUtility::getRegexpByType('url'), function ($matches) {
            return '';
        }, $content);

        $config = ConfigHelper::fresnsConfigByItemKey('hashtag_format');
        $regexp = ($config == 1) ? ContentUtility::getRegexpByType('space') : ContentUtility::getRegexpByType('hash');

        return ContentUtility::strHashtag(
            ContentUtility::matchAll($regexp, $newContent, 0)
        );
    }

    // Extract link
    public static function extractLink(string $content): array
    {
        return ContentUtility::matchAll(ContentUtility::getRegexpByType('url'), $content);
    }

    // Extract mention user
    public static function extractMention(string $content): array
    {
        return ContentUtility::matchAll(ContentUtility::getRegexpByType('at'), $content);
    }

    // Extract sticker
    public static function extractSticker(string $content): array
    {
        $content = strip_tags($content);

        $stickers = ContentUtility::matchAll(ContentUtility::getRegexpByType('sticker'), $content);

        if (empty($stickers)) {
            return [];
        }

        $result = [];
        foreach ($stickers as $sticker) {
            if (str_contains($sticker, ' ')) {
                continue;
            }

            $result[] = $sticker;
        }

        return $result;
    }

    // Extract file
    public static function extractFile(string $content): array
    {
        $files = ContentUtility::matchAll(ContentUtility::getRegexpByType('file'), $content);

        if (empty($files)) {
            return [];
        }

        $result = [];
        foreach ($files as $file) {
            if (str_contains($file, ' ')) {
                continue;
            }

            $result[] = $file;
        }

        return $result;
    }

    // Replace hashtag
    public static function replaceHashtag(string $content): string
    {
        $hashtagArr = ContentUtility::extractAllHashtag($content);
        $slugArr = [];
        foreach ($hashtagArr as $hashtag) {
            $slugArr[] = StrHelper::slug($hashtag);
        }

        $hashtagDataList = Hashtag::whereIn('slug', $slugArr)->get();

        $config = ConfigHelper::fresnsConfigByItemKeys([
            'hashtag_format',
            'site_url',
            'website_hashtag_detail_path',
        ]);

        $replaceList = [];
        $linkList = [];
        foreach ($hashtagArr as $hashtagName) {
            $hashtagData = $hashtagDataList->where('slug', StrHelper::slug($hashtagName))->first();
            if (empty($hashtagData) || ! $hashtagData->is_enabled) {
                continue;
            }

            $hashHashtag = "#{$hashtagName}#";
            $spaceHashtag = "#{$hashtagName} ";
            $replaceList[] = [$hashHashtag, $spaceHashtag];

            // <a href="https://abc.com/hashtag/PHP" class="fresns_hashtag" target="_blank">#PHP</a>
            // or
            // <a href="https://abc.com/hashtag/PHP" class="fresns_hashtag" target="_blank">#PHP#</a>
            $hashtag = ($config['hashtag_format'] == 1) ? "#{$hashtagName}" : "#{$hashtagName}#";

            $hashLink = sprintf(
                '<a href="%s/%s/%s" class="fresns_hashtag" target="_blank">%s</a>',
                $config['site_url'],
                $config['website_hashtag_detail_path'],
                StrHelper::slug($hashtagName),
                $hashtag,
            );

            $spaceLink = sprintf(
                '<a href="%s/%s/%s" class="fresns_hashtag" target="_blank">%s</a> ',
                $config['site_url'],
                $config['website_hashtag_detail_path'],
                StrHelper::slug($hashtagName),
                $hashtag,
            );

            $linkList[] = [$hashLink, $spaceLink];
        }

        $replaceList = Arr::collapse($replaceList);
        $linkList = Arr::collapse($linkList);

        return str_replace($replaceList, $linkList, $content);
    }

    // Replace link
    public static function replaceLink(string $content, ?int $userId = null): string
    {
        $urlList = ContentUtility::extractLink($content);

        $urlList = array_map(function ($url) {
            return str_replace('&amp;', '&', $url);
        }, $urlList);

        $urlDataList = DomainLink::with('domain')->whereIn('link_url', $urlList)->get();

        $siteUrl = ConfigHelper::fresnsConfigByItemKey('site_url') ?? AppUtility::WEBSITE_URL;
        $siteDomain = StrHelper::extractDomainByUrl($siteUrl);

        $mainRolePerms = $userId ? PermissionUtility::getUserMainRole($userId)['permissions'] : null;
        $contentLinkHandle = $mainRolePerms['content_link_handle'] ?? 2;

        $content = $content.' ';

        $newContent = preg_replace_callback(ContentUtility::getRegexpByType('url'), function ($matches) use ($urlDataList, $siteDomain, $contentLinkHandle) {
            $url = $matches[1];
            $url = str_replace('&amp;', '&', $url);

            $urlData = $urlDataList->where('link_url', $url)->first();
            if (empty($urlData) || empty($urlData?->domain) || ! $urlData?->is_enabled || ! $urlData?->domain?->is_enabled) {
                return $url;
            }

            $title = $urlData->link_title ?? $url;
            // <a href="https://fresns.org" class="fresns_link" target="_blank">Fresns Website</a>
            // or
            // <a href="https://fresns.org" class="fresns_link" target="_blank">https://fresns.org</a>

            if ($urlData->domain->domain == $siteDomain) {
                $contentLinkHandle = 3;
            }

            switch ($contentLinkHandle) {
                case 1:
                    return Str::replace($urlData->domain->host, '******', $url);
                    break;

                case 2:
                    return $url;
                    break;

                default:
                    return sprintf(
                        '<a href="%s" class="fresns_link" target="_blank">%s</a>',
                        $url,
                        $title,
                    );
                    break;
            }
        }, $content);

        return $newContent;
    }

    // Replace mention
    public static function replaceMention(string $content, int $mentionType, int $mentionId): string
    {
        $config = ConfigHelper::fresnsConfigByItemKeys([
            'user_identifier',
            'site_url',
            'website_user_detail_path',
        ]);

        $mentionArr = Mention::where('mention_type', $mentionType)->where('mention_id', $mentionId)->get();
        if ($mentionArr->isEmpty()) {
            return $content;
        }

        $userFsidList = ContentUtility::extractMention($content);
        $userArr = User::whereIn('uid', $userFsidList)->orWhereIn('username', $userFsidList)->get();

        $linkList = [];
        $replaceList = [];
        foreach ($userFsidList as $fsid) {
            // check mention record
            if (StrHelper::isPureInt($fsid)) {
                $user = $userArr->where('uid', $fsid)->first();
            } else {
                $user = $userArr->where('username', $fsid)->first();
            }

            $mentionUser = $mentionArr->where('mention_user_id', $user?->id)->first();

            if (empty($mentionUser)) {
                $replaceList[] = "@{$fsid}";
                $linkList[] = sprintf(
                    '<a href="%s/%s/0" class="fresns_mention" target="_blank">@%s</a>',
                    $config['site_url'],
                    $config['website_user_detail_path'],
                    $fsid
                );
                continue;
            }

            if ($config['user_identifier'] == 'uid') {
                // <a href="https://abc.com/u/{uid}" class="fresns_mention" target="_blank">@nickname</a>
                $urlName = $user->uid;
            } else {
                // <a href="https://abc.com/u/{username}" class="fresns_mention" target="_blank">@nickname</a>
                $urlName = $user->username;
            }

            $replaceList[] = "@{$fsid}";

            $linkList[] = sprintf(
                '<a href="%s/%s/%s" class="fresns_mention" target="_blank">@%s</a>',
                $config['site_url'],
                $config['website_user_detail_path'],
                $urlName,
                $user->nickname,
            );
        }

        return str_replace($replaceList, $linkList, $content);
    }

    // Replace sticker
    public static function replaceSticker(string $content): string
    {
        $stickerCodeList = ContentUtility::extractSticker($content);
        $stickerDataList = Sticker::whereIn('code', $stickerCodeList)->get();

        $replaceList = [];
        $linkList = [];
        foreach ($stickerCodeList as $sticker) {
            $replaceList[] = "[$sticker]";

            $currentSticker = $stickerDataList->where('code', $sticker)->first();
            if (is_null($currentSticker)) {
                $linkList[] = "[$sticker]";
            } else {
                $stickerUrl = FileHelper::fresnsFileUrlByTableColumn($currentSticker->image_file_id, $currentSticker->image_file_url);

                // <img src="$stickerUrl" class="fresns_sticker" alt="$sticker->code">
                $linkList[] = sprintf('<img src="%s" class="fresns_sticker" alt="%s" />', $stickerUrl, $currentSticker->code);
            }
        }

        return str_replace($replaceList, $linkList, $content);
    }

    // Replace file
    public static function replaceFile(string $content): string
    {
        $fidArr = ContentUtility::extractFile($content);

        $files = File::whereIn('fid', $fidArr)->get();

        $replaceList = [];
        $linkList = [];
        foreach ($fidArr as $fid) {
            $replaceList[] = "[file:{$fid}]";

            $file = $files->where('fid', $fid)->first();

            if (is_null($file)) {
                $linkList[] = "[file:{$fid}]";
            } else {
                $fileInfo = FileHelper::fresnsFileInfoById($file->id);

                if (! $fileInfo) {
                    continue;
                }

                $linkList[] = match ($file->type) {
                    File::TYPE_IMAGE => sprintf('<img class="fresns_file_image" loading="lazy" src="%s" alt="%s">', $fileInfo['imageBigUrl'], $fileInfo['name']),
                    File::TYPE_VIDEO => sprintf('<video class="fresns_file_video" controls preload="metadata" controlslist="nodownload" poster="%s"><source src="%s" type="%s"></video>', $fileInfo['videoPosterUrl'], $fileInfo['videoUrl'], $fileInfo['mime']),
                    File::TYPE_AUDIO => sprintf('<audio class="fresns_file_audio" controls preload="metadata" controlsList="nodownload" src="%s"></audio>', $fileInfo['audioUrl']),
                    File::TYPE_DOCUMENT => sprintf('<button class="fresns_file_document" type="button" data-fid="%s" data-file="%s">%s</button>', $fileInfo['fid'], json_encode($fileInfo, true), $fileInfo['name']),
                };
            }
        }

        return str_replace($replaceList, $linkList, $content);
    }

    // Handle and replace all
    public static function handleAndReplaceAll(string $content, int $isMarkdown, ?int $userId = null, ?int $mentionType = null, ?int $mentionId = null): string
    {
        // Replace link
        // Replace hashtag
        // Replace mention
        // Replace sticker
        if ($isMarkdown) {
            $content = Str::swap([
                '<script>' => '&lt;script&gt;',
                '</script>' => '&lt;&#47;script&gt;',
                '<iframe>' => '&lt;iframe&gt;',
                '</iframe>' => '&lt;&#47;iframe&gt;',
                '<frame>' => '&lt;frame&gt;',
                '</frame>' => '&lt;&#47;frame&gt;',
                '"javascript' => '&#34;javascript',
                'javascript"' => 'javascript&#34;',
                "'javascript" => '&#39;javascript',
                "javascript'" => 'javascript&#39;',
                'alert(' => 'alert &#40;',
            ], $content);
        } else {
            $content = htmlentities($content);

            $content = static::replaceLink($content, $userId);
        }

        $content = static::replaceHashtag($content);

        if ($mentionType && $mentionId) {
            $content = static::replaceMention($content, $mentionType, $mentionId);
        }

        $content = static::replaceSticker($content);

        return $content;
    }

    // Save hashtag
    public static function saveHashtag(string $content, int $usageType, int $useId): void
    {
        $hashtagArr = ContentUtility::extractConfigHashtag($content);

        if (empty($hashtagArr)) {
            return;
        }

        // add hashtag data
        foreach ($hashtagArr as $hashtag) {
            Hashtag::firstOrCreate([
                'slug' => StrHelper::slug($hashtag),
            ], [
                'name' => $hashtag,
            ]);
        }

        // add hashtag use
        $slugArr = [];
        foreach ($hashtagArr as $hashtag) {
            $slugArr[] = StrHelper::slug($hashtag);
        }
        $hashtagIdArr = Hashtag::whereIn('slug', $slugArr)->pluck('id')->toArray();

        foreach ($hashtagIdArr as $hashtagId) {
            $hashtagUseDataItem = [
                'usage_type' => $usageType,
                'usage_id' => $useId,
                'hashtag_id' => $hashtagId,
            ];

            HashtagUsage::create($hashtagUseDataItem);
        }
    }

    // Save link
    public static function saveLink(string $content, int $usageType, int $useId): void
    {
        $urlArr = ContentUtility::extractLink($content);

        // add domain data
        foreach ($urlArr as $url) {
            $domain = StrHelper::extractDomainByUrl($url);

            if (empty($domain)) {
                continue;
            }

            $domainModel = Domain::firstOrCreate([
                'host' => parse_url($url, PHP_URL_HOST),
            ], [
                'domain' => $domain,
            ]);

            DomainLink::firstOrCreate([
                'link_url' => $url,
            ], [
                'domain_id' => $domainModel->id,
            ]);
        }

        // add domain link use
        $urlIdArr = DomainLink::whereIn('link_url', $urlArr)->pluck('id')->toArray();

        foreach ($urlIdArr as $urlId) {
            $urlUseDataItem = [
                'usage_type' => $usageType,
                'usage_id' => $useId,
                'link_id' => $urlId,
            ];

            DomainLinkUsage::create($urlUseDataItem);
        }
    }

    // Save mention user
    public static function saveMention(string $content, int $mentionType, int $mentionId, int $authUserId): void
    {
        $fsidArr = ContentUtility::extractMention($content);

        $config = ConfigHelper::fresnsConfigByItemKey('user_identifier');

        if ($config == 'uid') {
            $userIdArr = User::whereIn('uid', $fsidArr)->pluck('id')->toArray();
        } else {
            $userIdArr = User::whereIn('username', $fsidArr)->pluck('id')->toArray();
        }

        foreach ($userIdArr as $userId) {
            $mentionDataItem = [
                'user_id' => $authUserId,
                'mention_type' => $mentionType,
                'mention_id' => $mentionId,
                'mention_user_id' => $userId,
            ];

            Mention::create($mentionDataItem);
        }
    }

    // Handle and save all(interaction content)
    public static function handleAndSaveAllInteraction(string $content, int $type, int $id, ?int $authUserId = null): void
    {
        $configs = ConfigHelper::fresnsConfigByItemKeys([
            'hashtag_status',
            'mention_status',
        ]);

        if ($configs['hashtag_status']) {
            static::saveHashtag($content, $type, $id);
        }

        static::saveLink($content, $type, $id);

        if ($configs['mention_status'] && $authUserId) {
            static::saveMention($content, $type, $id, $authUserId);
        }
    }

    // save operation usages
    // $operations = [{"id": "id", "fskey": null}]
    public static function saveOperationUsages(int $usageType, int $usageId, array $operations): void
    {
        foreach ($operations as $operation) {
            $id = $operation['id'] ?? null;
            if (empty($id)) {
                continue;
            }

            $operationModel = PrimaryHelper::fresnsModelById('operation', $id);
            if (empty($operationModel)) {
                continue;
            }

            OperationUsage::updateOrCreate([
                'usage_type' => $usageType,
                'usage_id' => $usageId,
                'operation_id' => $operation->id,
            ], [
                'app_fskey' => $operation['fskey'] ?? $operationModel->app_fskey,
            ]);
        }
    }

    // save archive usages
    // $archives = [{"code": "code", "value": "value", "isPrivate": true, "fskey": null}]
    public static function saveArchiveUsages(int $usageType, int $usageId, array $archives): void
    {
        foreach ($archives as $archive) {
            $code = $archive['code'] ?? null;
            if (empty($code)) {
                continue;
            }

            $archiveModel = PrimaryHelper::fresnsModelByFsid('archive', $code);
            if (empty($archiveModel)) {
                continue;
            }

            ArchiveUsage::updateOrCreate([
                'usage_type' => $usageType,
                'usage_id' => $usageId,
                'archive_id' => $archiveModel->id,
            ], [
                'archive_value' => $archive['value'] ?? null,
                'is_private' => $archive['isPrivate'] ?? false,
                'app_fskey' => $archive['fskey'] ?? $archiveModel->app_fskey,
            ]);
        }
    }

    // save extend usages
    // $extends = [{"eid": "eid", "canDelete": true, "sortOrder": 9, "fskey": null}]
    public static function saveExtendUsages(int $usageType, int $usageId, array $extends): void
    {
        foreach ($extends as $extend) {
            $eid = $extend['eid'] ?? null;
            if (empty($eid)) {
                continue;
            }

            $extendModel = PrimaryHelper::fresnsModelByFsid('extend', $eid);
            if (empty($extendModel)) {
                continue;
            }

            ExtendUsage::updateOrCreate([
                'usage_type' => $usageType,
                'usage_id' => $usageId,
                'extend_id' => $extendModel->id,
            ], [
                'can_delete' => $extend['canDelete'] ?? true,
                'sort_order' => $extend['sortOrder'] ?? 9,
                'app_fskey' => $extend['fskey'] ?? $extendModel->app_fskey,
            ]);
        }
    }

    // release location info
    public static function releaseLocationInfo(array $locationInfo): ?Geotag
    {
        // $locationInfo = [
        //     'langTag' => '',
        //     'name' => 'geotags->name',
        //     'description' => 'geotags->description',
        //     'placeId' => 'geotags->place_id', // Reference: Google Map
        //     'placeType' => 'geotags->place_type', // Reference: Google Map
        //     'mapId' => 'geotags->map_id',
        //     'latitude' => 'geotags->map_latitude',
        //     'longitude' => 'geotags->map_longitude',
        //     'continent' => '',
        //     'continentCode' => '',
        //     'country' => '',
        //     'countryCode' => '',
        //     'region' => '',
        //     'regionCode' => '',
        //     'city' => '',
        //     'cityCode' => '',
        //     'district' => 'geotags->district',
        //     'address' => 'geotags->address',
        //     'zip' => 'geotags->zip',
        //     'moreInfo' => 'geotags->more_info',
        // ];

        $name = $locationInfo['name'] ?? null;
        $mapId = $locationInfo['mapId'] ?? null;
        $latitude = $locationInfo['latitude'] ?? null;
        $longitude = $locationInfo['longitude'] ?? null;

        if (empty($name) || empty($mapId) || empty($latitude) || empty($longitude)) {
            return null;
        }

        $langTag = $locationInfo['langTag'] ?? AppHelper::getLangTag();

        // city
        $cityInfo = [
            'continent' => $locationInfo['continent'] ?? null,
            'continentCode' => $locationInfo['continentCode'] ?? null,
            'country' => $locationInfo['country'] ?? null,
            'countryCode' => $locationInfo['countryCode'] ?? null,
            'region' => $locationInfo['region'] ?? null,
            'regionCode' => $locationInfo['regionCode'] ?? null,
            'city' => $locationInfo['city'] ?? null,
            'cityCode' => $locationInfo['cityCode'] ?? null,
        ];

        $allNotEmpty = count(array_filter($cityInfo, function ($value) {
            return ! is_null($value);
        })) === count($cityInfo);

        $cityId = null;
        if ($allNotEmpty) {
            $cityModel = City::where('continent_code', $cityInfo['continentCode'])
                ->where('country_code', $cityInfo['countryCode'])
                ->where('region_code', $cityInfo['regionCode'])
                ->where('city_code', $cityInfo['cityCode'])
                ->first();

            if ($cityModel) {
                $dbContinent = $cityModel->continent;
                $dbContinent[$langTag] = $cityInfo['continent'];

                $dbCountry = $cityModel->country;
                $dbCountry[$langTag] = $cityInfo['country'];

                $dbRegion = $cityModel->region;
                $dbRegion[$langTag] = $cityInfo['region'];

                $dbCity = $cityModel->city;
                $dbCity[$langTag] = $cityInfo['city'];

                $dbZip = $cityModel->zip ?? $locationInfo['zip'] ?? null;

                $cityModel->update([
                    'continent' => $dbContinent,
                    'country' => $dbCountry,
                    'region' => $dbRegion,
                    'city' => $dbCity,
                    'zip' => $dbZip,
                ]);
            } else {
                $continent[$langTag] = $cityInfo['continent'];
                $country[$langTag] = $cityInfo['country'];
                $region[$langTag] = $cityInfo['region'];
                $city[$langTag] = $cityInfo['city'];

                $cityItems = [
                    'continent_code' => $cityInfo['continentCode'],
                    'country_code' => $cityInfo['countryCode'],
                    'region_code' => $cityInfo['regionCode'],
                    'city_code' => $cityInfo['cityCode'],
                    'continent' => $continent,
                    'country' => $country,
                    'region' => $region,
                    'city' => $city,
                    'zip' => $locationInfo['zip'] ?? null,
                ];

                $cityModel = Geotag::create($cityItems);
            }

            $cityId = $cityModel->id;
        }

        // geotag
        $placeId = $locationInfo['placeId'] ?? null;

        $geotag = $placeId ? Geotag::where('map_id', $mapId)->where('place_id', $placeId)->first() : null;

        if ($geotag) {
            $dbName = $geotag->name;
            $dbName[$langTag] = $name;

            $dbDescription = $geotag->description;
            if ($locationInfo['description'] ?? null) {
                $dbDescription[$langTag] = $locationInfo['description'];
            }

            $dbDistrict = $geotag->district;
            if ($locationInfo['district'] ?? null) {
                $dbDistrict[$langTag] = $locationInfo['district'];
            }

            $dbAddress = $geotag->address;
            if ($locationInfo['address'] ?? null) {
                $dbAddress[$langTag] = $locationInfo['address'];
            }

            $geotag->update([
                'name' => $dbName,
                'description' => $dbDescription,
                'district' => $dbDistrict,
                'address' => $dbAddress,
            ]);

            return $geotag;
        }

        // geotag
        $mapLocation = null;
        switch (config('database.default')) {
            case 'mysql':
                $mapLocation = DB::raw("ST_GeomFromText('POINT($longitude $latitude)', 4326)");
                break;

            case 'sqlite':
                $mapLocation = DB::raw("MakePoint($longitude, $latitude, 4326)");
                break;

            case 'pgsql':
                $mapLocation = DB::raw("ST_SetSRID(ST_MakePoint($longitude, $latitude), 4326)");
                break;

            case 'sqlsrv':
                $mapLocation = DB::raw("geography::Point($latitude, $longitude, 4326)");
                break;
        }

        $dbName[$langTag] = $name;

        $dbDescription = null;
        if ($locationInfo['description'] ?? null) {
            $dbDescription[$langTag] = $locationInfo['description'];
        }

        $dbDistrict = null;
        if ($locationInfo['district'] ?? null) {
            $dbDistrict[$langTag] = $locationInfo['district'];
        }

        $dbAddress = null;
        if ($locationInfo['address'] ?? null) {
            $dbAddress[$langTag] = $locationInfo['address'];
        }

        $geotagItems = [
            'place_id' => $placeId,
            'place_type' => $locationInfo['placeType'] ?? null,
            'name' => $dbName,
            'description' => $dbDescription,
            'city_id' => $cityId,
            'map_id' => $mapId,
            'map_longitude' => $longitude,
            'map_latitude' => $latitude,
            'map_location' => $mapLocation,
            'district' => $dbDistrict,
            'address' => $dbAddress,
            'more_info' => $locationInfo['moreInfo'] ?? [],
        ];

        $geotag = Geotag::create($geotagItems);

        return $geotag;
    }

    // release file usages
    public static function releaseFileUsages(string $type, int $logId, int $primaryId): void
    {
        $logTableName = match ($type) {
            'post' => 'post_logs',
            'comment' => 'comment_logs',
        };

        $tableName = match ($type) {
            'post' => 'posts',
            'comment' => 'comments',
        };

        FileUsage::where('table_name', $tableName)->where('table_column', 'id')->where('table_id', $primaryId)->delete();

        $fileUsages = FileUsage::where('table_name', $logTableName)->where('table_column', 'id')->where('table_id', $logId)->get();

        foreach ($fileUsages as $fileUsage) {
            $fileDataItem = [
                'file_id' => $fileUsage->file_id,
                'file_type' => $fileUsage->file_type,
                'usage_type' => $fileUsage->usage_type,
                'platform_id' => $fileUsage->platform_id,
                'table_name' => $tableName,
                'table_column' => 'id',
                'table_id' => $primaryId,
                'sort_order' => $fileUsage->sort_order,
                'account_id' => $fileUsage->account_id,
                'user_id' => $fileUsage->user_id,
                'remark' => $fileUsage->remark,
            ];

            FileUsage::create($fileDataItem);
        }
    }

    // release extend usages
    public static function releaseExtendUsages(string $type, int $logId, int $primaryId): void
    {
        $logUsageType = match ($type) {
            'post' => ExtendUsage::TYPE_POST_LOG,
            'comment' => ExtendUsage::TYPE_COMMENT_LOG,
        };

        $usageType = match ($type) {
            'post' => ExtendUsage::TYPE_POST,
            'comment' => ExtendUsage::TYPE_COMMENT,
        };

        ExtendUsage::where('usage_type', $usageType)->where('usage_id', $primaryId)->delete();

        $extendUsages = ExtendUsage::where('usage_type', $logUsageType)->where('usage_id', $logId)->get();

        foreach ($extendUsages as $extend) {
            $extendDataItem = [
                'usage_type' => $usageType,
                'usage_id' => $primaryId,
                'extend_id' => $extend->extend_id,
                'can_delete' => $extend->can_delete,
                'sort_order' => $extend->sort_order,
                'app_fskey' => $extend->app_fskey,
            ];

            ExtendUsage::create($extendDataItem);
        }
    }

    // release operation usages
    public static function releaseOperationUsages(string $type, int $logId, int $primaryId): void
    {
        $logUsageType = match ($type) {
            'post' => OperationUsage::TYPE_POST_LOG,
            'comment' => OperationUsage::TYPE_COMMENT_LOG,
        };

        $usageType = match ($type) {
            'post' => OperationUsage::TYPE_POST,
            'comment' => OperationUsage::TYPE_COMMENT,
        };

        OperationUsage::where('usage_type', $usageType)->where('usage_id', $primaryId)->delete();

        $operationUsages = OperationUsage::where('usage_type', $logUsageType)->where('usage_id', $logId)->get();

        foreach ($operationUsages as $operation) {
            $operationDataItem = [
                'usage_type' => $usageType,
                'usage_id' => $primaryId,
                'operation_id' => $operation->operation_id,
                'app_fskey' => $operation->app_fskey,
            ];

            OperationUsage::create($operationDataItem);
        }
    }

    // release archive usages
    public static function releaseArchiveUsages(string $type, int $logId, int $primaryId): void
    {
        $logUsageType = match ($type) {
            'post' => ArchiveUsage::TYPE_POST_LOG,
            'comment' => ArchiveUsage::TYPE_COMMENT_LOG,
        };

        $usageType = match ($type) {
            'post' => ArchiveUsage::TYPE_POST,
            'comment' => ArchiveUsage::TYPE_COMMENT,
        };

        ArchiveUsage::where('usage_type', $usageType)->where('usage_id', $primaryId)->delete();

        $archiveUsages = ArchiveUsage::where('usage_type', $logUsageType)->where('usage_id', $logId)->get();

        foreach ($archiveUsages as $archive) {
            $archiveDataItem = [
                'usage_type' => $usageType,
                'usage_id' => $primaryId,
                'archive_id' => $archive->archive_id,
                'archive_value' => $archive->archive_value,
                'is_private' => $archive->is_private,
                'app_fskey' => $archive->app_fskey,
            ];

            ArchiveUsage::create($archiveDataItem);
        }
    }

    // release post
    public static function releasePost(PostLog $postLog): Post
    {
        $geotag = PrimaryHelper::fresnsModelById('geotag', $postLog->geotag_id);
        if (! $geotag && $postLog->location_info) {
            $geotag = ContentUtility::releaseLocationInfo($postLog->location_info);
        }

        $mapLng = $geotag?->map_longitude;
        $mapLat = $geotag?->map_latitude;

        $mapLocation = null;
        if ($mapLng && $mapLat) {
            switch (config('database.default')) {
                case 'mysql':
                    $mapLocation = DB::raw("ST_GeomFromText('POINT($mapLng $mapLat)', 4326)");
                    break;

                case 'sqlite':
                    $mapLocation = DB::raw("MakePoint($mapLng, $mapLat, 4326)");
                    break;

                case 'pgsql':
                    $mapLocation = DB::raw("ST_SetSRID(ST_MakePoint($mapLng, $mapLat), 4326)");
                    break;

                case 'sqlsrv':
                    $mapLocation = DB::raw("geography::Point($mapLat, $mapLng, 4326)");
                    break;
            }
        }

        // old post
        if ($postLog->post_id) {
            $oldPost = PrimaryHelper::fresnsModelById('post', $postLog->post_id);

            InteractionUtility::editStats('post', $oldPost->id, 'decrement');
        }

        // new post
        $post = Post::updateOrCreate([
            'id' => $postLog->post_id,
        ], [
            'user_id' => $postLog->user_id,
            'quoted_post_id' => $postLog->quoted_post_id,
            'group_id' => $postLog->group_id,
            'geotag_id' => $geotag?->id,
            'title' => $postLog->title,
            'content' => $postLog->content,
            'lang_tag' => $postLog->lang_tag,
            'is_markdown' => $postLog->is_markdown,
            'is_anonymous' => $postLog->is_anonymous,
            'map_location' => $mapLocation,
            'more_info' => $postLog->more_info,
            'permissions' => $postLog->permissions,
        ]);

        ContentUtility::releaseArchiveUsages('post', $postLog->id, $post->id);
        ContentUtility::releaseOperationUsages('post', $postLog->id, $post->id);
        ContentUtility::releaseFileUsages('post', $postLog->id, $post->id);
        ContentUtility::releaseExtendUsages('post', $postLog->id, $post->id);

        if ($postLog->post_id) {
            if ($postLog->group_id != $oldPost->group_id) {
                $groupCommentCount = Comment::where('post_id', $post->id)->count();

                Group::where('id', $postLog->group_id)->increment('comment_count', $groupCommentCount);
                Group::where('id', $oldPost->group_id)->decrement('comment_count', $groupCommentCount);
            }

            HashtagUsage::where('usage_type', HashtagUsage::TYPE_POST)->where('usage_id', $post->id)->delete();
            DomainLinkUsage::where('usage_type', DomainLinkUsage::TYPE_POST)->where('usage_id', $post->id)->delete();
            Mention::where('user_id', $postLog->user_id)->where('mention_type', Mention::TYPE_POST)->where('mention_id', $post->id)->delete();

            ContentUtility::handleAndSaveAllInteraction($postLog->content, Mention::TYPE_POST, $post->id, $postLog->user_id);

            InteractionUtility::editStats('post', $post->id, 'increment');

            $post->update([
                'last_edit_at' => now(),
            ]);
            $post->increment('edit_count');

            CacheHelper::forgetFresnsModel('post', $post->id);
            CacheHelper::forgetFresnsMultilingual("fresns_detail_post_{$post->id}", 'fresnsPosts');
        } else {
            ContentUtility::handleAndSaveAllInteraction($postLog->content, Mention::TYPE_POST, $post->id, $postLog->user_id);
            InteractionUtility::publishStats('post', $post->id, 'increment');
        }

        $postLog->update([
            'post_id' => $post->id,
            'state' => PostLog::STATE_SUCCESS,
        ]);

        // send notification
        InteractionUtility::sendPublishNotification('post', $post->id);

        return $post;
    }

    // release comment
    public static function releaseComment(CommentLog $commentLog): Comment
    {
        $parentComment = PrimaryHelper::fresnsModelById('comment', $commentLog->parent_id);

        $topParentId = 0;
        if ($parentComment) {
            $topParentId = $parentComment->top_parent_id;
        }

        $geotag = PrimaryHelper::fresnsModelById('geotag', $commentLog->geotag_id);
        if (! $geotag && $commentLog->location_info) {
            $geotag = ContentUtility::releaseLocationInfo($commentLog->location_info);
        }

        $mapLng = $geotag?->map_longitude;
        $mapLat = $geotag?->map_latitude;

        $mapLocation = null;
        if ($mapLng && $mapLat) {
            switch (config('database.default')) {
                case 'mysql':
                    $mapLocation = DB::raw("ST_GeomFromText('POINT($mapLng $mapLat)', 4326)");
                    break;

                case 'sqlite':
                    $mapLocation = DB::raw("MakePoint($mapLng, $mapLat, 4326)");
                    break;

                case 'pgsql':
                    $mapLocation = DB::raw("ST_SetSRID(ST_MakePoint($mapLng, $mapLat), 4326)");
                    break;

                case 'sqlsrv':
                    $mapLocation = DB::raw("geography::Point($mapLat, $mapLng, 4326)");
                    break;
            }
        }

        // old comment
        if ($commentLog->comment_id) {
            $oldComment = PrimaryHelper::fresnsModelById('comment', $commentLog->comment_id);

            InteractionUtility::editStats('comment', $oldComment->id, 'decrement');
        }

        $post = PrimaryHelper::fresnsModelById('post', $commentLog->post_id);
        $postPermissions = $post?->permissions;
        $postPrivacy = $postPermissions['commentConfig']['privacy'] ?? 'public';

        $isPrivate = $commentLog->is_private ? Comment::PRIVACY_PRIVATE : Comment::PRIVACY_PUBLIC;
        $privacyState = ($postPrivacy == 'private') ? Comment::PRIVACY_PRIVATE_BY_POST : $isPrivate;

        // new comment
        $comment = Comment::updateOrCreate([
            'id' => $commentLog->comment_id,
        ], [
            'user_id' => $commentLog->user_id,
            'post_id' => $commentLog->post_id,
            'top_parent_id' => $topParentId,
            'parent_id' => $commentLog->parent_comment_id,
            'content' => $commentLog->content,
            'lang_tag' => $commentLog->lang_tag,
            'is_markdown' => $commentLog->is_markdown,
            'is_anonymous' => $commentLog->is_anonymous,
            'privacy_state' => $privacyState,
            'map_location' => $mapLocation,
            'more_info' => $commentLog->more_info,
            'permissions' => $commentLog->permissions,
        ]);

        ContentUtility::releaseFileUsages('comment', $commentLog->id, $comment->id);
        ContentUtility::releaseExtendUsages('comment', $commentLog->id, $comment->id);
        ContentUtility::releaseArchiveUsages('comment', $commentLog->id, $comment->id);
        ContentUtility::releaseOperationUsages('comment', $commentLog->id, $comment->id);

        if ($commentLog->comment_id) {
            HashtagUsage::where('usage_type', HashtagUsage::TYPE_COMMENT)->where('usage_id', $comment->id)->delete();
            DomainLinkUsage::where('usage_type', DomainLinkUsage::TYPE_COMMENT)->where('usage_id', $comment->id)->delete();
            Mention::where('user_id', $commentLog->user_id)->where('mention_type', Mention::TYPE_COMMENT)->where('mention_id', $comment->id)->delete();

            ContentUtility::handleAndSaveAllInteraction($commentLog->content, Mention::TYPE_COMMENT, $comment->id, $commentLog->user_id);

            InteractionUtility::editStats('comment', $comment->id, 'increment');

            $comment->update([
                'last_edit_at' => now(),
            ]);
            $comment->increment('edit_count');

            CacheHelper::forgetFresnsModel('comment', $comment->id);
            CacheHelper::forgetFresnsMultilingual("fresns_detail_comment_{$comment->id}", 'fresnsComments');
        } else {
            ContentUtility::handleAndSaveAllInteraction($commentLog->content, Mention::TYPE_COMMENT, $comment->id, $commentLog->user_id);
            InteractionUtility::publishStats('comment', $comment->id, 'increment');
        }

        $commentLog->update([
            'comment_id' => $comment->id,
            'state' => CommentLog::STATE_SUCCESS,
        ]);

        // send notification
        InteractionUtility::sendPublishNotification('comment', $comment->id);

        return $comment;
    }

    // batch copy content extends
    public static function batchCopyContentExtends(string $type, int $primaryId, int $logId): void
    {
        $tableName = match ($type) {
            'post' => 'posts',
            'comment' => 'comments',
        };

        $logTableName = match ($type) {
            'post' => 'post_logs',
            'comment' => 'comment_logs',
        };

        $usageType = match ($type) {
            'post' => ExtendUsage::TYPE_POST,
            'comment' => ExtendUsage::TYPE_COMMENT,
        };

        $logUsageType = match ($type) {
            'post' => ExtendUsage::TYPE_POST_LOG,
            'comment' => ExtendUsage::TYPE_COMMENT_LOG,
        };

        // files
        $fileUsages = FileUsage::where('table_name', $tableName)->where('table_column', 'id')->where('table_id', $primaryId)->get();
        foreach ($fileUsages as $fileUsage) {
            $fileDataItem = [
                'file_id' => $fileUsage->file_id,
                'file_type' => $fileUsage->file_type,
                'usage_type' => $fileUsage->usage_type,
                'platform_id' => $fileUsage->platform_id,
                'table_name' => $logTableName,
                'table_column' => 'id',
                'table_id' => $logId,
                'sort_order' => $fileUsage->sort_order,
                'account_id' => $fileUsage->account_id,
                'user_id' => $fileUsage->user_id,
                'remark' => $fileUsage->remark,
            ];

            FileUsage::create($fileDataItem);
        }

        // archives
        $archiveUsages = ArchiveUsage::where('usage_type', $usageType)->where('usage_id', $primaryId)->get();

        foreach ($archiveUsages as $archive) {
            $archiveDataItem = [
                'usage_type' => $logUsageType,
                'usage_id' => $logId,
                'archive_id' => $archive->archive_id,
                'archive_value' => $archive->archive_value,
                'is_private' => $archive->is_private,
                'app_fskey' => $archive->app_fskey,
            ];

            ArchiveUsage::create($archiveDataItem);
        }

        // extends
        $extendUsages = ExtendUsage::where('usage_type', $usageType)->where('usage_id', $primaryId)->get();

        foreach ($extendUsages as $extend) {
            $extendDataItem = [
                'usage_type' => $logUsageType,
                'usage_id' => $logId,
                'extend_id' => $extend->extend_id,
                'can_delete' => $extend->can_delete,
                'sort_order' => $extend->sort_order,
                'app_fskey' => $extend->app_fskey,
            ];

            ExtendUsage::create($extendDataItem);
        }
    }

    // generate post draft
    public static function generatePostDraft(Post $post): PostLog
    {
        $postLog = PostLog::where('post_id', $post->id)->whereNot('state', PostLog::STATE_SUCCESS)->first();

        if ($postLog) {
            return $postLog;
        }

        // post log
        $logData = [
            'create_type' => 3,
            'user_id' => $post->user_id,
            'post_id' => $post->id,
            'quoted_post_id' => $post->quoted_post_id,
            'group_id' => $post->group_id,
            'geotag_id' => $post->geotag_id,
            'title' => $post->title,
            'content' => $post->content,
            'is_markdown' => $post->is_markdown,
            'is_anonymous' => $post->is_anonymous,
            'more_info' => $post->more_info,
            'permissions' => $post->permissions,
        ];

        $postLog = PostLog::create($logData);

        ContentUtility::batchCopyContentExtends('post', $post->id, $postLog->id);

        return $postLog;
    }

    // generate comment draft
    public static function generateCommentDraft(Comment $comment): CommentLog
    {
        $commentLog = CommentLog::where('comment_id', $comment->id)->whereNot('state', CommentLog::STATE_SUCCESS)->first();

        if ($commentLog) {
            return $commentLog;
        }

        // comment log
        $logData = [
            'create_type' => 3,
            'user_id' => $comment->user_id,
            'post_id' => $comment->post_id,
            'comment_id' => $comment->id,
            'parent_comment_id' => $comment->parent_id,
            'geotag_id' => $comment->geotag_id,
            'content' => $comment->content,
            'is_markdown' => $comment->is_markdown,
            'is_anonymous' => $comment->is_anonymous,
            'is_private' => ($comment->privacy_state != Comment::PRIVACY_PUBLIC) ? true : false,
            'more_info' => $comment->more_info,
            'permissions' => $comment->permissions,
        ];

        $commentLog = CommentLog::create($logData);

        ContentUtility::batchCopyContentExtends('comment', $comment->id, $commentLog->id);

        return $commentLog;
    }
}
