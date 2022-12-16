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

    // null cache count
    public static function nullCacheCount(string $cacheKey, string|array $cacheTag, ?int $cacheMinutes = null)
    {
        Cache::pull($cacheKey);

        $nullCacheKey = CacheHelper::getNullCacheKey($cacheKey);

        $currentCacheKeyNullNum = (int) Cache::get($nullCacheKey);

        $now = $cacheMinutes ? now()->addMinutes($cacheMinutes) : CacheHelper::fresnsCacheTimeByFileType();

        $cacheTag = (array) $cacheTag;

        if (CacheHelper::isSupportTags()) {
            Cache::tags($cacheTag)->put($nullCacheKey, ++$currentCacheKeyNullNum, $now);
        } else {
            Cache::put($nullCacheKey, ++$currentCacheKeyNullNum, $now);

            CacheHelper::addCacheItems($cacheKey, $cacheTag);
        }
    }

    // is known to be empty
    public static function isKnownEmpty(string $cacheKey): bool
    {
        $nullCacheKey = CacheHelper::getNullCacheKey($cacheKey);

        // null cache count
        if (Cache::get($nullCacheKey) > CacheHelper::NULL_CACHE_COUNT) {
            return true;
        }

        return false;
    }

    // does the cache support tags
    public static function isSupportTags(): bool
    {
        $isSupportTags = Cache::rememberForever('fresns_cache_is_support_tags', function () {
            $cacheDriver = env('CACHE_DRIVER', 'file');

            if ($cacheDriver == 'file' || $cacheDriver == 'dynamodb' || $cacheDriver == 'database') {
                return false;
            }

            return true;
        });

        return $isSupportTags;
    }

    // cache put
    public static function put(mixed $cacheData, string $cacheKey, string|array $cacheTag, ?int $nullCacheMinutes = null, ?Carbon $cacheTime = null)
    {
        $cacheTag = (array) $cacheTag;

        // null cache count
        if (empty($cacheData)) {
            CacheHelper::nullCacheCount($cacheKey, $cacheTag, $nullCacheMinutes);

            return $cacheData;
        }

        $cacheTime = $cacheTime ?: CacheHelper::fresnsCacheTimeByFileType();

        if (CacheHelper::isSupportTags()) {
            Cache::tags($cacheTag)->put($cacheKey, $cacheData, $cacheTime);
        } else {
            Cache::put($cacheKey, $cacheData, $cacheTime);

            CacheHelper::addCacheItems($cacheKey, $cacheTag);
        }
    }

    // add cache items
    public static function addCacheItems(string $cacheKey, string|array $cacheTag)
    {
        $cacheTag = (array) $cacheTag;

        foreach ($cacheTag as $tag) {
            if ($tag == 'fresnsSystems' || $tag == 'fresnsConfigs' || $tag == 'fresnsLanguages') {
                $cacheItems = Cache::get($tag) ?? [];

                $newCacheItems = Arr::add($cacheItems, $cacheKey, $tag);

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
        \Artisan::call('clear-compiled');
        \Artisan::call('config:cache');
        \Artisan::call('event:cache');
        \Artisan::call('optimize:clear');
        \Artisan::call('route:clear');
        \Artisan::call('schedule:clear-cache');
        \Artisan::call('view:cache');

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
        CacheHelper::forgetFresnsModel('account', $aid);
    }

    // forget fresns user
    public static function forgetFresnsUser(?int $uid = null)
    {
        if (empty($uid)) {
            return;
        }

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

        if ($type == InteractionUtility::TYPE_GROUP) {
            CacheHelper::forgetFresnsKeys([
                "fresns_filter_groups_by_user_{$userId}",
                "fresns_user_{$userId}_all_groups",
            ]);
        }
    }

    /**
     * tag: fresnsSystems
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
     * tag: fresnsConfigs
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
    // fresns_content_block_words // fresns_{$type}_block_words
    // fresns_user_block_words // fresns_{$type}_block_words
    // fresns_conversation_block_words // fresns_{$type}_block_words
    // fresns_content_ban_words
    // fresns_content_review_words
    // fresns_user_ban_words
    // fresns_conversation_ban_words
    // fresns_editor_{$type}_extends_{$roleId}_{$langTag}

    /**
     * tag: fresnsLanguages
     * tag: fresnsUnknownLanguages
     * tag: fresnsPluginUsageLanguages
     * tag: fresnsRoleLanguages
     * tag: fresnsStickerLanguages
     * tag: fresnsNotificationLanguages
     */
    // fresns_{$tableName}_{$tableColumn}_{$tableId}_{$langTag}

    /**
     * tag: fresnsCodeMessages
     */
    // fresns_code_messages_{$unikey}_{$langTag}

    /**
     * tag: fresnsAccounts
     */
    // fresns_token_account_{$accountId}_{$token}                   // +tag: fresnsAccountTokens
    // fresns_model_account_{$aid}                                  // +tag: fresnsAccountModels
    // fresns_api_account_{$aid}_{$langTag}                         // +tag: fresnsAccountData

    /**
     * tag: fresnsUsers
     */
    // fresns_token_user_{$userId}_{$token}                         // +tag: fresnsUserTokens
    // fresns_model_user_{$uidOrUsername}                           // +tag: fresnsUserModels
    // fresns_model_user_{$userId}                                  // +tag: fresnsUserModels
    // fresns_model_conversation_{$userId}_{$conversationUserId}    // +tag: fresnsUserConversations
    // fresns_user_{$userId}_main_role_{$langTag}                   // +tag: fresnsUserRoles
    // fresns_user_{$userId}_roles_{$langTag}                       // +tag: fresnsUserRoles
    // fresns_publish_{$type}_config_{$userId}_{$langTag}           // +tag: fresnsUserConfigs
    // fresns_interaction_status_{$markType}_{$markId}_{$userId}    // +tag: fresnsUserInteractions
    // fresns_follow_{$type}_array_by_{$userId}                     // +tag: fresnsUserInteractions, fresnsFollowData
    // fresns_block_{$type}_array_by_{$userId}                      // +tag: fresnsUserInteractions, fresnsBlockData
    // fresns_seo_user_{$userId}                                    // +tag: fresnsUserData
    // fresns_api_user_{$uid}_{$langTag}                            // +tag: fresnsUserData
    // fresns_api_user_panel_conversations_{$uid}                   // +tag: fresnsUserData, fresnsUserConversations
    // fresns_api_user_panel_notifications_{$uid}                   // +tag: fresnsUserData, fresnsUserNotifications
    // fresns_api_user_panel_drafts_{$uid}                          // +tag: fresnsUserData, fresnsUserDrafts

    /**
     * tag: fresnsGroups
     */
    // fresns_group_count                                           // +tag: fresnsGroupConfigs
    // fresns_private_groups                                        // +tag: fresnsGroupConfigs
    // fresns_filter_groups_by_guest                                // +tag: fresnsGroupConfigs, fresnsUsers, fresnsUserInteractions
    // fresns_filter_groups_by_user_{$userId}                       // +tag: fresnsGroupConfigs, fresnsUsers, fresnsUserInteractions
    // fresns_model_group_{$gid}                                    // +tag: fresnsGroupModels
    // fresns_model_group_{$groupId}                                // +tag: fresnsGroupModels
    // fresns_group_admins_{$groupId}                               // +tag: fresnsGroupAdmins
    // fresns_seo_group_{$groupId}                                  // +tag: fresnsGroupData
    // fresns_guest_all_groups                                      // +tag: fresnsGroupData
    // fresns_user_{$userId}_all_groups                             // +tag: fresnsGroupData, fresnsUsers, fresnsUserData
    // fresns_follow_{$type}_model_{$id}_by_{$authUserId}           // +tag: fresnsGroupData, fresnsUsers, fresnsUserInteractions, fresnsFollowData
    // fresns_api_group_{$gid}_{$langTag}                           // +tag: fresnsGroupData
    // fresns_api_group_{$gid}_extensions_{$userId}_{$langTag}      // +tag: fresnsGroupExtensions

    /**
     * tag: fresnsHashtags
     */
    // fresns_model_hashtag_{$hid}                  // +tag: fresnsHashtagModels
    // fresns_model_hashtag_{$hashtagId}            // +tag: fresnsHashtagModels
    // fresns_seo_hashtag_{$id}                     // +tag: fresnsHashtagData
    // fresns_api_hashtag_{$hid}_{$langTag}         // +tag: fresnsHashtagData

    /**
     * tag: fresnsPosts
     */
    // fresns_model_post_{$pid}                             // +tag: fresnsPostModels
    // fresns_model_post_{$postId}                          // +tag: fresnsPostModels
    // fresns_seo_post_{$id}                                // +tag: fresnsPostData
    // fresns_api_post_{$pid}_{$langTag}                    // +tag: fresnsPostData
    // fresns_api_post_{$pid}_list_content                  // +tag: fresnsPostData
    // fresns_api_post_{$pid}_detail_content                // +tag: fresnsPostData
    // fresns_api_post_{$pid}_allow_{$uid}                  // +tag: fresnsPostData, fresnsUsers, fresnsUserData
    // fresns_api_post_{$postId}_top_comments_{$langTag}    // +tag: fresnsPostData, fresnsComments, fresnsCommentData

    /**
     * tag: fresnsComments
     */
    // fresns_model_comment_{$cid}                                  // +tag: fresnsCommentModels
    // fresns_model_comment_{$commentId}                            // +tag: fresnsCommentModels
    // fresns_seo_comment_{$id}                                     // +tag: fresnsCommentData
    // fresns_api_comment_{$cid}_{$langTag}                         // +tag: fresnsCommentData
    // fresns_api_comment_{$cid}_list_content                       // +tag: fresnsCommentData
    // fresns_api_comment_{$cid}_detail_content                     // +tag: fresnsCommentData
    // fresns_api_comment_{$commentId}_sub_comments_{$langTag}      // +tag: fresnsCommentData

    /**
     * tag: fresnsModels
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
     * tag: fresnsExtensions
     */
    // fresns_wallet_extends_{$langTag}
    // fresns_{$type}_content_types_{$langTag}                  // +tag: fresnsConfigs
    // fresns_{$type}_manages_by_everyone_{$langTag}            // +tag: fresnsManages
    // fresns_{$type}_manages_by_group_{$langTag}               // +tag: fresnsManages, fresnsGroupConfigs
    // fresns_{$type}_manages_by_role_{$roleId}_{$langTag}      // +tag: fresnsManages
    // fresns_features_{$userId}_{$langTag}                     // +tag: fresnsUserData, fresnsUserFeatures
    // fresns_profiles_{$userId}_{$langTag}                     // +tag: fresnsUserData, fresnsUserProfiles
    // fresns_user_manages_{$rid}_{$langTag}                    // +tag: fresnsConfigs

    /**
     * tag: fresnsApiData
     */
    // fresns_api_archives_{$type}_{$unikey}_{$langTag}     // +tag: fresnsArchives
    // fresns_api_sticker_tree_{$langTag}                   // +tag: fresnsConfigs
}
