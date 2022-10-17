<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    // cache time
    public static function fresnsCacheTimeByFileType(?int $fileType = null)
    {
        if (empty($fileType)) {
            return now()->addHours();
        }

        $fileConfig = FileHelper::fresnsFileStorageConfigByType($fileType);

        if (! $fileConfig['antiLinkStatus']) {
            return now()->addHours();
        }

        $cacheTime = now()->addMinutes($fileConfig['antiLinkExpire'] - 1);

        return $cacheTime;
    }

    /**
     * clear all cache.
     */
    public static function clearAllCache()
    {
        Cache::flush();
        \Artisan::call('view:cache');
        \Artisan::call('config:cache');
    }

    /**
     * forget fresns config.
     */
    public static function forgetFresnsConfig()
    {
        Cache::forget('fresns_crontab_items');
        Cache::forget('fresns_default_langTag');
        Cache::forget('fresns_default_timezone');
        Cache::forget('fresns_lang_tags');
        // Cache::forget("fresns_config_*");
        // Cache::forget("fresns_config_keys_*");
        // Cache::forget("fresns_config_tag_*");
        Cache::forget('fresns_content_block_words');
        Cache::forget('fresns_user_block_words');
        Cache::forget('fresns_dialog_block_words');
        Cache::forget('fresns_content_ban_words');
        Cache::forget('fresns_content_review_words');
        Cache::forget('fresns_user_ban_words');
        Cache::forget('fresns_dialog_ban_words');
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
    public static function forgetFresnsModel(string $modelName, string $fsid)
    {
        $cacheKey = "fresns_model_{$modelName}_{$fsid}";

        Cache::forget($cacheKey);
    }

    /**
     * forget table column lang content.
     *
     * fresns_{$tableName}_{$tableColumn}_{$tableId}_{$langTag}
     */
    public static function forgetFresnsTableColumnLangContent(string $tableName, string $tableColumn, int $tableId, ?string $langTag = null)
    {
        $cacheKey = "fresns_{$tableName}_{$tableColumn}_{$tableId}_{$langTag}";

        Cache::forget($cacheKey);
    }

    /**
     * forget fresns api multilingual info.
     *
     * fresns_api_auth_account_{$aid}_{$langTag}
     * fresns_api_auth_user_{$uid}_{$langTag}
     * fresns_api_archives_{$type}_{$unikey}_{$langTag}
     * fresns_api_stickers_{$langTag}
     */
    public static function forgetFresnsApiMultilingualInfo(string $cacheName)
    {
        $langTagArr = ConfigHelper::fresnsConfigLangTags();

        foreach ($langTagArr as $langTag) {
            $cacheKey = "{$cacheName}_{$langTag}";

            Cache::forget($cacheKey);
        }
    }

    /**
     * forget fresns api multilingual and timezone info.
     *
     * fresns_api_publish_{$authUserId}_{$langTag}_{$timezone}
     */
    public static function forgetFresnsApiLangAndTimezoneInfo(string $cacheName)
    {
        $langTagArr = ConfigHelper::fresnsConfigLangTags();

        $langCacheKeyArr = null;
        foreach ($langTagArr as $langTag) {
            $cacheKey = "{$cacheName}_{$langTag}";

            $langCacheKeyArr[] = $cacheKey;
        }

        $utcArr = ConfigHelper::fresnsConfigByItemKey('utc');

        foreach ($langCacheKeyArr as $langCacheKey) {
            foreach ($utcArr as $utc) {
                $cacheKey = "{$langCacheKey}_{$utc}";

                Cache::forget($cacheKey);
            }
        }
    }

    /**
     * forget fresns api info.
     *
     * fresns_api_guest_expire_info
     * fresns_api_guest_groups
     * fresns_api_user_{$uid}_expire_info
     * fresns_api_user_{$uid}_content_view_perm
     * fresns_api_user_{$authUserId}_groups
     */
    public static function forgetFresnsApiInfo(string $cacheKey)
    {
        Cache::forget($cacheKey);
    }

    // forget fresns api account
    public static function forgetApiAccount(?string $aid)
    {
        return CacheHelper::forgetFresnsApiMultilingualInfo("fresns_api_auth_account_{$aid}");
    }

    // forget fresns api user
    public static function forgetApiUser(?int $uid)
    {
        return CacheHelper::forgetFresnsApiMultilingualInfo("fresns_api_auth_user_{$uid}");
    }
}
