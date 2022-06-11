<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;
use App\Helpers\StrHelper;
use App\Models\Domain;
use App\Models\User;
use App\Models\Mention;
use App\Models\Sticker;
use App\Models\DomainLink;
use App\Models\DomainLinkLinked;
use App\Models\Extend;
use App\Models\Hashtag;
use App\Models\HashtagLinked;
use App\Models\Role;
use Illuminate\Support\Collection;

class ContentUtility
{
    // preg regexp
    public static function getRegexpByType($type)
    {
        return match ($type) {
            'hash' => "/#(.*?)#/",
            'space' => "/#(.*?)\s/",
            'url' => "/(https?:\/\/.*?)\s/",
            'at' => "/@(.*?)\s/",
            'sticker' => "/\[(.*?)\]/",
        };
    }

    // Not valid for hashtag containing special characters
    public static function filterHashtagChars($data, $exceptChars = ',# ')
    {
        $data = array_filter($data);

        // Array of characters to be excluded
        $exceptChars = str_split($exceptChars);

        $result = [];
        foreach ($data as $item) {
            $needExcludeFlag = false;

            // Skip when it contains characters that need to be excluded
            foreach ($exceptChars as $char) {
                if (str_contains($item, $char)) {
                    $needExcludeFlag = true;
                    break;
                }
            }

            if ($needExcludeFlag) {
                continue;
            }

            $result[] = $item;
        }

        return $result;
    }

    // match all extract
    public static function matchAll($regexp, $content, ?callable $filterChars = null)
    {
        // Matching information is handled at the end
        $content = $content . ' ';

        preg_match_all($regexp, $content, $matches);

        $data = $matches[1] ?? [];

        if (is_callable($filterChars)) {
            return $filterChars($data);
        }

        return $data;
    }

    // Extract hashtag
    public static function extractHashtag(string $content): array
    {
        $hashData = ContentUtility::filterHashtagChars(
            ContentUtility::matchAll(ContentUtility::getRegexpByType('hash'), $content)
        );
        $spaceData = ContentUtility::filterHashtagChars(
            ContentUtility::matchAll(ContentUtility::getRegexpByType('space'), $content)
        );

        // De-duplication of the extracted hashtag
        $data = array_unique([...$spaceData, ...$hashData]);

        return $data;
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
        return ContentUtility::filterChars(
            ContentUtility::matchAll(ContentUtility::getRegexpByType('sticker'), $content),
            ' '
        );
    }

    // Replace hashtag
    public static function replaceHashtag(string $content): string
    {
        $hashtagList = ContentUtility::extractHashtag($content);

        $config = ConfigHelper::fresnsConfigByItemKeys(['site_domain', 'hashtag_show']);

        $replaceList = [];
        $linkList = [];
        foreach ($hashtagList as $hashtagName) {
            if ($config['hashtag_show'] == 1) {
                // <a href="https://abc.com/hashtag/PHP" class="fresns_hashtag" target="_blank">#PHP</a>
                $hashtag = "#{$hashtagName}";
                $replaceList[] = "$hashtag ";
            } else {
                // <a href="https://abc.com/hashtag/PHP" class="fresns_hashtag" target="_blank">#PHP#</a>
                $hashtag = "#{$hashtagName}#";
                $replaceList[] = "$hashtag";
            }

            $link = sprintf(
                '<a href="%s/hashtag/%s" class="fresns_hashtag" target="_blank">%s</a>',
                $config['site_domain'],
                StrHelper::slug($hashtagName),
                $hashtag
            );

            $linkList[] = $link;
        }

        return str_replace($replaceList, $linkList, $content);
    }

    // Replace link
    public static function replaceLink(string $content): string
    {
        $urlList = ContentUtility::extractLink($content);

        $urlDataList = DomainLink::whereIn('link_url', $urlList)->get();

        $replaceList = [];
        $linkList = [];
        foreach ($urlList as $url) {
            $urlData = $urlDataList->where('link_url', $url)->first();

            // <a href="https://fresns.org" class="fresns_link" target="_blank">Fresns Website</a>
            // or
            // <a href="https://fresns.org" class="fresns_link" target="_blank">https://fresns.org</a>
            $title = $urlData->link_title ?? $url;

            $replaceList[] = "{$url} ";
            $linkList[] = sprintf('<a href="%s" class="fresns_link" target="_blank">%s</a>', $url, $title);
        }

        return str_replace($replaceList, $linkList, $content);
    }

