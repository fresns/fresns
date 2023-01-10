<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;
use App\Helpers\StrHelper;
use App\Models\ArchiveUsage;
use App\Models\Extend;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\Operation;
use App\Models\OperationUsage;
use App\Models\Plugin;
use App\Models\PluginBadge;
use App\Models\PluginUsage;
use Illuminate\Support\Arr;

class ExtendUtility
{
    // get extend cache key
    public static function getExtendCacheKey(int $type, string $typeName, ?int $scene = null, ?int $groupId = null, ?string $langTag = null)
    {
        $sceneName = match ($scene) {
            PluginUsage::SCENE_POST => 'post',
            PluginUsage::SCENE_COMMENT => 'comment',
            PluginUsage::SCENE_USER => 'user',
            default => null,
        };

        $cacheKey = match ($type) {
            PluginUsage::TYPE_WALLET_RECHARGE => "fresns_wallet_recharge_extends_by_{$typeName}_{$langTag}",
            PluginUsage::TYPE_WALLET_WITHDRAW => "fresns_wallet_withdraw_extends_by_{$typeName}_{$langTag}",
            PluginUsage::TYPE_EDITOR => "fresns_editor_{$sceneName}_extends_by_{$typeName}_{$langTag}",
            PluginUsage::TYPE_CONTENT => "fresns_{$sceneName}_content_types_by_{$typeName}_{$langTag}",
            PluginUsage::TYPE_MANAGE => "fresns_manage_{$sceneName}_extends_by_{$typeName}_{$langTag}",
            PluginUsage::TYPE_GROUP => "fresns_group_{$groupId}_extends_by_{$typeName}_{$langTag}",
            PluginUsage::TYPE_FEATURE => "fresns_feature_extends_by_{$typeName}_{$langTag}",
            PluginUsage::TYPE_PROFILE => "fresns_profile_extends_by_{$typeName}_{$langTag}",
            PluginUsage::TYPE_MAP => "fresns_map_extends_by_{$typeName}_{$langTag}",
            default => null,
        };

        return $cacheKey;
    }

    // get extend cache tags
    public static function getExtendCacheTags(int $type)
    {
        $cacheTags = match ($type) {
            PluginUsage::TYPE_WALLET_RECHARGE => ['fresnsExtensions', 'fresnsWallets'],
            PluginUsage::TYPE_WALLET_WITHDRAW => ['fresnsExtensions', 'fresnsWallets'],
            PluginUsage::TYPE_EDITOR => ['fresnsExtensions', 'fresnsEditor'],
            PluginUsage::TYPE_CONTENT => ['fresnsExtensions', 'fresnsContentTypes'],
            PluginUsage::TYPE_MANAGE => ['fresnsExtensions', 'fresnsManages'],
            PluginUsage::TYPE_GROUP => ['fresnsExtensions', 'fresnsGroupConfigs', 'fresnsGroupExtensions'],
            PluginUsage::TYPE_FEATURE => ['fresnsExtensions', 'fresnsFeatures'],
            PluginUsage::TYPE_PROFILE => ['fresnsExtensions', 'fresnsProfiles'],
            PluginUsage::TYPE_MAP => ['fresnsExtensions', 'fresnsMaps'],
        };

        return $cacheTags;
    }

    // get plugin badge
    public static function getPluginBadge(string $unikey, ?int $userId = null)
    {
        $badge['badgeType'] = null;
        $badge['badgeValue'] = null;

        if (empty($userId)) {
            return $badge;
        }

        $cacheKey = "fresns_plugin_{$unikey}_badge_{$userId}";
        $cacheTags = ['fresnsUsers', 'fresnsUserConfigs'];

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return $badge;
        }

