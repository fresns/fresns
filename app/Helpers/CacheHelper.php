<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\App;
use App\Models\Config;
use App\Models\File;
use App\Models\FileUsage;
use App\Utilities\InteractionUtility;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CacheHelper
{
    const NULL_CACHE_KEY_PREFIX = 'null_key_';
    const NULL_CACHE_COUNT = 2;

    // cache time
    public static function fresnsCacheTimeByFileType(?int $fileType = null, ?int $specificMinutes = null, ?int $offsetMinutes = 1): Carbon
    {
        if (empty($fileType)) {
            $digital = rand(12, 72);

            return now()->addHours($digital);
        }

        if ($fileType != File::TYPE_ALL) {
            $fileConfig = FileHelper::fresnsFileStorageConfigByType($fileType);

            if (! $fileConfig['antiLinkStatus']) {
                if ($specificMinutes) {
                    return now()->addMinutes($specificMinutes);
                }

                $digital = rand(72, 168);

                return now()->addHours($digital);
            }

            $cacheTime = now()->addMinutes($fileConfig['antiLinkExpire'] - $offsetMinutes);

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
            if ($specificMinutes) {
                return now()->addMinutes($specificMinutes);
            }

            $digital = rand(6, 72);

            return now()->addHours($digital);
        }

        $minAntiLinkExpire = min($newAntiLinkExpire);

        $cacheTime = now()->addMinutes($minAntiLinkExpire - $offsetMinutes);

        return $cacheTime;
    }

    // get null cache key
    public static function getNullCacheKey(string $cacheKey): string
    {
        return CacheHelper::NULL_CACHE_KEY_PREFIX.$cacheKey;
    }

    // put null cache count
    public static function putNullCacheCount(string $cacheKey, ?int $cacheMinutes = null): void
    {
        CacheHelper::forgetFresnsKey($cacheKey);

        $nullCacheKey = CacheHelper::getNullCacheKey($cacheKey);
        $cacheTag = 'fresnsNullCount';

        $currentCacheKeyNullNum = (int) CacheHelper::get($nullCacheKey, $cacheTag) ?? 0;

        $now = $cacheMinutes ? now()->addMinutes($cacheMinutes) : CacheHelper::fresnsCacheTimeByFileType();

        if (Cache::supportsTags()) {
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
        $whetherToCache = ConfigHelper::fresnsConfigDeveloperMode()['cache'];
        $isWebCache = Str::startsWith($cacheKey, 'fresns_web');
        if (! $whetherToCache && ! $isWebCache) {
            return false;
        }

        $nullCacheKey = CacheHelper::getNullCacheKey($cacheKey);

        $nullCacheCount = CacheHelper::get($nullCacheKey, 'fresnsNullCount');

        // null cache count
        if ($nullCacheCount > CacheHelper::NULL_CACHE_COUNT) {
            return true;
        }

        return false;
    }

    // cache get
    public static function get(string $cacheKey, mixed $cacheTags = null): mixed
    {
        $whetherToCache = ConfigHelper::fresnsConfigDeveloperMode()['cache'];
        $isWebCache = Str::startsWith($cacheKey, 'fresns_web');
        if (! $whetherToCache && ! $isWebCache) {
            return null;
        }

        $cacheTags = (array) $cacheTags;

        if (Cache::supportsTags() && $cacheTags) {
            return Cache::tags($cacheTags)->get($cacheKey);
        }

        return Cache::get($cacheKey);
    }

    // cache put
    public static function put(mixed $cacheData, string $cacheKey, mixed $cacheTags = null, ?int $nullCacheMinutes = null, ?Carbon $cacheTime = null): void
    {
        $whetherToCache = ConfigHelper::fresnsConfigDeveloperMode()['cache'];
        $isWebCache = Str::startsWith($cacheKey, 'fresns_web');
        if (! $whetherToCache && ! $isWebCache) {
            return;
        }

        $cacheTags = (array) $cacheTags;

        // null cache count
        if (empty($cacheData)) {
            CacheHelper::putNullCacheCount($cacheKey, $nullCacheMinutes);

            return;
        }

        $cacheTime = $cacheTime ?: CacheHelper::fresnsCacheTimeByFileType();

        if (Cache::supportsTags() && $cacheTags) {
            Cache::tags($cacheTags)->put($cacheKey, $cacheData, $cacheTime);
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
    public static function addCacheItems(string $cacheKey, mixed $cacheTags = null): void
    {
        if (empty($cacheTags)) {
            return;
        }

        $cacheTags = (array) $cacheTags;
        $tags = [
            'fresnsNullCount',
            'fresnsSystems',
            'fresnsConfigs',
            'fresnsExtensions',
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
    public static function clearAllCache(): void
    {
        Cache::flush();
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('event:clear');
        Artisan::call('schedule:clear-cache');

        // time of the latest cache
        Config::updateOrCreate([
            'item_key' => 'cache_datetime',
        ], [
            'item_value' => now(),
            'item_type' => 'string',
            'is_multilingual' => 0,
            'is_custom' => 0,
            'is_api' => 1,
        ]);
    }

    /**
     * clear config cache.
     */
    public static function clearConfigCache(string $cacheType): void
    {
        // system
        if ($cacheType == 'fresnsSystem') {
            CacheHelper::forgetFresnsTag('fresnsSystems');
            Artisan::call('config:clear');
            Artisan::call('config:cache');
        }

        // config
        if ($cacheType == 'fresnsConfig') {
            CacheHelper::forgetFresnsTag('fresnsConfigs');
            CacheHelper::forgetFresnsTag('fresnsWebConfigs');

            // time of the latest cache
            Config::updateOrCreate([
                'item_key' => 'cache_datetime',
            ], [
                'item_value' => now(),
                'item_type' => 'string',
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
            Artisan::call('view:clear');
            Artisan::call('view:cache');
        }

        // route
        if ($cacheType == 'fresnsRoute') {
            Artisan::call('route:clear');
        }

        // event
        if ($cacheType == 'fresnsEvent') {
            Artisan::call('event:clear');
            Artisan::call('event:cache');
        }

        // schedule
        if ($cacheType == 'fresnsSchedule') {
            Artisan::call('schedule:clear-cache');
        }
    }

    /**
     * clear data cache.
     */
    public static function clearDataCache(string $cacheType, int|string $fsid): void
    {
        $model = PrimaryHelper::fresnsModelByFsid($cacheType, $fsid);
        if (empty($model)) {
            return;
        }
        $id = $model->id;

        CacheHelper::forgetFresnsModel($cacheType, $fsid);

        switch ($cacheType) {
            case 'user':
                CacheHelper::forgetFresnsUser($id, $model->uid);

                $account = PrimaryHelper::fresnsModelById('account', $model->account_id);
                CacheHelper::forgetFresnsAccount($account->aid);

                // fresns_follow_{$type}_array_by_{$userId}
                $interactionKeys = [
                    "fresns_follow_1_array_by_{$id}",
                    "fresns_follow_2_array_by_{$id}",
                    "fresns_follow_3_array_by_{$id}",
                    "fresns_follow_4_array_by_{$id}",
                    "fresns_follow_5_array_by_{$id}",
                ];
                foreach ($interactionKeys as $key) {
                    CacheHelper::forgetFresnsKey($key, 'fresnsUsers');
                    CacheHelper::forgetFresnsKey($key, 'fresnsGroups');
                    CacheHelper::forgetFresnsKey($key, 'fresnsHashtags');
                    CacheHelper::forgetFresnsKey($key, 'fresnsPosts');
                    CacheHelper::forgetFresnsKey($key, 'fresnsComments');

                    $cacheKey = CacheHelper::getNullCacheKey($key);
                    CacheHelper::forgetFresnsKey($cacheKey, 'fresnsNullCount');
                }

                $groupKeys = [
                    "fresns_filter_groups_by_user_{$id}",
                    "fresns_user_all_groups_{$id}",
                ];
                CacheHelper::forgetFresnsKeys($groupKeys, ['fresnsGroups', 'fresnsUsers']);
                foreach ($groupKeys as $key) {
                    $cacheKey = CacheHelper::getNullCacheKey($key);

                    CacheHelper::forgetFresnsKey($cacheKey, 'fresnsNullCount');
                }

                CacheHelper::forgetFresnsKey("fresns_seo_user_{$id}", ['fresnsSeo', 'fresnsUsers']);

                $apps = App::all();
                foreach ($apps as $app) {
                    CacheHelper::forgetFresnsKey("fresns_plugin_{$app->fskey}_badge_{$id}", 'fresnsUsers');
                }
                break;

            case 'group':
                CacheHelper::forgetFresnsKeys([
                    'fresns_group_count',
                    'fresns_private_groups',
                    'fresns_filter_group_models',
                    'fresns_filter_groups_by_guest',
                    'fresns_guest_all_groups',
                ], 'fresnsGroups');

                CacheHelper::forgetFresnsKey("fresns_seo_group_{$id}", ['fresnsSeo', 'fresnsGroups']);
                CacheHelper::forgetFresnsMultilingual("fresns_api_group_{$model->gid}", 'fresnsGroups');
                CacheHelper::forgetFresnsMultilingual("fresns_group_{$id}_extends_by_everyone", ['fresnsExtensions', 'fresnsGroups']);
                CacheHelper::forgetFresnsMultilingual("fresns_group_{$id}_extends_by_role", ['fresnsExtensions', 'fresnsGroups']);
                CacheHelper::forgetFresnsMultilingual("fresns_group_{$id}_extends_by_group_admin", ['fresnsExtensions', 'fresnsGroups']);
                break;

            case 'hashtag':
                CacheHelper::forgetFresnsKey("fresns_seo_hashtag_{$id}", ['fresnsSeo', 'fresnsHashtags']);
                CacheHelper::forgetFresnsMultilingual("fresns_api_hashtag_{$model->slug}", 'fresnsHashtags');
                break;

            case 'post':
                // fresns_api_post_{$postId}_preview_comments_{$langTag}    // +tag: fresnsComments
                // fresns_api_post_{$postId}_preview_like_users_{$langTag}  // +tag: fresnsUsers
                CacheHelper::forgetFresnsKey("fresns_seo_post_{$id}", ['fresnsSeo', 'fresnsPosts']);
                CacheHelper::forgetFresnsKeys([
                    "fresns_api_post_{$model->pid}_list_content",
                    "fresns_api_post_{$model->pid}_detail_content",
                ], 'fresnsPosts');
                CacheHelper::forgetFresnsMultilingual("fresns_api_post_{$model->pid}", 'fresnsPosts');
                CacheHelper::forgetFresnsMultilingual("fresns_api_post_{$id}_preview_comments", ['fresnsPosts', 'fresnsComments']);
                CacheHelper::forgetFresnsMultilingual("fresns_api_post_{$id}_preview_like_users", ['fresnsPosts', 'fresnsUsers']);
                break;

            case 'comment':
                CacheHelper::forgetFresnsKey("fresns_seo_comment_{$id}", ['fresnsSeo', 'fresnsComments']);
                CacheHelper::forgetFresnsKeys([
                    "fresns_api_comment_{$model->cid}_list_content",
                    "fresns_api_comment_{$model->cid}_detail_content",
                ], 'fresnsComments');
                CacheHelper::forgetFresnsMultilingual("fresns_api_comment_{$model->cid}", 'fresnsComments');
                CacheHelper::forgetFresnsMultilingual("fresns_api_comment_{$id}_sub_comments", 'fresnsComments');
                break;

            case 'file':
                CacheHelper::forgetFresnsFileUsage($fsid);
                break;
        }
    }

    /**
     * forget fresns tag.
     */
    public static function forgetFresnsTag(string $tag): void
    {
        if ($tag == 'fresnsSystems') {
            CacheHelper::forgetFresnsKey('developer_mode');
        }

        if (Cache::supportsTags()) {
            Cache::tags($tag)->flush();

            return;
        }

        $tags = [
            'fresnsNullCount',
            'fresnsSystems',
            'fresnsConfigs',
            'fresnsExtensions',
            'fresnsWebConfigs',
        ];

        if (in_array($tag, $tags)) {
            $keyArr = Cache::get($tag) ?? [];

            foreach ($keyArr as $key => $datetime) {
                Cache::forget($key);
            }

            Cache::forget($tag);
        }
    }

    /**
     * forget fresns key.
     */
    public static function forgetFresnsKey(string $cacheKey, mixed $cacheTags = null): void
    {
        $nullCacheKey = CacheHelper::getNullCacheKey($cacheKey);
        $nullCacheTag = ['fresnsNullCount'];

        if (Cache::supportsTags() && $cacheTags) {
            $cacheTags = (array) $cacheTags;

            Cache::tags($cacheTags)->forget($cacheKey);
            Cache::tags($nullCacheTag)->forget($nullCacheKey);
        } else {
            Cache::forget($cacheKey);
            Cache::forget($nullCacheKey);
        }
    }

    /**
     * forget fresns keys.
     */
    public static function forgetFresnsKeys(array $cacheKeys, mixed $cacheTags = null): void
    {
        $nullCacheTag = ['fresnsNullCount'];

        if (Cache::supportsTags()) {
            $cacheTags = (array) $cacheTags;

            foreach ($cacheKeys as $key) {
                $nullCacheKey = CacheHelper::getNullCacheKey($key);

                Cache::tags($cacheTags)->forget($key);
                Cache::tags($nullCacheTag)->forget($nullCacheKey);
            }
        } else {
            foreach ($cacheKeys as $key) {
                $nullCacheKey = CacheHelper::getNullCacheKey($key);

                Cache::forget($key);
                Cache::forget($nullCacheKey);
            }
        }
    }

    /**
     * forget fresns multilingual info.
     */
    public static function forgetFresnsMultilingual(string $cacheName, mixed $cacheTags = null): void
    {
        $langTagArr = ConfigHelper::fresnsConfigLangTags();

        foreach ($langTagArr as $langTag) {
            $cacheKey = "{$cacheName}_{$langTag}";

            CacheHelper::forgetFresnsKey($cacheKey, $cacheTags);
        }
    }

    /**
     * forget fresns config keys.
     */
    public static function forgetFresnsConfigs(mixed $itemKeys): void
    {
        $itemKeys = (array) $itemKeys;

        foreach ($itemKeys as $key) {
            $cacheKey = "fresns_config_{$key}";

            CacheHelper::forgetFresnsMultilingual($cacheKey, 'fresnsConfigs');

            $configKeys = Cache::get('fresns_cache_config_keys') ?? [];
            $configKeysCacheKey = $configKeys[$key] ?? null;
            if ($configKeysCacheKey) {
                CacheHelper::forgetFresnsMultilingual($configKeysCacheKey, 'fresnsConfigs');
            }
        }
    }

    /**
     * forget fresns model.
     */
    public static function forgetFresnsModel(string $modelName, int|string $fsid): void
    {
        $cacheTag = match ($modelName) {
            'account' => 'fresnsAccounts',
            'user' => 'fresnsUsers',
            'group' => 'fresnsGroups',
            'hashtag' => 'fresnsHashtags',
            'post' => 'fresnsPosts',
            'comment' => 'fresnsComments',
            'file' => 'fresnsFiles',
            'extend' => 'fresnsExtends',
            'archive' => 'fresnsArchives',
            'operation' => 'fresnsOperations',
            'conversation' => 'fresnsConversations',
            default => 'fresnsModels',
        };

        // user model
        if ($modelName == 'user') {
            if (StrHelper::isPureInt($fsid)) {
                $model = PrimaryHelper::fresnsModelById('user', $fsid);

                $fsidModel = PrimaryHelper::fresnsModelByFsid('user', $fsid);
            } else {
                $model = PrimaryHelper::fresnsModelByFsid('user', $fsid);

                $fsidModel = null;
            }

            CacheHelper::forgetFresnsKeys([
                "fresns_model_user_{$model?->id}",
                "fresns_model_user_{$model?->uid}_by_fsid",
                "fresns_model_user_{$model?->username}_by_fsid",
                "fresns_model_user_{$fsidModel?->id}",
                "fresns_model_user_{$fsidModel?->uid}_by_fsid",
                "fresns_model_user_{$fsidModel?->username}_by_fsid",
            ], $cacheTag);

            return;
        }

        // others
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

        CacheHelper::forgetFresnsKeys([$fsidCacheKey, $idCacheKey], $cacheTag);
    }

    /**
     * forget fresns file usage.
     */
    public static function forgetFresnsFileUsage(int|string $fileIdOrFid): void
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

                    CacheHelper::forgetFresnsMultilingual("fresns_api_post_{$pid}", 'fresnsPosts');
                    CacheHelper::forgetFresnsKeys([
                        "fresns_api_post_{$pid}_list_content",
                        "fresns_api_post_{$pid}_detail_content",
                    ], 'fresnsPosts');
                    break;

                case FileUsage::TYPE_COMMENT:
                    $comment = PrimaryHelper::fresnsModelById('comment', $usage->table_id);
                    $cid = $comment?->cid;

                    CacheHelper::forgetFresnsMultilingual("fresns_api_comment_{$cid}", 'fresnsComments');
                    CacheHelper::forgetFresnsKeys([
                        "fresns_api_comment_{$cid}_list_content",
                        "fresns_api_comment_{$cid}_detail_content",
                    ], 'fresnsComments');
                    break;
            }
        }
    }

    // forget fresns account
    public static function forgetFresnsAccount(?string $aid = null): void
    {
        if (empty($aid)) {
            return;
        }

        CacheHelper::forgetFresnsModel('account', $aid);
        CacheHelper::forgetFresnsMultilingual("fresns_api_account_{$aid}", 'fresnsAccounts');
        CacheHelper::forgetFresnsMultilingual("fresns_web_account_{$aid}", 'fresnsWeb');
    }

    // forget fresns user
    public static function forgetFresnsUser(?int $userId = null, ?int $uid = null): void
    {
        if (empty($userId) && empty($uid)) {
            return;
        }

        CacheHelper::forgetFresnsModel('user', $uid);
        CacheHelper::forgetFresnsMultilingual("fresns_user_{$userId}_main_role", 'fresnsUsers');
        CacheHelper::forgetFresnsMultilingual("fresns_user_{$userId}_roles", 'fresnsUsers');
        CacheHelper::forgetFresnsMultilingual("fresns_publish_post_config_{$userId}", 'fresnsUsers');
        CacheHelper::forgetFresnsMultilingual("fresns_publish_comment_config_{$userId}", 'fresnsUsers');

        CacheHelper::forgetFresnsMultilingual("fresns_api_user_{$uid}", 'fresnsUsers');
        CacheHelper::forgetFresnsMultilingual("fresns_api_user_stats_{$uid}", 'fresnsUsers');
        CacheHelper::forgetFresnsKey("fresns_api_user_panel_conversations_{$uid}", 'fresnsUsers');
        CacheHelper::forgetFresnsKey("fresns_api_user_panel_notifications_{$uid}", 'fresnsUsers');
        CacheHelper::forgetFresnsKey("fresns_api_user_panel_drafts_{$uid}", 'fresnsUsers');

        CacheHelper::forgetFresnsMultilingual("fresns_web_user_{$uid}", 'fresnsWeb');
        CacheHelper::forgetFresnsMultilingual("fresns_web_user_panel_{$uid}", 'fresnsWeb');
        CacheHelper::forgetFresnsMultilingual("fresns_web_channels_{$uid}", 'fresnsWeb');
    }

    /**
     * forget fresns interaction.
     */
    public static function forgetFresnsInteraction(int $type, int $id, int $userId): void
    {
        $typeName = match ($type) {
            1 => 'user',
            2 => 'group',
            3 => 'hashtag',
            4 => 'post',
            5 => 'comment',
        };

        $cacheTag = match ($type) {
            1 => 'fresnsUsers',
            2 => 'fresnsGroups',
            3 => 'fresnsHashtags',
            4 => 'fresnsPosts',
            5 => 'fresnsComments',
        };

        CacheHelper::forgetFresnsKey("fresns_interaction_status_{$type}_{$id}_{$userId}", 'fresnsUsers');
        CacheHelper::forgetFresnsKey("fresns_interaction_status_{$type}_{$userId}_{$id}", 'fresnsUsers');

        CacheHelper::forgetFresnsKeys([
            "fresns_follow_{$type}_array_by_{$userId}",
        ], $cacheTag);

        CacheHelper::forgetFresnsKeys([
            CacheHelper::getNullCacheKey("fresns_follow_{$type}_array_by_{$userId}"),
        ], 'fresnsNullCount');

        if ($type == InteractionUtility::TYPE_GROUP) {
            CacheHelper::forgetFresnsKeys([
                "fresns_user_all_groups_{$userId}",
                "fresns_filter_groups_by_user_{$userId}",
            ], ['fresnsGroups', 'fresnsUsers']);
        }
    }

    /**
     * no tag.
     */
    // fresns_cache_tags
    // fresns_cache_config_keys
    // developer_mode
    // install_{$step}
    // autoUpgradeStep
    // autoUpgradeTip
    // manualUpgradeStep
    // manualUpgradeTip

    /**
     * tag: fresnsSystems.
     */
    // fresns_current_version
    // fresns_new_version
    // fresns_news
    // fresns_database_timezone
    // fresns_database_datetime
    // fresns_panel_translation_{$locale}

    /**
     * tag: fresnsConfigs.
     */
    // fresns_default_langTag
    // fresns_lang_tags
    // fresns_config_file_accept
    // fresns_config_file_url_expire
    // fresns_role_{$id}
    // fresns_code_messages_{$fskey}
    // fresns_plugin_url_{$fskey}
    // fresns_plugin_host_{$fskey}
    // fresns_plugin_version_{$fskey}

    /**
     * fresns model.
     */
    // fresns_model_config_{$itemKey}                               // tag: fresnsConfigs
    // fresns_model_key_{$appId}                                    // tag: fresnsSystems
    // fresns_model_account_{$aid}                                  // tag: fresnsAccounts
    // fresns_model_user_{$uidOrUsername}_by_fsid                   // tag: fresnsUsers
    // fresns_model_user_{$userId}                                  // tag: fresnsUsers
    // fresns_model_group_{$gid}                                    // tag: fresnsGroups
    // fresns_model_group_{$groupId}                                // tag: fresnsGroups
    // fresns_model_hashtag_{$htid}                                 // tag: fresnsHashtags
    // fresns_model_hashtag_{$hashtagId}                            // tag: fresnsHashtags
    // fresns_model_geotag_{$gtid}                                  // tag: fresnsGeotags
    // fresns_model_geotag_{$geotagId}                              // tag: fresnsGeotags
    // fresns_model_post_{$pid}                                     // tag: fresnsPosts
    // fresns_model_post_{$postId}                                  // tag: fresnsPosts
    // fresns_model_comment_{$cid}                                  // tag: fresnsComments
    // fresns_model_comment_{$commentId}                            // tag: fresnsComments
    // fresns_model_file_{$fid}                                     // tag: fresnsFiles
    // fresns_model_file_{$fileId}                                  // tag: fresnsFiles
    // fresns_model_extend_{$eid}                                   // tag: fresnsExtends
    // fresns_model_archive_{$code}                                 // tag: fresnsArchives
    // fresns_model_operation_{$operationId}                        // tag: fresnsOperations
    // fresns_model_conversation_{$conversationId}                  // tag: fresnsConversations
    // fresns_model_seo_{$usageType}_{$usageId}                     // tag: fresnsSeo
    // fresns_model_subgroups_{$idOrGid}                            // tag: fresnsGroups
    // fresns_model_conversation_{$userId}_{$conversationUserId}    // tag: fresnsUsers

    /**
     * fresns detail.
     */
    // fresns_detail_account_{$id}_{$langTag}                       // tag: fresnsAccounts
    // fresns_detail_user_{$id}_{$langTag}                          // tag: fresnsUsers
    // fresns_detail_user_stats_{$id}                               // tag: fresnsUsers
    // fresns_detail_group_{$id}_{$langTag}                         // tag: fresnsGroups
    // fresns_detail_hashtag_{$id}_{$langTag}                       // tag: fresnsHashtags
    // fresns_detail_geotag_{$id}_{$langTag}                        // tag: fresnsGeotags
    // fresns_detail_post_{$id}_{$langTag}                          // tag: fresnsPosts
    // fresns_detail_post_{$id}_preview_like_users_{$langTag}       // tag: fresnsPosts
    // fresns_detail_post_{$id}_preview_comments_{$langTag}         // tag: fresnsPosts
    // fresns_detail_comment_{$id}_{$langTag}                       // tag: fresnsComments
    // fresns_detail_comment_{$id}_preview_like_users_{$langTag}    // tag: fresnsComments
    // fresns_detail_comment_{$id}_preview_comments_{$langTag}      // tag: fresnsComments

    /**
     * fresns token.
     */
    // fresns_token_account_{$accountId}_{$token}                   // tag: fresnsAccounts
    // fresns_token_user_{$userId}_{$token}                         // tag: fresnsUsers

    /**
     * fresns api.
     */
    // fresns_api_configs_{$langTag}                                // tag: fresnsConfigs
    // fresns_api_language_pack_{$langTag}                          // tag: fresnsConfigs
    // fresns_api_archives_{$type}_{$fskey}_{$langTag}              // tag: fresnsConfigs
    // fresns_api_stickers_{$langTag}                               // tag: fresnsConfigs

    /**
     * tag: fresnsUsers.
     */
    // fresns_user_{$userId}_main_role
    // fresns_user_{$userId}_roles
    // fresns_app_badge_{$fskey}_{$userId}

    // fresns_publish_{$type}_config_{$userId}_{$langTag}
    // fresns_interaction_status_{$markType}_{$markId}_{$userId}
    // fresns_follow_{$type}_array_by_{$userId}
    // fresns_user_activity_{$uid}
    // fresns_user_post_auth_{$postId}_{$userId}
    // fresns_api_user_panel_conversations_{$uid}
    // fresns_api_user_panel_notifications_{$uid}
    // fresns_api_user_panel_drafts_{$uid}

    /**
     * tag: fresnsGroups.
     */
    // fresns_group_count
    // fresns_private_groups
    // fresns_filter_group_models
    // fresns_filter_groups_by_guest
    // fresns_filter_groups_by_user_{$userId}   // +tag: fresnsUsers
    // fresns_guest_all_groups
    // fresns_user_all_groups_{$userId}         // +tag: fresnsUsers

    /**
     * tag: fresnsExtensions.
     */
    // fresns_wallet_recharge_extends_by_everyone_{$langTag}
    // fresns_wallet_withdraw_extends_by_everyone_{$langTag}
    // fresns_post_content_types_by_{$typeName}_{$langTag}              // +tag: fresnsConfigs
    // fresns_comment_content_types_by_{$typeName}_{$langTag}           // +tag: fresnsConfigs

    // fresns_editor_post_extends_by_everyone_{$langTag}
    // fresns_editor_comment_extends_by_everyone_{$langTag}
    // fresns_manage_post_extends_by_everyone_{$langTag}
    // fresns_manage_comment_extends_by_everyone_{$langTag}
    // fresns_manage_user_extends_by_everyone_{$langTag}
    // fresns_group_{$groupId}_extends_by_everyone_{$langTag}           // +tag: fresnsGroups
    // fresns_feature_extends_by_everyone_{$langTag}
    // fresns_profile_extends_by_everyone_{$langTag}
    // fresns_channel_extends_by_everyone_{$langTag}

    // fresns_editor_post_extends_by_role_{$roleId}_{$langTag}
    // fresns_editor_comment_extends_by_role_{$roleId}_{$langTag}
    // fresns_manage_post_extends_by_role_{$roleId}_{$langTag}
    // fresns_manage_comment_extends_by_role_{$roleId}_{$langTag}
    // fresns_manage_user_extends_by_role_{$roleId}_{$langTag}
    // fresns_group_{$groupId}_extends_by_role_{$roleId}_{$langTag}     // +tag: fresnsGroups
    // fresns_feature_extends_by_role_{$roleId}_{$langTag}
    // fresns_profile_extends_by_role_{$roleId}_{$langTag}

    // fresns_manage_post_extends_by_group_admin_{$langTag}
    // fresns_manage_comment_extends_by_group_admin_{$langTag}
    // fresns_group_{$groupId}_extends_by_group_admin_{$langTag}        // +tag: fresnsGroups
}
