<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\File;
use Illuminate\Support\Facades\Cache;

class CacheHelper
{
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

    /**
     * clear all cache.
     */
    public static function clearAllCache()
    {
        Cache::flush();
        \Artisan::call('clear-compiled');
        \Artisan::call('cache:clear');
        \Artisan::call('config:cache');
        \Artisan::call('event:cache');
        \Artisan::call('optimize:clear');
        \Artisan::call('route:clear');
        \Artisan::call('schedule:clear-cache');
        \Artisan::call('view:cache');
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
        Cache::forget('fresns_content_block_words');
        Cache::forget('fresns_user_block_words');
        Cache::forget('fresns_conversation_block_words');
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
     * forget fresns interaction.
     */
    public static function forgetFresnsInteraction(int $type, int $userId)
    {
        CacheHelper::forgetFresnsKeys([
            "fresns_user_follow_array_{$type}_{$userId}",
            "fresns_user_follow_group_model_{$userId}",
            "fresns_user_filter_groups_{$userId}",
            "fresns_user_block_array_{$type}_{$userId}",
            "fresns_user_main_role_{$userId}",
            "fresns_user_all_group_{$userId}",
        ]);
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

    // forget fresns api account
    public static function forgetApiAccount(?string $aid = null)
    {
        if (empty($aid)) {
            return;
        }

        CacheHelper::forgetFresnsMultilingual("fresns_api_account_{$aid}");
        CacheHelper::forgetFresnsMultilingual("fresns_api_account_wallet_extends_{$aid}");
        CacheHelper::forgetFresnsModel('account', $aid);
    }

    // forget fresns api user
    public static function forgetApiUser(?int $uid = null)
    {
        if (empty($uid)) {
            return;
        }

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
                $cacheKey = "{$langCacheKey}_{$utc}";

                Cache::forget($cacheKey);
            }
        }

        CacheHelper::forgetFresnsMultilingual("fresns_api_user_panel_plugins_{$uid}");
        CacheHelper::forgetFresnsKeys([
            "fresns_api_user_panel_conversations_{$uid}",
            "fresns_api_user_panel_notifications_{$uid}",
            "fresns_api_user_panel_drafts_{$uid}",
        ]);

        // user data
        CacheHelper::forgetFresnsMultilingual("fresns_api_user_{$uid}");
        CacheHelper::forgetFresnsModel('user', $uid);

    }

    /**
     * fresns all cache.
     */

    // fresns_group_admins_{$groupId}

    // fresns_interaction_status_{$markType}_{$markId}_{$userId}

    // fresns_api_user_{$uid}_{$langTag}
    // fresns_api_group_{$gid}_{$langTag}
    // fresns_api_hashtag_{$hid}_{$langTag}
    // fresns_api_post_{$pid}_{$langTag}
    // fresns_api_comment_{$cid}_{$langTag}

    // fresns_api_{$type}_manages_guest_{$langTag}
    // fresns_api_{$type}_manages_{$authUserId}_{$langTag}

    // fresns_seo_user_{$id}
    // fresns_seo_group_{$id}
    // fresns_seo_hashtag_{$id}
    // fresns_seo_post_{$id}
    // fresns_seo_comment_{$id}

    // fresns_plugin_{$unikey}_url
    // fresns_plugin_{$unikey}_{$parameterKey}_url
    // fresns_api_key_{$appId}
    // fresns_api_token_{$platformId}_{$aid}_{$uid}
    // fresns_api_stickers_{$langTag}

    // fresns_guest_all_group
    // fresns_guest_filter_groups
    // fresns_private_groups
}
