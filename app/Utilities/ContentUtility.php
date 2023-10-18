<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\ArchiveUsage;
use App\Models\BlockWord;
use App\Models\Comment;
use App\Models\CommentAppend;
use App\Models\CommentLog;
use App\Models\Domain;
use App\Models\DomainLink;
use App\Models\DomainLinkUsage;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\HashtagUsage;
use App\Models\Language;
use App\Models\Mention;
use App\Models\OperationUsage;
use App\Models\Post;
use App\Models\PostAppend;
use App\Models\PostAuth;
use App\Models\PostLog;
use App\Models\Role;
use App\Models\Sticker;
use App\Models\User;
use Illuminate\Support\Arr;
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

        $urlList = array_map(function($url) {
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
                '</script>' => '&lt;/script&gt;',
                '<iframe>' => '&lt;iframe&gt;',
                '</iframe>' => '&lt;/iframe&gt;',
                '"javascript' => '&#34;javascript',
                "'javascript" => '&#39;javascript',
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

    // handle read json
    public static function handleReadJson(?array $readConfig, string $langTag): ?array
    {
        if (! $readConfig) {
            return null;
        }

        $permissions['users'] = [];
        if (isset($readConfig['permissions']['users']) && $readConfig['permissions']['users']) {
            $users = User::whereIn('uid', $readConfig['permissions']['users'])->get();
            $userList = [];
            foreach ($users as $user) {
                $userList[] = $user->getUserProfile();
            }
            $permissions['users'] = $userList;
        }

        $permissions['roles'] = [];
        if (isset($readConfig['permissions']['roles']) && $readConfig['permissions']['roles']) {
            $roles = Role::whereIn('id', $readConfig['permissions']['roles'])->get();
            $roleList = [];
            foreach ($roles as $role) {
                $roleItem['rid'] = $role->id;
                $roleItem['nicknameColor'] = $role->nickname_color;
                $roleItem['name'] = LanguageHelper::fresnsLanguageByTableId('roles', 'name', $role->id, $langTag);
                $roleItem['nameDisplay'] = (bool) $role->is_display_name;
                $roleItem['icon'] = FileHelper::fresnsFileUrlByTableColumn($role->icon_file_id, $role->icon_file_url);
                $roleItem['iconDisplay'] = (bool) $role->is_display_icon;
                $roleItem['status'] = (bool) $role->is_enabled;

                $roleList[] = $roleItem;
            }
            $permissions['roles'] = $roleList;
        }

        $item['isReadLocked'] = (bool) $readConfig['isReadLocked'];
        $item['previewPercentage'] = $readConfig['previewPercentage'] ?? 0;
        $item['pluginUrl'] = PluginHelper::fresnsPluginUrlByFskey($readConfig['pluginFskey']);
        $item['pluginFskey'] = $readConfig['pluginFskey'];
        $item['defaultLangBtnName'] = collect($readConfig['btnName'])->where('langTag', $langTag)->first()['name'] ?? null;
        $item['btnName'] = $readConfig['btnName'];
        $item['permissions'] = $permissions;

        return $item;
    }

    // handle user list json
    public static function handleUserListJson(?array $userListConfig, string $langTag): ?array
    {
        if (! $userListConfig) {
            return null;
        }

        $item['isUserList'] = (bool) $userListConfig['isUserList'];
        $item['defaultLangUserListName'] = collect($userListConfig['userListName'])->where('langTag', $langTag)->first()['name'] ?? null;
        $item['userListName'] = $userListConfig['userListName'];
        $item['pluginUrl'] = PluginHelper::fresnsPluginUrlByFskey($userListConfig['pluginFskey']);
        $item['pluginFskey'] = $userListConfig['pluginFskey'];

        return $item;
    }

    // handle comment btn json
    public static function handleCommentBtnJson(?array $commentBtnConfig, string $langTag): ?array
    {
        if (! $commentBtnConfig) {
            return null;
        }

        $item['isCommentBtn'] = (bool) $commentBtnConfig['isCommentBtn'];
        $item['defaultLangBtnName'] = collect($commentBtnConfig['btnName'])->where('langTag', $langTag)->first()['name'] ?? null;
        $item['btnName'] = $commentBtnConfig['btnName'];
        $item['btnStyle'] = $commentBtnConfig['btnStyle'] ?? null;
        $item['pluginUrl'] = PluginHelper::fresnsPluginUrlByFskey($commentBtnConfig['pluginFskey']);
        $item['pluginFskey'] = $commentBtnConfig['pluginFskey'];

        return $item;
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
                'plugin_fskey' => $operation['fskey'] ?? $operationModel->plugin_fskey,
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
                'plugin_fskey' => $archive['fskey'] ?? $archiveModel->plugin_fskey,
            ]);
        }
    }

    // save extend usages
    // $extends = [{"eid": "eid", "canDelete": true, "rating": 9, "fskey": null}]
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
                'rating' => $extend['rating'] ?? 9,
                'plugin_fskey' => $extend['fskey'] ?? $extendModel->plugin_fskey,
            ]);
        }
    }

    // release lang name
    public static function releaseLangName(string $tableName, string $tableColumn, int $tableId, array $langContentArr): ?string
    {
        $defaultLangTag = ConfigHelper::fresnsConfigDefaultLangTag();

        foreach ($langContentArr as $lang) {
            Language::updateOrCreate([
                'table_name' => $tableName,
                'table_column' => $tableColumn,
                'table_id' => $tableId,
                'lang_tag' => $lang['langTag'],
            ], [
                'lang_content' => $lang['name'],
            ]);

            if ($lang['langTag'] == $defaultLangTag) {
                $defaultLangName = $lang['name'];
            }
        }

        return $defaultLangName ?? null;
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
                'rating' => $fileUsage->rating,
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
                'rating' => $extend->rating,
                'plugin_fskey' => $extend->plugin_fskey,
            ];

            ExtendUsage::create($extendDataItem);
        }
    }

    // release auth users and roles
    public static function releaseReadAuthUsersAndRoles(int $postId, array $permArr): void
    {
        if (empty($permArr)) {
            return;
        }

        if ($permArr['users']) {
            PostAuth::where('post_id', $postId)->where('type', PostAuth::TYPE_USER)->where('is_initial', 1)->delete();

            foreach ($permArr['users'] as $userId) {
                PostAuth::withTrashed()->updateOrCreate([
                    'post_id' => $postId,
                    'type' => PostAuth::TYPE_USER,
                    'object_id' => $userId,
                ], [
                    'is_initial' => 1,
                    'deleted_at' => null,
                ]);
            }
        }

        if ($permArr['roles']) {
            PostAuth::where('post_id', $postId)->where('type', PostAuth::TYPE_ROLE)->where('is_initial', 1)->delete();

            foreach ($permArr['roles'] as $roleId) {
                PostAuth::withTrashed()->updateOrCreate([
                    'post_id' => $postId,
                    'type' => PostAuth::TYPE_ROLE,
                    'object_id' => $roleId,
                ], [
                    'is_initial' => 1,
                    'deleted_at' => null,
                ]);
            }
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
                'plugin_fskey' => $operation->plugin_fskey,
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
                'plugin_fskey' => $archive->plugin_fskey,
            ];

            ArchiveUsage::create($archiveDataItem);
        }
    }

    // release post
    public static function releasePost(PostLog $postLog): Post
    {
        if ($postLog->post_id) {
            $oldPost = PrimaryHelper::fresnsModelById('post', $postLog->post_id);
        }

        $post = Post::updateOrCreate([
            'id' => $postLog->post_id,
        ], [
            'user_id' => $postLog->user_id,
            'parent_id' => $postLog->parent_post_id ?? 0,
            'group_id' => $postLog->group_id ?? 0,
            'title' => $postLog->title,
            'content' => $postLog->content,
            'is_markdown' => $postLog->is_markdown,
            'is_anonymous' => $postLog->is_anonymous,
            'map_longitude' => $postLog->map_json['longitude'] ?? null,
            'map_latitude' => $postLog->map_json['latitude'] ?? null,
        ]);

        $readBtnName = null;
        if (empty($postLog->read_json)) {
            Language::where('table_name', 'post_appends')->where('table_column', 'read_btn_name')->where('table_id', $post->id)->delete();
        } else {
            $readBtnName = ContentUtility::releaseLangName('post_appends', 'read_btn_name', $post->id, $postLog->read_json['btnName']);
        }

        $userListName = null;
        if (empty($postLog->user_list_json)) {
            Language::where('table_name', 'post_appends')->where('table_column', 'user_list_name')->where('table_id', $post->id)->delete();
        } else {
            $userListName = ContentUtility::releaseLangName('post_appends', 'user_list_name', $post->id, $postLog->user_list_json['userListName']);
        }

        $commentBtnName = null;
        if (empty($postLog->comment_btn_json)) {
            Language::where('table_name', 'post_appends')->where('table_column', 'comment_btn_name')->where('table_id', $post->id)->delete();
        } else {
            $commentBtnName = ContentUtility::releaseLangName('post_appends', 'comment_btn_name', $post->id, $postLog->comment_btn_json['btnName']);
        }

        $postAppend = PostAppend::updateOrCreate([
            'post_id' => $post->id,
        ], [
            'is_plugin_editor' => $postLog->is_plugin_editor,
            'editor_fskey' => $postLog->editor_fskey,
            'is_read_locked' => $postLog->read_json['isReadLocked'] ?? false,
            'read_pre_percentage' => $postLog->read_json['previewPercentage'] ?? null,
            'read_btn_name' => $readBtnName,
            'read_plugin_fskey' => $postLog->read_json['pluginFskey'] ?? null,
            'is_user_list' => $postLog->user_list_json['isUserList'] ?? false,
            'user_list_name' => $userListName,
            'user_list_plugin_fskey' => $postLog->user_list_json['pluginFskey'] ?? null,
            'is_comment_disabled' => $postLog->is_comment_disabled ?? true,
            'is_comment_private' => $postLog->is_comment_private ?? true,
            'is_comment_btn' => $postLog->comment_btn_json['isCommentBtn'] ?? false,
            'comment_btn_name' => $commentBtnName,
            'comment_btn_style' => $postLog->comment_btn_json['btnStyle'] ?? null,
            'comment_btn_plugin_fskey' => $postLog->comment_btn_json['pluginFskey'] ?? null,
            'map_id' => $postLog->map_json['mapId'] ?? null,
            'map_json' => $postLog->map_json ?? null,
            'map_continent_code' => $postLog->map_json['continentCode'] ?? null,
            'map_country_code' => $postLog->map_json['countryCode'] ?? null,
            'map_region_code' => $postLog->map_json['regionCode'] ?? null,
            'map_city_code' => $postLog->map_json['cityCode'] ?? null,
            'map_zip' => $postLog->map_json['zip'] ?? null,
            'map_poi_id' => $postLog->map_json['poiId'] ?? null,
        ]);

        ContentUtility::releaseFileUsages('post', $postLog->id, $post->id);
        ContentUtility::releaseExtendUsages('post', $postLog->id, $post->id);
        ContentUtility::releaseReadAuthUsersAndRoles($post->id, $postLog->read_json['permissions'] ?? []);
        ContentUtility::releaseArchiveUsages('post', $postLog->id, $post->id);
        ContentUtility::releaseOperationUsages('post', $postLog->id, $post->id);

        if (empty($postLog->post_id)) {
            ContentUtility::handleAndSaveAllInteraction($postLog->content, Mention::TYPE_POST, $post->id, $postLog->user_id);
            InteractionUtility::publishStats('post', $post->id, 'increment');
        } else {
            if ($postLog->group_id != $oldPost->group_id) {
                Group::where('id', $oldPost->group_id)->decrement('post_count');
                Group::where('id', $postLog->group_id)->increment('post_count');

                $groupCommentCount = Comment::where('post_id', $post->id)->count();

                Group::where('id', $postLog->group_id)->increment('comment_count', $groupCommentCount);
                Group::where('id', $oldPost->group_id)->decrement('comment_count', $groupCommentCount);
            }

            InteractionUtility::editStats('post', $post->id, 'decrement');

            HashtagUsage::where('usage_type', HashtagUsage::TYPE_POST)->where('usage_id', $post->id)->delete();
            DomainLinkUsage::where('usage_type', DomainLinkUsage::TYPE_POST)->where('usage_id', $post->id)->delete();
            Mention::where('user_id', $postLog->user_id)->where('mention_type', Mention::TYPE_POST)->where('mention_id', $post->id)->delete();

            ContentUtility::handleAndSaveAllInteraction($postLog->content, Mention::TYPE_POST, $post->id, $postLog->user_id);
            InteractionUtility::editStats('post', $post->id, 'increment');

            $post->update([
                'latest_edit_at' => now(),
            ]);
            $postAppend->increment('edit_count');

            CacheHelper::forgetFresnsModel('post', $post->id);
            CacheHelper::forgetFresnsMultilingual("fresns_api_post_{$post->pid}", 'fresnsPosts');
            CacheHelper::forgetFresnsKeys([
                "fresns_api_post_{$post->pid}_list_content",
                "fresns_api_post_{$post->pid}_detail_content",
            ], 'fresnsPosts');
        }

        $postLog->update([
            'post_id' => $post->id,
            'state' => PostLog::STATE_SUCCESS,
        ]);

        $author = PrimaryHelper::fresnsModelById('user', $post->user_id);
        $author->update([
            'last_post_at' => now(),
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
        if (! $parentComment) {
            $topParentId = $parentComment?->top_parent_id ?? 0;
        }

        $comment = Comment::updateOrCreate([
            'id' => $commentLog->comment_id,
        ], [
            'user_id' => $commentLog->user_id,
            'post_id' => $commentLog->post_id,
            'top_parent_id' => $topParentId,
            'parent_id' => $commentLog->parent_comment_id ?? 0,
            'content' => $commentLog->content,
            'is_markdown' => $commentLog->is_markdown,
            'is_anonymous' => $commentLog->is_anonymous,
            'map_longitude' => $commentLog->map_json['longitude'] ?? null,
            'map_latitude' => $commentLog->map_json['latitude'] ?? null,
        ]);

        $commentAppend = CommentAppend::updateOrCreate([
            'comment_id' => $comment->id,
        ], [
            'is_plugin_editor' => $commentLog->is_plugin_editor,
            'editor_fskey' => $commentLog->editor_fskey,
            'map_id' => $commentLog->map_json['mapId'] ?? null,
            'map_json' => $commentLog->map_json ?? null,
            'map_continent_code' => $commentLog->map_json['continentCode'] ?? null,
            'map_country_code' => $commentLog->map_json['countryCode'] ?? null,
            'map_region_code' => $commentLog->map_json['regionCode'] ?? null,
            'map_city_code' => $commentLog->map_json['cityCode'] ?? null,
            'map_zip' => $commentLog->map_json['zip'] ?? null,
            'map_poi_id' => $commentLog->map_json['poiId'] ?? null,
        ]);

        ContentUtility::releaseFileUsages('comment', $commentLog->id, $comment->id);
        ContentUtility::releaseExtendUsages('comment', $commentLog->id, $comment->id);
        ContentUtility::releaseArchiveUsages('comment', $commentLog->id, $comment->id);
        ContentUtility::releaseOperationUsages('comment', $commentLog->id, $comment->id);

        if (empty($commentLog->comment_id)) {
            ContentUtility::handleAndSaveAllInteraction($commentLog->content, Mention::TYPE_COMMENT, $comment->id, $commentLog->user_id);
            InteractionUtility::publishStats('comment', $comment->id, 'increment');
        } else {
            InteractionUtility::editStats('comment', $comment->id, 'decrement');

            HashtagUsage::where('usage_type', HashtagUsage::TYPE_COMMENT)->where('usage_id', $comment->id)->delete();
            DomainLinkUsage::where('usage_type', DomainLinkUsage::TYPE_COMMENT)->where('usage_id', $comment->id)->delete();
            Mention::where('user_id', $commentLog->user_id)->where('mention_type', Mention::TYPE_COMMENT)->where('mention_id', $comment->id)->delete();

            ContentUtility::handleAndSaveAllInteraction($commentLog->content, Mention::TYPE_COMMENT, $comment->id, $commentLog->user_id);
            InteractionUtility::editStats('comment', $comment->id, 'increment');

            $comment->update([
                'latest_edit_at' => now(),
            ]);
            $commentAppend->increment('edit_count');

            CacheHelper::forgetFresnsModel('comment', $comment->id);
            CacheHelper::forgetFresnsMultilingual("fresns_api_comment_{$comment->cid}", 'fresnsComments');
            CacheHelper::forgetFresnsKeys([
                "fresns_api_comment_{$comment->cid}_list_content",
                "fresns_api_comment_{$comment->cid}_detail_content",
            ], 'fresnsComments');
        }

        $commentLog->update([
            'comment_id' => $comment->id,
            'state' => CommentLog::STATE_SUCCESS,
        ]);

        $author = PrimaryHelper::fresnsModelById('user', $comment->user_id);
        $author->update([
            'last_comment_at' => now(),
        ]);

        $post = PrimaryHelper::fresnsModelById('post', $comment->post_id);
        $post->update([
            'latest_comment_at' => now(),
        ]);

        if ($comment->parent_id) {
            ContentUtility::parentCommentLatestCommentTime($comment->parent_id);
        }

        // send notification
        InteractionUtility::sendPublishNotification('comment', $comment->id);

        return $comment;
    }

    // parent comment latest release time
    public static function parentCommentLatestCommentTime(int $parentId): void
    {
        $comment = PrimaryHelper::fresnsModelById('comment', $parentId);

        $comment->update([
            'latest_comment_at' => now(),
        ]);

        // parent comment
        if ($comment?->parent_id) {
            ContentUtility::parentCommentLatestCommentTime($comment->parent_id);
        }
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
                'rating' => $fileUsage->rating,
                'account_id' => $fileUsage->account_id,
                'user_id' => $fileUsage->user_id,
                'remark' => $fileUsage->remark,
            ];

            FileUsage::create($fileDataItem);
        }

        // operations
        $operationUsages = OperationUsage::where('usage_type', $usageType)->where('usage_id', $primaryId)->get();

        foreach ($operationUsages as $operation) {
            $operationDataItem = [
                'usage_type' => $logUsageType,
                'usage_id' => $logId,
                'operation_id' => $operation->operation_id,
                'plugin_fskey' => $operation->plugin_fskey,
            ];

            OperationUsage::create($operationDataItem);
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
                'plugin_fskey' => $archive->plugin_fskey,
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
                'rating' => $extend->rating,
                'plugin_fskey' => $extend->plugin_fskey,
            ];

            ExtendUsage::create($extendDataItem);
        }
    }

    // generate post draft
    public static function generatePostDraft(Post $post): PostLog
    {
        $postLog = PostLog::where('post_id', $post->id)->whereIn('state', [PostLog::STATE_DRAFT, PostLog::STATE_UNDER_REVIEW, PostLog::STATE_FAILURE])->first();
        if ($postLog) {
            return $postLog;
        }

        // read json
        $readBtnNameArr = Language::where('table_name', 'post_appends')->where('table_column', 'read_btn_name')->where('table_id', $post->id)->get();
        $readBtnName = [];
        foreach ($readBtnNameArr as $btnName) {
            $item['langTag'] = $btnName->lang_tag;
            $item['name'] = $btnName->lang_content;
            $readBtnName[] = $item;
        }

        $readUserArr = PostAuth::where('post_id', $post->id)->where('is_initial', 1)->get()->groupBy('type');

        $readPermissions['users'] = $readUserArr->get(PostAuth::TYPE_USER)?->pluck('object_id')->all();
        $readPermissions['roles'] = $readUserArr->get(PostAuth::TYPE_ROLE)?->pluck('object_id')->all();

        $readJson['isReadLocked'] = $post->postAppend->is_read_locked;
        $readJson['btnName'] = $readBtnName;
        $readJson['previewPercentage'] = $post->postAppend->read_pre_percentage;
        $readJson['permissions'] = $readPermissions;
        $readJson['pluginFskey'] = $post->postAppend->read_plugin_fskey;

        // user list json
        $userListNameArr = Language::where('table_name', 'post_appends')->where('table_column', 'user_list_name')->where('table_id', $post->id)->get();
        $userListName = [];
        foreach ($userListNameArr as $name) {
            $item['langTag'] = $name->lang_tag;
            $item['name'] = $name->lang_content;
            $userListName[] = $item;
        }

        $userListJson['isUserList'] = $post->postAppend->is_user_list;
        $userListJson['userListName'] = $userListName;
        $userListJson['pluginFskey'] = $post->postAppend->user_list_plugin_fskey;

        // comment btn json
        $commentBtnNameArr = Language::where('table_name', 'post_appends')->where('table_column', 'comment_btn_name')->where('table_id', $post->id)->get();
        $commentBtnName = [];
        foreach ($commentBtnNameArr as $btnName) {
            $item['langTag'] = $btnName->lang_tag;
            $item['name'] = $btnName->lang_content;
            $commentBtnName[] = $item;
        }

        $commentBtnJson['isCommentBtn'] = $post->postAppend->is_comment_btn;
        $commentBtnJson['btnName'] = $commentBtnName;
        $commentBtnJson['pluginFskey'] = $post->postAppend->comment_btn_plugin_fskey;

        // post log
        $logData = [
            'user_id' => $post->user_id,
            'post_id' => $post->id,
            'parent_post_id' => $post->parent_id ?: null,
            'create_type' => 3,
            'is_plugin_editor' => $post->postAppend->is_plugin_editor,
            'editor_fskey' => $post->postAppend->editor_fskey,
            'group_id' => $post->group_id,
            'title' => $post->title,
            'content' => $post->content,
            'is_markdown' => $post->is_markdown,
            'is_anonymous' => $post->is_anonymous,
            'is_comment_disabled' => $post->postAppend->is_comment_disabled,
            'is_comment_private' => $post->postAppend->is_comment_private,
            'map_json' => $post->postAppend->map_json,
            'read_json' => $readJson,
            'user_list_json' => $userListJson,
            'comment_btn_json' => $commentBtnJson,
        ];

        $postLog = PostLog::create($logData);

        ContentUtility::batchCopyContentExtends('post', $post->id, $postLog->id);

        return $postLog;
    }

    // generate comment draft
    public static function generateCommentDraft(Comment $comment): CommentLog
    {
        $commentLog = CommentLog::where('comment_id', $comment->id)->whereIn('state', [CommentLog::STATE_DRAFT, CommentLog::STATE_UNDER_REVIEW, CommentLog::STATE_FAILURE])->first();
        if ($commentLog) {
            return $commentLog;
        }

        // comment log
        $logData = [
            'user_id' => $comment->user_id,
            'comment_id' => $comment->id,
            'post_id' => $comment->post_id,
            'parent_comment_id' => $comment->parent_id ?: null,
            'create_type' => 3,
            'is_plugin_editor' => $comment->commentAppend->is_plugin_editor,
            'editor_fskey' => $comment->commentAppend->editor_fskey,
            'content' => $comment->content,
            'is_markdown' => $comment->is_markdown,
            'is_anonymous' => $comment->is_anonymous,
            'map_json' => $comment->commentAppend->map_json,
        ];

        $commentLog = CommentLog::create($logData);

        ContentUtility::batchCopyContentExtends('comment', $comment->id, $commentLog->id);

        return $commentLog;
    }

    // Replace block words
    public static function replaceBlockWords(string $type, ?string $content = null): ?string
    {
        if (empty($content)) {
            return null;
        }

        $cacheKey = "fresns_{$type}_block_words";
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return $content;
        }

        $blockWords = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($blockWords)) {
            $blockWords = match ($type) {
                'content' => BlockWord::where('content_mode', '!=', 1)->get(['word', 'replace_word']),
                'user' => BlockWord::where('user_mode', '!=', 1)->get(['word', 'replace_word']),
                'conversation' => BlockWord::where('conversation_mode', '!=', 1)->get(['word', 'replace_word']),
            };

            CacheHelper::put($blockWords, $cacheKey, $cacheTag);
        }

        if (empty($blockWords)) {
            return $content;
        }

        $newContent = str_ireplace($blockWords->pluck('word')->toArray(), $blockWords->pluck('replace_word')->toArray(), $content);

        return $newContent;
    }
}