        $badge = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($badge)) {
            $badgeModel = PluginBadge::where('plugin_unikey', $unikey)->where('user_id', $userId)->first();
            $badge['badgeType'] = $badgeModel?->display_type;
            $badge['badgeValue'] = match ($badgeModel?->display_type) {
                1 => $badgeModel?->value_number,
                2 => $badgeModel?->value_text,
                default => null,
            };

            CacheHelper::put($badge, $cacheKey, $cacheTags);
        }

        return $badge;
    }

    // get data extend
    public static function getDataExtend(string $contentType, string $dataType)
    {
        $dataConfig = PluginUsage::type(PluginUsage::TYPE_CONTENT)->where('plugin_unikey', $contentType)->isEnable()->value('data_sources');

        if (empty($dataConfig)) {
            return null;
        }

        $dataPluginUnikey = $dataConfig[$dataType]['pluginUnikey'] ?? null;

        $dataPlugin = Plugin::where('unikey', $dataPluginUnikey)->isEnable()->first();

        if (empty($dataPlugin)) {
            return null;
        }

        return $dataPlugin->unikey;
    }

    // get operations
    public static function getOperations(int $type, int $id, ?string $langTag = null)
    {
        $operationQuery = OperationUsage::with('operation')->type($type)->where('usage_id', $id);

        $operationQuery->whereHas('operation', function ($query) {
            $query->where('is_enable', 1);
        });

        $operations = $operationQuery->get()->map(function ($operationUse) use ($langTag) {
            $item['type'] = $operationUse->operation->type;
            $item['code'] = $operationUse->operation->code;
            $item['style'] = $operationUse->operation->style;
            $item['name'] = LanguageHelper::fresnsLanguageByTableId('operations', 'name', $operationUse->operation->id, $langTag);
            $item['description'] = LanguageHelper::fresnsLanguageByTableId('operations', 'description', $operationUse->operation->id, $langTag);
            $item['imageUrl'] = FileHelper::fresnsFileUrlByTableColumn($operationUse->operation->image_file_id, $operationUse->operation->image_file_url);
            $item['imageActiveUrl'] = FileHelper::fresnsFileUrlByTableColumn($operationUse->operation->image_active_file_id, $operationUse->operation->image_active_file_url);
            $item['displayType'] = $operationUse->operation->display_type;
            $item['pluginUrl'] = PluginHelper::fresnsPluginUrlByUnikey($operationUse->operation->plugin_unikey);

            return $item;
        })->groupBy('type');

        $operationList['customizes'] = $operations->get(Operation::TYPE_CUSTOMIZE)?->all() ?? [];
        $operationList['buttonIcons'] = $operations->get(Operation::TYPE_BUTTON_ICON)?->all() ?? [];
        $operationList['diversifyImages'] = $operations->get(Operation::TYPE_DIVERSIFY_IMAGE)?->all() ?? [];
        $operationList['tips'] = $operations->get(Operation::TYPE_TIP)?->all() ?? [];

        return $operationList;
    }

    // get archives
    public static function getArchives(int $type, int $id, ?string $langTag = null)
    {
        $archiveQuery = ArchiveUsage::with('archive')->type($type)->where('usage_id', $id)->where('is_private', 0);

        $archiveQuery->whereHas('archive', function ($query) {
            $query->where('is_enable', 1)->orderBy('rating');
        });

        $archiveUsages = $archiveQuery->get();

        $archiveList = [];
        foreach ($archiveUsages as $use) {
            $archive = $use->archive;

            $item['code'] = $archive->code;
            $item['name'] = LanguageHelper::fresnsLanguageByTableId('archives', 'name', $archive->id, $langTag);

            if ($archive->api_type == 'file' && StrHelper::isPureInt($use->archive_value)) {
                $item['value'] = ConfigHelper::fresnsConfigFileUrlByItemKey($use->archive_value);
            } elseif ($archive->api_type == 'plugin') {
                $item['value'] = PluginHelper::fresnsPluginUrlByUnikey($use->archive_value);
            } elseif ($archive->api_type == 'plugins') {
                if ($use->archive_value) {
                    foreach ($use->archive_value as $plugin) {
                        $plugin['code'] = $plugin['code'];
                        $plugin['url'] = PluginHelper::fresnsPluginUrlByUnikey($plugin['unikey']);
                        $pluginArr[] = $plugin;
                    }
                    $item['value'] = $pluginArr;
                }
            } else {
                $item['value'] = $use->archive_value;
            }

            $archiveList[] = $item;
        }

        return $archiveList;
    }

    // get content extends
    public static function getContentExtends(int $type, int $id, ?string $langTag = null)
    {
        $extendQuery = ExtendUsage::with('extend')->type($type)->where('usage_id', $id)->orderBy('rating');

        $extendQuery->whereHas('extend', function ($query) {
            $query->where('is_enable', 1);
        });

        $extends = $extendQuery->get()->map(function ($extendUsage) use ($langTag) {
            $item['eid'] = $extendUsage->extend->eid;
            $item['type'] = $extendUsage->extend->type;
            $item['textContent'] = $extendUsage->extend->text_content;
            $item['textIsMarkdown'] = (bool) $extendUsage->extend->text_is_markdown;
            $item['infoType'] = $extendUsage->extend->info_type;
            $item['infoTypeString'] = StrHelper::infoTypeString($extendUsage->extend->info_type);
            $item['cover'] = FileHelper::fresnsFileUrlByTableColumn($extendUsage->extend->cover_file_id, $extendUsage->extend->cover_file_url);
            $item['title'] = LanguageHelper::fresnsLanguageByTableId('extends', 'title', $extendUsage->extend->id, $langTag) ?? $extendUsage->extend->title;
            $item['titleColor'] = $extendUsage->extend->title_color;
            $item['descPrimary'] = LanguageHelper::fresnsLanguageByTableId('extends', 'desc_primary', $extendUsage->extend->id, $langTag) ?? $extendUsage->extend->desc_primary;
            $item['descPrimaryColor'] = $extendUsage->extend->desc_primary_color;
            $item['descSecondary'] = LanguageHelper::fresnsLanguageByTableId('extends', 'desc_secondary', $extendUsage->extend->id, $langTag) ?? $extendUsage->extend->desc_secondary;
            $item['descSecondaryColor'] = $extendUsage->extend->desc_secondary_color;
            $item['buttonName'] = LanguageHelper::fresnsLanguageByTableId('extends', 'button_name', $extendUsage->extend->id, $langTag) ?? $extendUsage->extend->button_name;
            $item['buttonColor'] = $extendUsage->extend->button_color;
            $item['position'] = $extendUsage->extend->position;
            $item['accessUrl'] = PluginHelper::fresnsPluginUsageUrl($extendUsage->extend->plugin_unikey, $extendUsage->extend->parameter);
            $item['moreJson'] = $extendUsage->extend->more_json;

            return $item;
        })->groupBy('type');

        $operationList['textBox'] = $extends->get(Extend::TYPE_TEXT_BOX)?->all() ?? [];
        $operationList['infoBox'] = $extends->get(Extend::TYPE_INFO_BOX)?->all() ?? [];
        $operationList['interactionBox'] = $extends->get(Extend::TYPE_INTERACTIVE_BOX)?->all() ?? [];

        return $operationList;
    }

    // get extends by everyone
    public static function getExtendsByEveryone(int $type, ?int $scene = null, ?int $groupId = null, ?string $langTag = null)
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();
        $cacheKey = ExtendUtility::getExtendCacheKey($type, 'everyone', $scene, $groupId, $langTag);
        $cacheTags = ExtendUtility::getExtendCacheTags($type);

        if (empty($cacheKey)) {
            return [];
        }

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        $extendList = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($extendList)) {
            $extendQuery = PluginUsage::where('usage_type', $type)->where('is_group_admin', 0);

            $extendQuery->when($scene, function ($query, $value) {
                $query->where('scene', 'like', "%$value%");
            });

            $extendQuery->when($groupId, function ($query, $value) {
                $query->where('group_id', $value);
            });

            $extendArr = $extendQuery->orderBy('rating')->get();

            $extendList = [];
            foreach ($extendArr as $extend) {
                if ($extend->roles) {
                    continue;
                }

                $extendList[] = $extend->getUsageInfo($langTag);
            }

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($extendList, $cacheKey, $cacheTags, null, $cacheTime);
        }

        return $extendList;
    }

    // get extends by role
    public static function getExtendsByRole(int $type, int $roleId, ?int $scene = null, ?int $groupId = null, ?string $langTag = null)
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();
        $cacheKey = ExtendUtility::getExtendCacheKey($type, "role_{$roleId}", $scene, $groupId, $langTag);
        $cacheTags = ExtendUtility::getExtendCacheTags($type);

        if (empty($cacheKey)) {
            return [];
        }

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        $extendList = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($extendList)) {
            $extendQuery = PluginUsage::where('usage_type', $type)->where('is_group_admin', 0);

            $extendQuery->when($scene, function ($query, $value) {
                $query->where('scene', 'like', "%$value%");
            });

            $extendQuery->when($groupId, function ($query, $value) {
                $query->where('group_id', $value);
            });

            $extendArr = $extendQuery->orderBy('rating')->get();

            $extendList = [];
            foreach ($extendArr as $extend) {
                if (empty($extend->roles)) {
                    continue;
                }

                $roleArr = explode(',', $extend->roles);

                if (! in_array($roleId, $roleArr)) {
                    continue;
                }

                $extendList[] = $extend->getUsageInfo($langTag);
            }

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($extendList, $cacheKey, $cacheTags, null, $cacheTime);
        }

        return $extendList;
    }

    // get extends by group admin
    public static function getExtendsByGroupAdmin(string $type, ?int $scene = null, ?int $groupId = null, ?string $langTag = null)
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();
        $cacheKey = ExtendUtility::getExtendCacheKey($type, 'group_admin', $scene, $groupId, $langTag);
        $cacheTags = ExtendUtility::getExtendCacheTags($type);

        if (empty($cacheKey)) {
            return [];
        }

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        $extendList = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($extendList)) {
            $extendQuery = PluginUsage::where('usage_type', $type)->where('is_group_admin', 1);

            $extendQuery->when($scene, function ($query, $value) {
                $query->where('scene', 'like', "%$value%");
            });

            $extendQuery->when($groupId, function ($query, $value) {
                $query->where('group_id', $value);
            });

            $extendArr = $extendQuery->orderBy('rating')->get();

            $extendList = [];
            foreach ($extendArr as $extend) {
                $extendList[] = $extend->getUsageInfo($langTag);
            }

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($extendList, $cacheKey, $cacheTags, null, $cacheTime);
        }

        return $extendList;
    }

    /**
     * get extensions.
     */

    // get editor extensions
    public static function getEditorExtensions(string $type, int $authUserId, string $langTag)
    {
        $scene = match ($type) {
            'post' => 1,
            'comment' => 2,
            'posts' => 1,
            'comments' => 2,
            default => null,
        };

        if (empty($scene)) {
            return [];
        }

        $everyoneExtends = ExtendUtility::getExtendsByEveryone(PluginUsage::TYPE_EDITOR, $scene, null, $langTag);

        $roleExtends = [];
        $roleArr = PermissionUtility::getUserRoles($authUserId, $langTag);
        foreach ($roleArr as $role) {
            $roleExtends[] = ExtendUtility::getExtendsByRole(PluginUsage::TYPE_EDITOR, $role['rid'], $scene, null, $langTag);
        }

        $allExtends = array_merge($everyoneExtends, Arr::collapse($roleExtends));

        if (empty($allExtends)) {
            return [];
        }

        $unikeys = array_column($allExtends, 'name');
        $unikeys = array_unique($unikeys);
        $newAllExtends = array_intersect_key($allExtends, $unikeys);

        return array_values($newAllExtends);
    }

    // get manage extensions
    public static function getManageExtensions(string $type, string $langTag, ?int $authUserId = null, ?int $groupId = null)
    {
        if (empty($authUserId)) {
            return [];
        }

        $scene = match ($type) {
            'post' => PluginUsage::SCENE_POST,
            'comment' => PluginUsage::SCENE_COMMENT,
            'user' => PluginUsage::SCENE_USER,
            default => null,
        };

        $everyoneManages = ExtendUtility::getExtendsByEveryone(PluginUsage::TYPE_MANAGE, $scene, null, $langTag);

        $roleManages = [];
        $roleArr = PermissionUtility::getUserRoles($authUserId, $langTag);
        foreach ($roleArr as $role) {
            $roleManages[] = ExtendUtility::getExtendsByRole(PluginUsage::TYPE_MANAGE, $role['rid'], $scene, null, $langTag);
        }

        $groupManages = [];
        if ($groupId) {
            $checkGroupAdmin = PermissionUtility::checkUserGroupAdmin($groupId, $authUserId);
            $groupManages = $checkGroupAdmin ? ExtendUtility::getExtendsByGroupAdmin(PluginUsage::TYPE_MANAGE, $scene, null, $langTag) : [];
        }

        $allManageExtends = array_merge($everyoneManages, Arr::collapse($roleManages), $groupManages);

        if (empty($allManageExtends)) {
            return [];
        }

        $unikeys = array_column($allManageExtends, 'name');
        $unikeys = array_unique($unikeys);
        $newManageExtends = array_intersect_key($allManageExtends, $unikeys);

        return array_values($newManageExtends);
    }

    // get user extensions
    public static function getUserExtensions(string $type, int $authUserId, string $langTag)
    {
        $usageType = match ($type) {
            'feature' => PluginUsage::TYPE_FEATURE,
            'profile' => PluginUsage::TYPE_PROFILE,
            'features' => PluginUsage::TYPE_FEATURE,
            'profiles' => PluginUsage::TYPE_PROFILE,
            default => null,
        };

        if (empty($usageType)) {
            return [];
        }

        $everyoneExtends = ExtendUtility::getExtendsByEveryone($usageType, null, null, $langTag);

        $roleExtends = [];
        $roleArr = PermissionUtility::getUserRoles($authUserId, $langTag);
        foreach ($roleArr as $role) {
            $roleExtends[] = ExtendUtility::getExtendsByRole($usageType, $role['rid'], null, null, $langTag);
        }

        $allExtends = array_merge($everyoneExtends, Arr::collapse($roleExtends));

        if (empty($allExtends)) {
            return [];
        }

        $unikeys = array_column($allExtends, 'name');
        $unikeys = array_unique($unikeys);
        $newAllExtends = array_intersect_key($allExtends, $unikeys);
        $newAllExtends = array_values($newAllExtends);

        $userExtensions = [];
        foreach ($newAllExtends as $extend) {
            $badge = ExtendUtility::getPluginBadge($extend['unikey'], $authUserId);

            $extend['badgeType'] = $badge['badgeType'];
            $extend['badgeValue'] = $badge['badgeValue'];

            $userExtensions[] = $extend;
        }

        return $userExtensions;
    }

    // get group extensions
    public static function getGroupExtensions(int $groupId, string $langTag, ?int $authUserId = null)
    {
        $everyoneExtends = ExtendUtility::getExtendsByEveryone(PluginUsage::TYPE_GROUP, null, $groupId, $langTag);

        $roleExtends = [];
        $roleArr = PermissionUtility::getUserRoles($authUserId, $langTag);
        foreach ($roleArr as $role) {
            $roleExtends[] = ExtendUtility::getExtendsByRole(PluginUsage::TYPE_GROUP, $role['rid'], null, $groupId, $langTag);
        }

        $groupAdminExtends = [];
        if ($groupId) {
            $checkGroupAdmin = PermissionUtility::checkUserGroupAdmin($groupId, $authUserId);
            $groupAdminExtends = $checkGroupAdmin ? ExtendUtility::getExtendsByGroupAdmin(PluginUsage::TYPE_GROUP, null, $groupId, $langTag) : [];
        }

        $allExtends = array_merge($everyoneExtends, Arr::collapse($roleExtends), $groupAdminExtends);

        if (empty($allExtends)) {
            return [];
        }

        $unikeys = array_column($allExtends, 'name');
        $unikeys = array_unique($unikeys);
        $newAllExtends = array_intersect_key($allExtends, $unikeys);
        $newAllExtends = array_values($newAllExtends);

        $groupExtensions = [];
        foreach ($newAllExtends as $extend) {
            $badge = ExtendUtility::getPluginBadge($extend['unikey'], $authUserId);

            $extend['badgeType'] = $badge['badgeType'];
            $extend['badgeValue'] = $badge['badgeValue'];

            $groupExtensions[] = $extend;
        }

        return $groupExtensions;
    }
}
