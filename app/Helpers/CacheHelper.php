<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Config;
use App\Models\File;
use App\Utilities\InteractionUtility;
use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    const NULL_CACHE_KEY_PREFIX = 'null_key_';
    const NULL_CACHE_COUNT = 3;

    // cache time
    public static function fresnsCacheTimeByFileType(?int $fileType = null)
    {
        if (empty($fileType)) {
            $digital = rand(6, 72);

            return now()->addHours($digital);
        }

        if ($fileType != File::TYPE_ALL) {
            $fileConfig = FileHelper::fresnsFileStorageConfigByType($fileType);

            if (! $fileConfig['antiLinkStatus']) {
                $digital = rand(72, 168);

                return now()->addHours($digital);
            }

            $cacheTime = now()->addMinutes($fileConfig['antiLinkExpire'] - 1);

            return $cacheTime;
        }

        $imageConfig = FileHelper::fresnsFileStorageConfigByType(File::TYPE_IMAGE);
        $videoConfig = FileHelper::fresnsFileStorageConfigByType(File::TYPE_VIDEO);
        $audioConfig = FileHelper::fresnsFileStorageConfigByType(File::TYPE_AUDIO);
        $documentConfig = FileHelper::fresnsFileStorageConfigByType(File::TYPE_DOCUMENT);

        $antiLinkExpire = [
            $imageConfig['antiLinkStatus'] ? $imageConfig['antiLinkExpire'] : 0,
            $videoConfig['antiLinkStatus'] ? $videoConfig['antiLinkExpire'] : 0,
            $audioConfig['antiLinkStatus'] ? $audioConfig['antiLinkExpire'] : 0,
            $documentConfig['antiLinkStatus'] ? $documentConfig['antiLinkExpire'] : 0,
        ];

        $newAntiLinkExpire = array_filter($antiLinkExpire);

        if (empty($newAntiLinkExpire)) {
            $digital = rand(6, 72);

            return now()->addHours($digital);
        }

        $minAntiLinkExpire = min($newAntiLinkExpire);

        $cacheTime = now()->addMinutes($minAntiLinkExpire - 1);

        return $cacheTime;
    }

    // get null cache key
    public static function getNullCacheKey(string $cacheKey)
    {
        return CacheHelper::NULL_CACHE_KEY_PREFIX.$cacheKey;
    }

    // null cache count
    public static function nullCacheCount(string $cacheKey, string $nullCacheKey, ?int $cacheMinutes = null)
    {
        Cache::pull($cacheKey);

        $currentCacheKeyNullNum = (int) Cache::get($nullCacheKey);

        $now = $cacheMinutes ? now()->addMinutes($cacheMinutes) : CacheHelper::fresnsCacheTimeByFileType();

        Cache::put($nullCacheKey, ++$currentCacheKeyNullNum, $now);
    }

    /**
     * clear all cache.
     */
    public static function clearAllCache(?array $tags = [])
    {
        // fresnsSystems
        // fresnsConfigs
        // fresnsLanguages
        // fresnsModels
        // fresnsUserInteraction
        // fresnsApiExtensions
        // fresnsApiData
        // fresnsWebData

        Cache::flush();
        \Artisan::call('cache:clear');
        \Artisan::call('clear-compiled');
        \Artisan::call('config:cache');
        \Artisan::call('event:cache');
        \Artisan::call('optimize:clear');
        \Artisan::call('route:clear');
        \Artisan::call('schedule:clear-cache');
        \Artisan::call('view:cache');

        // time of the latest cache
        $cacheConfig = Config::where('item_key', 'cache_datetime')->firstOrNew();
        $cacheConfig->item_value = now();
        $cacheConfig->item_type = 'string';
        $cacheConfig->item_tag = 'systems';
        $cacheConfig->is_multilingual = 0;
        $cacheConfig->is_custom = 0;
        $cacheConfig->is_api = 1;
        $cacheConfig->save();
    }

    /**
     * forget fresns config.
     */
    public static function forgetFresnsConfig()
    {
        Cache::forget('fresns_panel_path');
        Cache::forget('fresns_news');
        Cache::forget('fresns_current_version');
        Cache::forget('fresns_new_version');
        Cache::forget('fresns_database_timezone');
        Cache::forget('fresns_database_datetime');
        Cache::forget('fresns_crontab_items');
        Cache::forget('fresns_default_langTag');
        Cache::forget('fresns_default_timezone');
        Cache::forget('fresns_lang_tags');
        // Cache::forget("fresns_config_*");
        // Cache::forget("fresns_config_keys_*");
        // Cache::forget("fresns_config_tag_*");
        Cache::forget('fresns_content_block_words'); // fresns_{$type}_block_words
        Cache::forget('fresns_user_block_words'); // fresns_{$type}_block_words
        Cache::forget('fresns_conversation_block_words'); // fresns_{$type}_block_words
        Cache::forget('fresns_content_ban_words');
        Cache::forget('fresns_content_review_words');
        Cache::forget('fresns_user_ban_words');
        Cache::forget('fresns_conversation_ban_words');
    }

    /**
     * forget fresns keys.
     */
    public static function forgetFresnsKeys(array $keys)
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * forget fresns multilingual info.
     */
    public static function forgetFresnsMultilingual(string $cacheName)
    {
        $langTagArr = ConfigHelper::fresnsConfigLangTags();

        foreach ($langTagArr as $langTag) {
            $cacheKey = "{$cacheName}_{$langTag}";

            Cache::forget($cacheKey);
        }
    }

    /**
     * forget fresns model.
     *
     * fresns_model_account_{$aid}
     * fresns_model_user_{$uidOrUsername}
     * fresns_model_group_{$gid}
     * fresns_model_hashtag_{$hid}
     * fresns_model_post_{$pid}
     * fresns_model_comment_{$cid}
     * fresns_model_file_{$fid}
     * fresns_model_extend_{$eid}
     */
    public static function forgetFresnsModel(string $modelName, string|int $fsid)
    {
        if (StrHelper::isPureInt($fsid)) {
            $model = PrimaryHelper::fresnsModelById($modelName, $fsid);

            $modelFsid = match ($modelName) {
                'config' => $model?->item_key,
                'account' => $model?->aid,
                'user' => $model?->uid,
                'group' => $model?->aid,
                'hashtag' => $model?->slug,
                'post' => $model?->pid,
                'comment' => $model?->cid,
                'file' => $model?->fid,
                'extend' => $model?->eid,

                default => null,
            };

            $fsidCacheKey = "fresns_model_{$modelName}_{$modelFsid}";
            $idCacheKey = "fresns_model_{$modelName}_{$fsid}";
        } else {
            $model = PrimaryHelper::fresnsModelByFsid($modelName, $fsid);

            $fsidCacheKey = "fresns_model_{$modelName}_{$fsid}";
            $idCacheKey = "fresns_model_{$modelName}_{$model?->id}";
        }

        if ($modelName == 'user') {
            Cache::forget("fresns_model_user_{$model?->uid}");
            Cache::forget("fresns_model_user_{$model?->username}");
        }

        Cache::forget($fsidCacheKey);
        Cache::forget($idCacheKey);
    }

    /**
     * forget table column lang content.
     *
     * fresns_{$tableName}_{$tableColumn}_{$tableId}_{$langTag}
     */
    public static function forgetFresnsTableColumnLangContent(string $tableName, string $tableColumn, int $tableId)
    {
        $cacheKey = "fresns_{$tableName}_{$tableColumn}_{$tableId}";

        CacheHelper::forgetFresnsMultilingual($cacheKey);
    }

    // forget fresns account
    public static function forgetFresnsAccount(?string $aid = null)
    {
        if (empty($aid)) {
            return;
        }

        CacheHelper::forgetFresnsMultilingual("fresns_api_account_{$aid}");
        CacheHelper::forgetFresnsMultilingual("fresns_api_account_wallet_extends_{$aid}");
        CacheHelper::forgetFresnsModel('account', $aid);
    }

    // forget fresns user
    public static function forgetFresnsUser(?int $uid = null)
    {
        if (empty($uid)) {
            return;
        }

        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($uid);

        // user panel
        $langTagArr = ConfigHelper::fresnsConfigLangTags();

        $langCacheKeyArr = [];
        foreach ($langTagArr as $langTag) {
            $cacheKey = "fresns_api_user_panel_publish_{$uid}_{$langTag}";

            $langCacheKeyArr[] = $cacheKey;
        }

        $utcArr = ConfigHelper::fresnsConfigByItemKey('utc');

        foreach ($langCacheKeyArr as $langCacheKey) {
            foreach ($utcArr as $utc) {
                $cacheKey = "{$langCacheKey}_{$utc['value']}";

                Cache::forget($cacheKey);
            }
        }

        CacheHelper::forgetFresnsMultilingual("fresns_api_user_panel_extends_{$uid}");
        CacheHelper::forgetFresnsMultilingual("fresns_api_user_manages_{$userId}");
        CacheHelper::forgetFresnsMultilingual("fresns_api_post_manages_{$userId}");
        CacheHelper::forgetFresnsMultilingual("fresns_api_comment_manages_{$userId}");
        CacheHelper::forgetFresnsKeys([
            "fresns_api_user_panel_conversations_{$uid}",
            "fresns_api_user_panel_notifications_{$uid}",
            "fresns_api_user_panel_drafts_{$uid}",
            "fresns_user_main_role_{$userId}",
            "fresns_seo_user_{$userId}",
        ]);

        // user data
        CacheHelper::forgetFresnsMultilingual("fresns_api_user_{$uid}");
        CacheHelper::forgetFresnsModel('user', $uid);
    }

    // forget fresns cache
    public static function forgetFresnsSpecifyType(string $type, string|int $fsid, ?int $userId = null)
    {
        switch ($type) {
            // account | $fsid = $aid
            case 'account':
                if (empty($fsid)) {
                    return;
                }

                self::forgetFresnsAccount($fsid);
            break;

            // user | $fsid = $uid
            case 'user':
                if (empty($fsid)) {
                    return;
                }

                self::forgetFresnsUser($fsid);
            break;

            // group | $fsid = $gid
            case 'group':
                $groupId = PrimaryHelper::fresnsGroupIdByGid($fsid);

                self::forgetFresnsMultilingual("fresns_api_group_{$fsid}");
                self::forgetFresnsKeys([
                    "fresns_user_follow_group_model_{$userId}",
                    "fresns_user_filter_groups_{$userId}",
                    "fresns_user_all_group_{$userId}",
                    "fresns_group_admins_{$groupId}",
                    "fresns_seo_group_{$groupId}",
                    'fresns_guest_all_group',
                    'fresns_guest_filter_groups',
                    'fresns_private_groups',
                ]);
                self::forgetFresnsModel('group', $fsid);
            break;

            // hashtag | $fsid = $hid
            case 'hashtag':
                $hashtagId = PrimaryHelper::fresnsHashtagIdByHid($fsid);

                Cache::forget("fresns_seo_hashtag_{$hashtagId}");
                self::forgetFresnsMultilingual("fresns_api_hashtag_{$fsid}");
                self::forgetFresnsModel('hashtag', $fsid);
            break;

            // post | $fsid = $pid
            case 'post':
                $postId = PrimaryHelper::fresnsPostIdByPid($fsid);

                Cache::forget("fresns_seo_post_{$postId}");
                self::forgetFresnsMultilingual("fresns_api_post_{$fsid}");
                self::forgetFresnsMultilingual("fresns_api_post_{$postId}_top_comment");
                self::forgetFresnsModel('post', $fsid);
            break;

            // comment | $fsid = $cid
            case 'comment':
                $commentId = PrimaryHelper::fresnsCommentIdByCid($fsid);

                Cache::forget("fresns_seo_comment_{$commentId}");
                self::forgetFresnsMultilingual("fresns_api_comment_{$fsid}");
                self::forgetFresnsMultilingual("fresns_api_comment_{$commentId}_sub_comment");
                self::forgetFresnsModel('comment', $fsid);
            break;

            // guest
            case 'guest':
                self::forgetFresnsMultilingual('fresns_api_guest_user_manages');
                self::forgetFresnsMultilingual('fresns_api_guest_post_manages');
                self::forgetFresnsMultilingual('fresns_api_guest_comment_manages');
                self::forgetFresnsKeys([
                    'fresns_guest_all_group',
                    'fresns_guest_filter_groups',
                    'fresns_private_groups',
                ]);
            break;

            // default
            default:
                return;
            break;
        }
    }

    /**
     * forget fresns interaction.
     */
    public static function forgetFresnsInteraction(int $type, int $id, int $userId)
    {
        CacheHelper::forgetFresnsKeys([
            "fresns_interaction_status_{$type}_{$id}_{$userId}",
            "fresns_user_follow_array_{$type}_{$userId}",
            "fresns_user_block_array_{$type}_{$userId}",
        ]);

        if ($type == InteractionUtility::TYPE_GROUP) {
            CacheHelper::forgetFresnsKeys([
                "fresns_user_follow_group_model_{$userId}",
                "fresns_user_filter_groups_{$userId}",
                "fresns_user_all_group_{$userId}",
            ]);
        }
    }

    /**
     * fresns cache group.
     */
    // fresns_api_user_{$uid}_{$langTag}
    // fresns_api_group_{$gid}_{$langTag}
    // fresns_api_hashtag_{$hid}_{$langTag}
    // fresns_api_post_{$pid}_{$langTag}
    // fresns_api_post_{$pid}_{$userId}_{$langTag}
    // fresns_api_comment_{$cid}_{$langTag}
    // fresns_api_comment_{$cid}_{$userId}_{$langTag}

    // fresns_seo_user_{$id}
    // fresns_seo_group_{$id}
    // fresns_seo_hashtag_{$id}
    // fresns_seo_post_{$id}
    // fresns_seo_comment_{$id}

    // fresns_code_messages_{$unikey}_{$langTag}
    // fresns_api_key_{$appId}
    // fresns_api_token_{$platformId}_{$aid}_{$uid}
    // fresns_api_stickers_{$langTag}
    // fresns_plugin_url_{$unikey}
}
