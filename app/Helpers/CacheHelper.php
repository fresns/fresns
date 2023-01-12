<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Config;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Group;
use App\Models\Plugin;
use App\Utilities\InteractionUtility;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    const NULL_CACHE_KEY_PREFIX = 'null_key_';
    const NULL_CACHE_COUNT = 2;

    // cache time
    public static function fresnsCacheTimeByFileType(?int $fileType = null, ?int $minutes = null)
    {
        if (empty($fileType)) {
            $digital = rand(12, 72);

            return now()->addHours($digital);
        }

        if ($fileType != File::TYPE_ALL) {
            $fileConfig = FileHelper::fresnsFileStorageConfigByType($fileType);

            if (! $fileConfig['antiLinkStatus']) {
                if ($minutes) {
                    return now()->addMinutes($minutes);
                }

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
            if ($minutes) {
                return now()->addMinutes($minutes);
            }

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

    // put null cache count
    public static function putNullCacheCount(string $cacheKey, ?int $cacheMinutes = null)
    {
        CacheHelper::forgetFresnsKey($cacheKey);

        $nullCacheKey = CacheHelper::getNullCacheKey($cacheKey);
        $cacheTag = 'fresnsNullCount';

        $currentCacheKeyNullNum = (int) CacheHelper::get($nullCacheKey, $cacheTag) ?? 0;

        $now = $cacheMinutes ? now()->addMinutes($cacheMinutes) : CacheHelper::fresnsCacheTimeByFileType();

        if (CacheHelper::isSupportTags()) {
            $cacheTags = (array) $cacheTag;

            Cache::tags($cacheTags)->put($nullCacheKey, ++$currentCacheKeyNullNum, $now);
        } else {
            Cache::put($nullCacheKey, ++$currentCacheKeyNullNum, $now);
        }

        CacheHelper::addCacheItems($cacheKey, $cacheTag);
    }

    // is known to be empty
    public static function isKnownEmpty(string $cacheKey): bool
    {
        $nullCacheKey = CacheHelper::getNullCacheKey($cacheKey);

        $nullCacheCount = CacheHelper::get($nullCacheKey, 'fresnsNullCount');

        // null cache count
        if ($nullCacheCount > CacheHelper::NULL_CACHE_COUNT) {
            return true;
        }

        return false;
    }

    // does the cache support tags
    public static function isSupportTags(): bool
    {
        $isSupportTags = Cache::rememberForever('fresns_cache_is_support_tags', function () {
            // $cacheDriver = env('CACHE_DRIVER', 'file');
            $cacheDriver = config('cache.default', 'file');

            if ($cacheDriver == 'file' || $cacheDriver == 'dynamodb' || $cacheDriver == 'database') {
                return false;
            }

            return true;
        });

        return $isSupportTags;
    }

    // cache get
    public static function get(string $cacheKey, mixed $cacheTags = null)
    {
        $addTag = (array) $cacheKey;
        $cacheTags = (array) $cacheTags;
        $allTags = Arr::collapse([$cacheTags, $addTag]);

        if (CacheHelper::isSupportTags() && $cacheTags) {
            return Cache::tags($allTags)->get($cacheKey);
        }

        return Cache::get($cacheKey);
    }

    // cache put
    public static function put(mixed $cacheData, string $cacheKey, mixed $cacheTags = null, ?int $nullCacheMinutes = null, ?Carbon $cacheTime = null)
    {
        $addTag = (array) $cacheKey;
        $cacheTags = (array) $cacheTags;
        $allTags = Arr::collapse([$cacheTags, $addTag]);

        // null cache count
        if (empty($cacheData)) {
            CacheHelper::putNullCacheCount($cacheKey, $nullCacheMinutes);

            return $cacheData;
        }

        $cacheTime = $cacheTime ?: CacheHelper::fresnsCacheTimeByFileType();

        if (CacheHelper::isSupportTags() && $cacheTags) {
            Cache::tags($allTags)->put($cacheKey, $cacheData, $cacheTime);
        } else {
            Cache::put($cacheKey, $cacheData, $cacheTime);
        }

        CacheHelper::addCacheItems($cacheKey, $cacheTags);

        $cacheTagList = Cache::get('fresns_cache_tags') ?? [];
        foreach ($cacheTags as $tag) {
            $datetime = date('Y-m-d H:i:s');

            $newTagList = Arr::add($cacheTagList, $tag, $datetime);

            Cache::forever('fresns_cache_tags', $newTagList);
        }
    }

    // add cache items
    public static function addCacheItems(string $cacheKey, mixed $cacheTags = null)
    {
        if (empty($cacheTags)) {
            return;
        }

        $cacheTags = (array) $cacheTags;
        $tags = [
            'fresnsNullCount',
            'fresnsSystems',
            'fresnsConfigs',
            'fresnsCodeMessages',
            'fresnsArchives',
            'fresnsExtensions',
            'fresnsConfigLanguages',
            'fresnsPluginUsageLanguages',
            'fresnsRoleLanguages',
            'fresnsStickerLanguages',
            'fresnsWebConfigs',
        ];

        foreach ($cacheTags as $tag) {
            if (in_array($tag, $tags)) {
                $cacheItems = Cache::get($tag) ?? [];

                $datetime = date('Y-m-d H:i:s');

                $newCacheItems = Arr::add($cacheItems, $cacheKey, $datetime);

                Cache::forever($tag, $newCacheItems);
            }
        }
    }

    /**
     * clear all cache.
     */
    public static function clearAllCache()
    {
        Cache::flush();
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('view:clear');
        \Artisan::call('route:clear');
        \Artisan::call('event:clear');
        \Artisan::call('schedule:clear-cache');

        // time of the latest cache
        Config::updateOrCreate([
            'item_key' => 'cache_datetime',
        ], [
            'item_value' => now(),
            'item_type' => 'string',
            'item_tag' => 'systems',
            'is_multilingual' => 0,
            'is_custom' => 0,
            'is_api' => 1,
        ]);

        \Artisan::call('config:cache');
        \Artisan::call('view:cache');
        \Artisan::call('event:cache');
    }

    /**
     * clear config cache.
     */
    public static function clearConfigCache(string $cacheType)
    {
        // system
        if ($cacheType == 'fresnsSystem') {
            $keyArr = Cache::get('fresnsSystems') ?? [];
            foreach ($keyArr as $key => $datetime) {
                CacheHelper::forgetFresnsKey($key);
            }

            CacheHelper::forgetFresnsKey('fresnsSystems');
            CacheHelper::forgetFresnsKey('fresns_cache_is_support_tags');
        }

        // config
        if ($cacheType == 'fresnsConfig') {
            CacheHelper::forgetFresnsTag('fresnsConfigs');
            CacheHelper::forgetFresnsTag('fresnsCodeMessages');
            CacheHelper::forgetFresnsTag('fresnsArchives');
            CacheHelper::forgetFresnsTag('fresnsConfigLanguages');
            CacheHelper::forgetFresnsTag('fresnsRoleLanguages');
            CacheHelper::forgetFresnsTag('fresnsStickerLanguages');
            CacheHelper::forgetFresnsTag('fresnsWebConfigs');

            // time of the latest cache
            Config::updateOrCreate([
                'item_key' => 'cache_datetime',
            ], [
                'item_value' => now(),
                'item_type' => 'string',
                'item_tag' => 'systems',
                'is_multilingual' => 0,
                'is_custom' => 0,
                'is_api' => 1,
            ]);
        }

        // extend
        if ($cacheType == 'fresnsExtend') {
            CacheHelper::forgetFresnsTag('fresnsExtensions');
        }

        // view
        if ($cacheType == 'fresnsView') {
            \Artisan::call('view:clear');
            \Artisan::call('view:cache');
        }

        // route
        if ($cacheType == 'fresnsRoute') {
            \Artisan::call('route:clear');
        }

        // event
        if ($cacheType == 'fresnsEvent') {
            \Artisan::call('event:clear');
            \Artisan::call('event:cache');
        }

        // schedule
        if ($cacheType == 'fresnsSchedule') {
            \Artisan::call('schedule:clear-cache');
        }

        // framework
        if ($cacheType == 'frameworkConfig') {
            \Artisan::call('config:clear');
            \Artisan::call('config:cache');
        }
    }

    /**
     * clear data cache.
     */
    public static function clearDataCache(string $cacheType, int|string $fsid, string $dataType)
    {
        $model = PrimaryHelper::fresnsModelByFsid($cacheType, $fsid);
        if (empty($model)) {
            return;
        }
        $id = $model->id;

        switch ($cacheType) {
            // user
            case 'user':
                if ($dataType == 'fresnsModel') {
                    CacheHelper::forgetFresnsMultilingual("fresns_user_{$id}_main_role");
                    CacheHelper::forgetFresnsMultilingual("fresns_user_{$id}_roles");
                    CacheHelper::forgetFresnsMultilingual("fresns_publish_post_config_{$id}");
                    CacheHelper::forgetFresnsMultilingual("fresns_publish_comment_config_{$id}");
                }

                if ($dataType == 'fresnsInteraction') {
                    CacheHelper::forgetFresnsKeys([
                        "fresns_follow_1_array_by_{$id}",
                        "fresns_follow_2_array_by_{$id}",
                        "fresns_follow_3_array_by_{$id}",
                        "fresns_follow_4_array_by_{$id}",
                        "fresns_follow_5_array_by_{$id}",
                        "fresns_block_1_array_by_{$id}",
                        "fresns_block_2_array_by_{$id}",
                        "fresns_block_3_array_by_{$id}",
                        "fresns_block_4_array_by_{$id}",
                        "fresns_block_5_array_by_{$id}",
                        "fresns_filter_groups_by_user_{$id}",
                        "fresns_user_{$id}_all_groups",
                    ]);

                    $plugins = Plugin::get();
                    foreach ($plugins as $plugin) {
                        CacheHelper::forgetFresnsKey("fresns_plugin_{$plugin->unikey}_badge_{$id}");
                    }

                    $groups = Group::get();
                    foreach ($groups as $group) {
                        CacheHelper::forgetFresnsKey("fresns_follow_group_model_{$group->id}_by_{$id}");
                    }
                }

                if ($dataType == 'fresnsApiData') {
                    CacheHelper::forgetFresnsMultilingual("fresns_api_user_{$model->uid}");
                    CacheHelper::forgetFresnsMultilingual("fresns_api_user_panel_conversations_{$model->uid}");
                    CacheHelper::forgetFresnsMultilingual("fresns_api_user_panel_notifications_{$model->uid}");
                    CacheHelper::forgetFresnsMultilingual("fresns_api_user_panel_drafts_{$model->uid}");
                }

                if ($dataType == 'fresnsExtension') {
                }
            break;

            // group
            case 'group':
                if ($dataType == 'fresnsModel') {
                    CacheHelper::forgetFresnsKeys([
                        'fresns_group_count',
                        'fresns_private_groups',
                        'fresns_guest_all_groups',
                        'fresns_filter_groups_by_guest',
                    ]);
                }

                if ($dataType == 'fresnsApiData') {
                    CacheHelper::forgetFresnsMultilingual("fresns_api_group_{$model->gid}");
                }

                if ($dataType == 'fresnsExtension') {
                    CacheHelper::forgetFresnsMultilingual("fresns_group_{$id}_extends_by_everyone");
                    CacheHelper::forgetFresnsMultilingual("fresns_group_{$id}_extends_by_role");
                    CacheHelper::forgetFresnsMultilingual("fresns_group_{$id}_extends_by_group_admin");
                }
            break;

            // hashtag
            case 'hashtag':
                if ($dataType == 'fresnsApiData') {
                    CacheHelper::forgetFresnsMultilingual("fresns_api_hashtag_{$model->slug}");
                }
            break;

            // post
            case 'post':
                if ($dataType == 'fresnsApiData') {
                    CacheHelper::forgetFresnsMultilingual("fresns_api_post_{$model->pid}");
                    CacheHelper::forgetFresnsKeys([
                        "fresns_api_post_{$model->pid}_list_content",
                        "fresns_api_post_{$model->pid}_detail_content",
                    ]);
                    CacheHelper::forgetFresnsMultilingual("fresns_api_post_{$id}_preview_comments");
                    CacheHelper::forgetFresnsMultilingual("fresns_api_post_{$id}_preview_like_users");
                }
            break;

            // comment
            case 'comment':
                if ($dataType == 'fresnsApiData') {
                    CacheHelper::forgetFresnsMultilingual("fresns_api_comment_{$model->cid}");
                    CacheHelper::forgetFresnsKeys([
                        "fresns_api_comment_{$model->cid}_list_content",
                        "fresns_api_comment_{$model->cid}_detail_content",
                    ]);
                    CacheHelper::forgetFresnsMultilingual("fresns_api_comment_{$id}_sub_comments");
                }
            break;
        }

        if ($dataType == 'fresnsSeo') {
            CacheHelper::forgetFresnsKey("fresns_seo_{$cacheType}_{$id}");
        }

        if ($dataType == 'fresnsModel') {
            CacheHelper::forgetFresnsModel($cacheType, $fsid);
        }
    }

    /**
     * forget fresns key.
     */
    public static function forgetFresnsKey(string $key)
    {
        if (CacheHelper::isSupportTags()) {
            Cache::tags($key)->flush();
            Cache::forget($key);
        } else {
            Cache::forget($key);
        }
    }

    /**
     * forget fresns keys.
     */
    public static function forgetFresnsKeys(array $keys)
    {
        if (CacheHelper::isSupportTags()) {
            foreach ($keys as $key) {
                Cache::tags($key)->flush();
                Cache::forget($key);
            }
        } else {
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        }
    }

    /**
     * forget fresns tag.
     */
    public static function forgetFresnsTag(string $tag)
    {
        if (CacheHelper::isSupportTags()) {
            Cache::tags($tag)->flush();
            Cache::forget($tag);

            return;
        }

        $tags = [
            'fresnsNullCount',
            'fresnsSystems',
            'fresnsConfigs',
            'fresnsCodeMessages',
            'fresnsArchives',
            'fresnsExtensions',
            'fresnsConfigLanguages',
            'fresnsPluginUsageLanguages',
            'fresnsRoleLanguages',
            'fresnsStickerLanguages',
            'fresnsWebConfigs',
        ];

        if (in_array($tag, $tags)) {
            $keyArr = Cache::get($tag) ?? [];

            foreach ($keyArr as $key => $datetime) {
                Cache::forget($key);
            }

            Cache::forget($tag);
        }

        if ($tag == 'fresnsSystems') {
            CacheHelper::forgetFresnsKey('fresns_cache_is_support_tags');
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

            CacheHelper::forgetFresnsKey($cacheKey);
        }
    }

    /**
     * forget fresns model.
     *
     * fresns_model_key_{$appId}
     * fresns_model_account_{$aid}
     * fresns_model_user_{$uidOrUsername}
     * fresns_model_group_{$gid}
     * fresns_model_hashtag_{$hid}
     * fresns_model_post_{$pid}
     * fresns_model_comment_{$cid}
     * fresns_model_file_{$fid}
     * fresns_model_extend_{$eid}
     */
    public static function forgetFresnsModel(string $modelName, int|string $fsid)
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
            CacheHelper::forgetFresnsKey("fresns_model_user_{$model?->uid}");
            CacheHelper::forgetFresnsKey("fresns_model_user_{$model?->username}");
        }

        CacheHelper::forgetFresnsKey($fsidCacheKey);
        CacheHelper::forgetFresnsKey($idCacheKey);
    }

    /**
     * forget fresns file usage.
     */
    public static function forgetFresnsFileUsage(int|string $fileIdOrFid)
    {
        if (StrHelper::isPureInt($fileIdOrFid)) {
            $fileId = (int) $fileIdOrFid;
        } else {
            $fileId = PrimaryHelper::fresnsFileIdByFid($fileIdOrFid);
        }

        if (empty($fileId)) {
            return;
        }

        CacheHelper::forgetFresnsModel('file', $fileId);

        $fileUsages = FileUsage::where('file_id', $fileId)->get();

        foreach ($fileUsages as $usage) {
            switch ($usage->usage_type) {
                case FileUsage::TYPE_POST:
                    $post = PrimaryHelper::fresnsModelById('post', $usage->table_id);
                    $pid = $post?->pid;
                    $keys = [
                        "fresns_api_post_{$pid}_list_content",
                        "fresns_api_post_{$pid}_detail_content",
                    ];
                break;

                case FileUsage::TYPE_COMMENT:
                    $comment = PrimaryHelper::fresnsModelById('comment', $usage->table_id);
                    $cid = $comment?->cid;
                    $keys = [
                        "fresns_api_comment_{$cid}_list_content",
                        "fresns_api_comment_{$cid}_detail_content",
                    ];
                break;
            }

            CacheHelper::forgetFresnsKeys($keys);
        }
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
        CacheHelper::forgetFresnsModel('account', $aid);
    }

    // forget fresns user
    public static function forgetFresnsUser(?int $userId = null, ?int $uid = null)
    {
        if (empty($userId) && empty($uid)) {
            return;
        }

        CacheHelper::forgetFresnsMultilingual("fresns_user_{$userId}_main_role");
        CacheHelper::forgetFresnsMultilingual("fresns_user_{$userId}_roles");
        CacheHelper::forgetFresnsMultilingual("fresns_publish_post_config_{$userId}");
        CacheHelper::forgetFresnsMultilingual("fresns_publish_comment_config_{$userId}");
        CacheHelper::forgetFresnsMultilingual("fresns_api_user_{$uid}");
        CacheHelper::forgetFresnsModel('user', $uid);
    }

    /**
     * forget fresns interaction.
     */
    public static function forgetFresnsInteraction(int $type, int $id, int $userId)
    {
        CacheHelper::forgetFresnsKeys([
            "fresns_interaction_status_{$type}_{$id}_{$userId}",
            "fresns_follow_{$type}_model_{$id}_by_{$userId}",
            "fresns_follow_{$type}_array_by_{$userId}",
            "fresns_block_{$type}_array_by_{$userId}",
        ]);

        if ($type == InteractionUtility::TYPE_USER) {
            CacheHelper::forgetFresnsKey("fresns_interaction_status_{$type}_{$userId}_{$id}");
        }

        if ($type == InteractionUtility::TYPE_GROUP) {
            CacheHelper::forgetFresnsKeys([
                "fresns_filter_groups_by_user_{$userId}",
                "fresns_user_{$userId}_all_groups",
            ]);
        }
    }

    /**
     * no tag.
     */
    // fresns_cache_is_support_tags
    // fresns_cache_tags
    // install_{$step}
    // autoUpgradeStep
    // autoUpgradeTip
    // physicalUpgradeStep
    // physicalUpgradeTip

    /**
     * tag: fresnsSystems.
     */
    // fresns_current_version
    // fresns_new_version
    // fresns_news
    // fresns_database_timezone
    // fresns_database_datetime
    // fresns_panel_login_path
    // fresns_panel_translation_{$locale}
    // fresns_model_key_{$appId}
    // fresns_crontab_items

    /**
     * tag: fresnsConfigs.
     */
    // fresns_default_langTag
    // fresns_default_timezone
    // fresns_lang_tags
    // fresns_config_{$itemKey}_{$langTag}
    // fresns_config_keys_{$key}_{$langTag}
    // fresns_config_tag_{$itemTag}_{$langTag}
    // fresns_config_file_accept
    // fresns_config_file_url_expire
    // fresns_plugin_url_{$unikey}
    // fresns_content_block_words       // fresns_{$type}_block_words
    // fresns_user_block_words          // fresns_{$type}_block_words
    // fresns_conversation_block_words  // fresns_{$type}_block_words
    // fresns_content_ban_words
    // fresns_content_review_words
    // fresns_user_ban_words
    // fresns_conversation_ban_words

    /**
     * tag: fresnsLanguages
     * tag: fresnsUnknownLanguages
     * tag: fresnsConfigLanguages
     * tag: fresnsPluginUsageLanguages
     * tag: fresnsRoleLanguages
     * tag: fresnsStickerLanguages
     * tag: fresnsNotificationLanguages.
     */
    // fresns_{$tableName}_{$tableColumn}_{$tableId}_{$langTag}

    /**
     * tag: fresnsCodeMessages.
     */
    // fresns_code_messages_{$unikey}_{$langTag}

    /**
     * tag: fresnsAccounts.
     */
    // fresns_token_account_{$accountId}_{$token}   // +tag: fresnsAccountTokens
    // fresns_model_account_{$aid}                  // +tag: fresnsAccountModels
    // fresns_api_account_{$aid}_{$langTag}         // +tag: fresnsAccountData

    /**
     * tag: fresnsUsers.
     */
    // fresns_token_user_{$userId}_{$token}                         // +tag: fresnsUserTokens
    // fresns_model_user_{$uidOrUsername}                           // +tag: fresnsUserModels
    // fresns_model_user_{$userId}                                  // +tag: fresnsUserModels
    // fresns_model_conversation_{$userId}_{$conversationUserId}    // +tag: fresnsUserConversations
    // fresns_user_{$userId}_main_role_{$langTag}                   // +tag: fresnsUserRoles
    // fresns_user_{$userId}_roles_{$langTag}                       // +tag: fresnsUserRoles
    // fresns_publish_{$type}_config_{$userId}_{$langTag}           // +tag: fresnsUserConfigs
    // fresns_plugin_{$unikey}_badge_{$userId}                      // +tag: fresnsUserConfigs
    // fresns_interaction_status_{$markType}_{$markId}_{$userId}    // +tag: fresnsUserInteractions
    // fresns_follow_{$type}_array_by_{$userId}                     // +tag: fresnsUserInteractions, fresnsFollowData
    // fresns_block_{$type}_array_by_{$userId}                      // +tag: fresnsUserInteractions, fresnsBlockData
    // fresns_seo_user_{$userId}                                    // +tag: fresnsUserData
    // fresns_api_user_{$uid}_{$langTag}                            // +tag: fresnsUserData
    // fresns_api_user_panel_conversations_{$uid}                   // +tag: fresnsUserData, fresnsUserConversations
    // fresns_api_user_panel_notifications_{$uid}                   // +tag: fresnsUserData, fresnsUserNotifications
    // fresns_api_user_panel_drafts_{$uid}                          // +tag: fresnsUserData, fresnsUserDrafts

    /**
     * tag: fresnsGroups.
     */
    // fresns_group_count                                           // +tag: fresnsGroupConfigs
    // fresns_private_groups                                        // +tag: fresnsGroupConfigs
    // fresns_filter_groups_by_guest                                // +tag: fresnsGroupConfigs, fresnsUsers, fresnsUserInteractions
    // fresns_filter_groups_by_user_{$userId}                       // +tag: fresnsGroupConfigs, fresnsUsers, fresnsUserInteractions
    // fresns_model_group_{$gid}                                    // +tag: fresnsGroupModels
    // fresns_model_group_{$groupId}                                // +tag: fresnsGroupModels
    // fresns_seo_group_{$groupId}                                  // +tag: fresnsGroupData
    // fresns_guest_all_groups                                      // +tag: fresnsGroupData
    // fresns_user_{$userId}_all_groups                             // +tag: fresnsGroupData, fresnsUsers, fresnsUserData
    // fresns_follow_group_model_{$id}_by_{$userId}                 // +tag: fresnsGroupData, fresnsUsers, fresnsUserInteractions, fresnsFollowData
    // fresns_api_group_{$gid}_{$langTag}                           // +tag: fresnsGroupData

    /**
     * tag: fresnsHashtags.
     */
    // fresns_model_hashtag_{$hid}              // +tag: fresnsHashtagModels
    // fresns_model_hashtag_{$hashtagId}        // +tag: fresnsHashtagModels
    // fresns_seo_hashtag_{$id}                 // +tag: fresnsHashtagData
    // fresns_api_hashtag_{$hid}_{$langTag}     // +tag: fresnsHashtagData

    /**
     * tag: fresnsPosts.
     */
    // fresns_model_post_{$pid}                                 // +tag: fresnsPostModels
    // fresns_model_post_{$postId}                              // +tag: fresnsPostModels
    // fresns_seo_post_{$id}                                    // +tag: fresnsPostData
    // fresns_api_post_{$pid}_{$langTag}                        // +tag: fresnsPostData
    // fresns_api_post_{$pid}_list_content                      // +tag: fresnsPostData
    // fresns_api_post_{$pid}_detail_content                    // +tag: fresnsPostData
    // fresns_api_post_{$pid}_allow_{$uid}                      // +tag: fresnsPostData, fresnsUsers, fresnsUserData
    // fresns_api_post_{$postId}_preview_comments_{$langTag}    // +tag: fresnsPostData, fresnsComments, fresnsCommentData
    // fresns_api_post_{$postId}_preview_like_users_{$langTag}  // +tag: fresnsPostData, fresnsUsers, fresnsUserData

    /**
     * tag: fresnsComments.
     */
    // fresns_model_comment_{$cid}                              // +tag: fresnsCommentModels
    // fresns_model_comment_{$commentId}                        // +tag: fresnsCommentModels
    // fresns_seo_comment_{$id}                                 // +tag: fresnsCommentData
    // fresns_api_comment_{$cid}_{$langTag}                     // +tag: fresnsCommentData
    // fresns_api_comment_{$cid}_list_content                   // +tag: fresnsCommentData
    // fresns_api_comment_{$cid}_detail_content                 // +tag: fresnsCommentData
    // fresns_api_comment_{$commentId}_sub_comments_{$langTag}  // +tag: fresnsCommentData

    /**
     * tag: fresnsModels.
     */
    // fresns_model_file_{$fid}                         // +tag: fresnsFiles
    // fresns_model_file_{$fileId}                      // +tag: fresnsFiles
    // fresns_model_extend_{$eid}                       // +tag: fresnsExtends
    // fresns_model_extend_{$extendId}                  // +tag: fresnsExtends
    // fresns_model_archive_{$code}                     // +tag: fresnsArchives
    // fresns_model_archive_{$archiveId}                // +tag: fresnsArchives
    // fresns_model_operation_{$operationId}            // +tag: fresnsOperations
    // fresns_model_conversation_{$conversationId}      // +tag: fresnsConversations

    /**
     * tag: fresnsExtensions.
     */
    // fresns_wallet_recharge_extends_by_everyone_{$langTag}            // +tag: fresnsWallets
    // fresns_wallet_withdraw_extends_by_everyone_{$langTag}            // +tag: fresnsWallets
    // fresns_post_content_types_by_{$typeName}_{$langTag}              // +tag: fresnsContentTypes
    // fresns_comment_content_types_by_{$typeName}_{$langTag}           // +tag: fresnsContentTypes
    // fresns_map_extends_by_everyone_{$langTag}                        // +tag: fresnsMaps

    // fresns_editor_post_extends_by_everyone_{$langTag}                // +tag: fresnsEditor
    // fresns_editor_comment_extends_by_everyone_{$langTag}             // +tag: fresnsEditor
    // fresns_manage_post_extends_by_everyone_{$langTag}                // +tag: fresnsManages
    // fresns_manage_comment_extends_by_everyone_{$langTag}             // +tag: fresnsManages
    // fresns_manage_user_extends_by_everyone_{$langTag}                // +tag: fresnsManages
    // fresns_group_{$groupId}_extends_by_everyone_{$langTag}           // +tag: fresnsGroupConfigs, fresnsGroupExtensions
    // fresns_feature_extends_by_everyone_{$langTag}                    // +tag: fresnsFeatures
    // fresns_profile_extends_by_everyone_{$langTag}                    // +tag: fresnsProfiles

    // fresns_editor_post_extends_by_role_{$roleId}_{$langTag}          // +tag: fresnsEditor
    // fresns_editor_comment_extends_by_role_{$roleId}_{$langTag}       // +tag: fresnsEditor
    // fresns_manage_post_extends_by_role_{$roleId}_{$langTag}          // +tag: fresnsManages
    // fresns_manage_comment_extends_by_role_{$roleId}_{$langTag}       // +tag: fresnsManages
    // fresns_manage_user_extends_by_role_{$roleId}_{$langTag}          // +tag: fresnsManages
    // fresns_group_{$groupId}_extends_by_role_{$roleId}_{$langTag}     // +tag: fresnsGroupConfigs, fresnsGroupExtensions
    // fresns_feature_extends_by_role_{$roleId}_{$langTag}              // +tag: fresnsFeatures
    // fresns_profile_extends_by_role_{$roleId}_{$langTag}              // +tag: fresnsProfiles

    // fresns_manage_post_extends_by_group_admin_{$langTag}             // +tag: fresnsManages
    // fresns_manage_comment_extends_by_group_admin_{$langTag}          // +tag: fresnsManages
    // fresns_group_{$groupId}_extends_by_group_admin_{$langTag}        // +tag: fresnsGroupConfigs, fresnsGroupExtensions

    /**
     * tag: fresnsApiData.
     */
    // fresns_api_archives_{$type}_{$unikey}_{$langTag}     // +tag: fresnsArchives
    // fresns_api_sticker_tree_{$langTag}                   // +tag: fresnsConfigs
}