    // Replace mention
    public static function replaceMention(string $content, int $mentionType, int $mentionId): string
    {
        $config = ConfigHelper::fresnsConfigByItemKeys(['site_domain', 'user_identifier']);
        $usernameList = ContentUtility::extractMention($content);

        $userData = User::whereIn('username', $usernameList)->get();
        $mentionData = Mention::where('mention_type', $mentionType)->where('mention_id', $mentionId)->get();

        $linkList = [];
        $replaceList = [];
        foreach ($usernameList as $username) {
            // check mention record
            $user = $userData->where('username', $username)->first();
            $mentionUser = $mentionData->where('mention_user_id', $user?->id)->first();

            if (is_null($mentionUser)) {
                $replaceList[] = "@{$username} ";
                $linkList[] = sprintf('<a href="%s/u/404" class="fresns_user" target="_blank">@%s</a>', $config['site_domain'], $username);
                continue;
            }

            if ($config['user_identifier'] == 'uid') {
                // <a href="https://abc.com/u/{uid}" class="fresns_user" target="_blank">@nickname</a>
                $urlName = $user->uid;
            } else {
                // <a href="https://abc.com/u/{username}" class="fresns_user" target="_blank">@nickname</a>
                $urlName = $user->username;
            }

            $replaceList[] = "@{$user->nickname} ";

            $linkList[] = sprintf('<a href="%s/u/%s" class="fresns_user" target="_blank">@%s</a>', $config['site_domain'], $urlName, $user->nickname);
        }

        return str_replace($replaceList, $linkList, $content);
    }

    // Replace sticker
    public static function replaceSticker(string $content): string
    {
        $stickerCodeList = ContentUtility::extractMention($content);
        $stickerDataList = Sticker::whereIn('code', $stickerCodeList)->get();

        $replaceList = [];
        $linkList = [];
        foreach ($stickerCodeList as $sticker) {
            $replaceList[] = "[$sticker]";

            $currentSticker = $stickerDataList->where('code', $sticker)->first();
            if (is_null($currentSticker)) {
                $linkList[] = "[$sticker]";
            } else {
                $stickerUrl = FileHelper::fresnsFileUrlByTableColumn($sticker->image_file_id, $sticker->image_file_url);

                // <img src="$stickerUrl" class="fresns_sticker" alt="$sticker->code">
                $linkList[] = sprintf('<img src="%s" class="fresns_sticker" alt="%s" />', $stickerUrl, $currentSticker->code);
            }
        }

        return str_replace($replaceList, $linkList, $content);
    }

    // Handle and replace all
    public static function handleAndReplaceAll(string $content, ?int $mentionType = null, ?int $mentionId = null): string
    {
        // Replace hashtag
        // Replace url
        // Replace mention
        // Replace sticker
        $content = static::replaceHashtag($content);
        $content = static::replaceLink($content);
        if ($mentionType && $mentionId) {
            $content = static::replaceMention($content, $mentionType, $mentionId);
        }
        $content = static::replaceSticker($content);

        return $content;
    }

    // Save hashtag
    public static function saveHashtag(string $content, int $linkedType, int $linkedId)
    {
        $hashtagArr = ContentUtility::extractHashtag($content);

        // add hashtag data
        foreach ($hashtagArr as $hashtag) {
            Hashtag::firstOrCreate([
                'name' => $hashtag,
            ], [
                'slug' => StrHelper::slug($hashtag),
            ]);
        }

        // add hashtag linked
        $hashtagIdArr = Hashtag::whereIn('name', $hashtagArr)->pluck('id')->toArray();

        $hashtagLinkedData = [];
        foreach ($hashtagIdArr as $hashtagId) {
            $hashtagLinkedData[] = [
                'linked_type' => $linkedType,
                'linked_id' => $linkedId,
                'hashtag_id' => $hashtagId,
            ];
        }

        HashtagLinked::createMany($hashtagLinkedData);

        return;
    }

    // Save link
    public static function saveLink(string $content, int $linkedType, int $linkedId)
    {
        $urlArr = ContentUtility::extractLink($content);

        // add domain data
        foreach ($urlArr as $url) {
            Domain::firstOrCreate([
                'host' => parse_url($url, PHP_URL_HOST),
            ], [
                'domain' => StrHelper::extractDomainByUrl($url),
            ]);
        }

        // add domain link data
        foreach ($urlArr as $url) {
            DomainLink::firstOrCreate([
                'link_url' => $url,
            ], [
                'domain_id' => Domain::withTrashed()->where('host', parse_url($url, PHP_URL_HOST))->value('id') ?? 0,
            ]);
        }

        // add domain link linked
        $urlIdArr = DomainLink::whereIn('link_url', $urlArr)->pluck('id')->toArray();
        $urlLinkedData = [];
        foreach ($urlIdArr as $urlId) {
            $urlLinkedData[] = [
                'linked_type' => $linkedType,
                'linked_id' => $linkedId,
                'link_id' => $urlId,
            ];
        }
        DomainLinkLinked::createMany($urlLinkedData);

        return;
    }

    // Save mention user
    public static function saveMention(string $content, int $mentionType, int $mentionId, int $authUserId)
    {
        $usernameArr = ContentUtility::extractMention($content);
        $userIdArr = User::whereIn('username', $usernameArr)->pluck('id')->toArray();

        $mentionData = [];
        foreach ($userIdArr as $userId) {
            $mentionData[] = [
                'user_id' => $authUserId,
                'mention_type' => $mentionType,
                'mention_id' => $mentionId,
                'mention_user_id' => $userId,
            ];
        }

        Mention::createMany($mentionData);

        return;
    }

    // Handle and save all
    public static function handleAndSaveAll(string $content, int $type, int $id, ?int $authUserId = null)
    {
        static::saveHashtag($content, $type, $id);
        static::saveUrl($content, $type, $id);

        if (! empty($authUserId)) {
            static::saveMention($content, $type, $id, $authUserId);
        }

        return;
    }

    // extend json handle
    public static function extendJsonHandle(array $extends, string $langTag): array
    {
        $extendsCollection = collect($extends);

        $extendArr = Extend::whereIn('eid', $extendsCollection->pluck('eid'))->isEnable()->get();

        $extendList = null;
        foreach ($extendArr as $extend) {
            $item['eid'] = $extend->eid;
            $item['canDelete'] = $extendsCollection->where('eid', $extend->eid)->value('canDelete');
            $item['rating'] = $extendsCollection->where('eid', $extend->eid)->value('rating');
            $item['frameType'] = $extend->frame_type;
            $item['framePosition'] = $extend->frame_position;
            $item['textContent'] = $extend->text_content;
            $item['textIsMarkdown'] = $extend->text_is_markdown;
            $item['cover'] = FileHelper::fresnsFileUrlByTableColumn($extend['cover_file_id'], $extend['cover_file_url']);
            $item['title'] = LanguageHelper::fresnsLanguageByTableId('extends', 'title', $extend->id, $langTag);
            $item['titleColor'] = $extend->title_color;
            $item['descPrimary'] = LanguageHelper::fresnsLanguageByTableId('extends', 'desc_primary', $extend->id, $langTag);
            $item['descPrimaryColor'] = $extend->desc_primary_color;
            $item['descSecondary'] = LanguageHelper::fresnsLanguageByTableId('extends', 'desc_secondary', $extend->id, $langTag);
            $item['descSecondaryColor'] = $extend->desc_secondary_color;
            $item['btnName'] = LanguageHelper::fresnsLanguageByTableId('extends', 'btn_name', $extend->id, $langTag);
            $item['type'] = $extend->extend_type;
            $item['target'] = $extend->extend_target;
            $item['value'] = $extend->extend_value;
            $item['support'] = $extend->extend_support;
            $item['moreJson'] = $extend->more_json;

            $extendList[] = $item;
        }

        return collect($extendList)->sortBy('rating')->toArray();
    }

    // read allow json handle
    public static function readAllowJsonHandle(array $readAllowConfig, string $langTag, string $timezone): array
    {
        $permission['users'] = null;
        if (empty($readAllowConfig['permission']['users'])) {
            $users = User::whereIn('uid', $readAllowConfig['permission']['users'])->first();
            foreach ($users as $user) {
                $userList = $user->getUserProfile($langTag, $timezone);
            }
            $permission['users'] = $userList;
        }

        $permission['roles'] = null;
        if (empty($readAllowConfig['permission']['roles'])) {
            $roles = Role::whereIn('id', $readAllowConfig['permission']['roles'])->first();
            foreach ($roles as $role) {
                $roleItem['rid'] = $role->id;
                $roleItem['nicknameColor'] = $role->nickname_color;
                $roleItem['name'] = LanguageHelper::fresnsLanguageByTableId('roles', 'name', $role->id, $langTag);
                $roleItem['nameDisplay'] = (bool) $role->is_display_name;
                $roleItem['icon'] = FileHelper::fresnsFileUrlByTableColumn($role->icon_file_id, $role->icon_file_url);
                $roleItem['iconDisplay'] = (bool) $role->is_display_icon;
                $roleItem['status'] = (bool) $role->is_enable;
                $roleList[] = $roleItem;
            }
            $permission['roles'] = $roleList;
        }

        $item['isAllow'] = (bool) $readAllowConfig['isAllow'];
        $item['proportion'] = $readAllowConfig['proportion'];
        $item['url'] = PluginHelper::fresnsPluginUrlByUnikey($readAllowConfig['pluginUnikey']);
        $item['btnName'] = collect($readAllowConfig['btnName'])->where('langTag', $langTag)->first()['name'] ?? null;
        $item['permission'] = $permission;

        return $item;
    }

    // user list json handle
    public static function userListJsonHandle(array $userListConfig, string $langTag): array
    {
        $item['isUserList'] = (bool) $userListConfig['isUserList'];
        $item['userListName'] = collect($userListConfig['userListName'])->where('langTag', $langTag)->first()['name'] ?? null;
        $item['url'] = PluginHelper::fresnsPluginUrlByUnikey($userListConfig['pluginUnikey']);

        return $item;
    }

    // comment btn json handle
    public static function commentBtnJsonHandle(array $commentBtnConfig, string $langTag): array
    {
        $item['isCommentBtn'] = (bool) $commentBtnConfig['isCommentBtn'];
        $item['btnName'] = collect($commentBtnConfig['btnName'])->where('langTag', $langTag)->first()['name'] ?? null;
        $item['btnStyle'] = $commentBtnConfig['btnStyle'];
        $item['url'] = PluginHelper::fresnsPluginUrlByUnikey($commentBtnConfig['pluginUnikey']);

        return $item;
    }
}
