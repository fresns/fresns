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
    public static function getExtendCacheKey(int $type, string $typeName, ?int $scene = null, ?int $groupId = null, ?string $langTag = null): ?string
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
            PluginUsage::TYPE_CHANNEL => "fresns_channel_extends_by_{$typeName}_{$langTag}",
            default => null,
        };

        return $cacheKey;
    }

    // get extend cache tags
    public static function getExtendCacheTags(int $type): array
    {
        $cacheTags = match ($type) {
            PluginUsage::TYPE_WALLET_RECHARGE => ['fresnsExtensions'],
            PluginUsage::TYPE_WALLET_WITHDRAW => ['fresnsExtensions'],
            PluginUsage::TYPE_EDITOR => ['fresnsExtensions'],
            PluginUsage::TYPE_CONTENT => ['fresnsExtensions', 'fresnsConfigs'],
            PluginUsage::TYPE_MANAGE => ['fresnsExtensions'],
            PluginUsage::TYPE_GROUP => ['fresnsExtensions', 'fresnsGroups'],
            PluginUsage::TYPE_FEATURE => ['fresnsExtensions'],
            PluginUsage::TYPE_PROFILE => ['fresnsExtensions'],
            PluginUsage::TYPE_CHANNEL => ['fresnsExtensions'],
        };

        return $cacheTags;
    }

    // get plugin badge
    public static function getPluginBadge(string $fskey, ?int $userId = null): array
    {
        $badge['badgeType'] = null;
        $badge['badgeValue'] = null;

        if (empty($userId)) {
            return $badge;
        }

        $cacheKey = "fresns_plugin_{$fskey}_badge_{$userId}";
        $cacheTag = 'fresnsUsers';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return $badge;
        }

        $badge = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($badge)) {
            $badgeModel = PluginBadge::where('plugin_fskey', $fskey)->where('user_id', $userId)->first();

            $badge['badgeType'] = $badgeModel?->display_type;
            $badge['badgeValue'] = match ($badgeModel?->display_type) {
                1 => $badgeModel?->value_number,
                2 => $badgeModel?->value_text,
                default => null,
            };

            CacheHelper::put($badge, $cacheKey, $cacheTag);
        }

        return $badge;
    }

    // get data extend
    public static function getDataExtend(string $contentType, string $dataType): ?string
    {
        $dataConfig = PluginUsage::type(PluginUsage::TYPE_CONTENT)->where('plugin_fskey', $contentType)->isEnabled()->value('data_sources');

        if (empty($dataConfig)) {
            return null;
        }

        $dataPluginFskey = $dataConfig[$dataType]['pluginFskey'] ?? null;

        $dataPlugin = Plugin::where('fskey', $dataPluginFskey)->isEnabled()->first();

        if (empty($dataPlugin)) {
            return null;
        }

        return $dataPlugin->fskey;
    }

    // get operations
    public static function getOperations(int $type, int $id, ?string $langTag = null): array
    {
        $operationQuery = OperationUsage::with('operation')->type($type)->where('usage_id', $id);

        $operationQuery->whereHas('operation', function ($query) {
            $query->where('is_enabled', true);
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
            $item['pluginUrl'] = PluginHelper::fresnsPluginUrlByFskey($operationUse->operation->plugin_fskey);

            return $item;
        })->groupBy('type');

        $operationList['customizes'] = $operations->get(Operation::TYPE_CUSTOMIZE)?->all() ?? [];
        $operationList['buttonIcons'] = $operations->get(Operation::TYPE_BUTTON_ICON)?->all() ?? [];
        $operationList['diversifyImages'] = $operations->get(Operation::TYPE_DIVERSIFY_IMAGE)?->all() ?? [];
        $operationList['tips'] = $operations->get(Operation::TYPE_TIP)?->all() ?? [];

        return $operationList;
    }

    // get archives
    public static function getArchives(int $type, int $id, ?string $langTag = null): array
    {
        $archiveQuery = ArchiveUsage::with('archive')->type($type)->where('usage_id', $id);

        $archiveQuery->whereHas('archive', function ($query) {
            $query->where('is_enabled', true)->orderBy('rating');
        });

        $archiveUsages = $archiveQuery->get();

        $archiveList = [];
        foreach ($archiveUsages as $use) {
            $archive = $use->archive;
            if (empty($archive)) {
                continue;
            }

            $pluginArr = [];
            if ($archive->value_type == 'plugins') {
                foreach ($use->archive_value ?? [] as $plugin) {
                    $plugin['code'] = $plugin['code'];
                    $plugin['url'] = PluginHelper::fresnsPluginUrlByFskey($plugin['fskey']) ?? $plugin['fskey'];

                    $pluginArr[] = $plugin;
                }
            }

            $archiveValue = match ($archive->value_type) {
                'file' => StrHelper::isPureInt($use->archive_value) ? FileHelper::fresnsFileUrlById($use->archive_value) : $use->archive_value,
                'plugin' => PluginHelper::fresnsPluginUrlByFskey($use->archive_value) ?? $use->archive_value,
                'plugins' => $pluginArr,
                'number' => (int) $use->archive_value,
                'boolean' => (bool) $use->archive_value,
                'array' => (array) $use->archive_value,
                'object' => (object) $use->archive_value,
                default => $use->archive_value,
            };

            $item['code'] = $archive->code;
            $item['name'] = LanguageHelper::fresnsLanguageByTableId('archives', 'name', $archive->id, $langTag) ?? $archive->name;
            $item['description'] = LanguageHelper::fresnsLanguageByTableId('archives', 'description', $archive->id, $langTag) ?? $archive->description;
            $item['value'] = $archiveValue;
            $item['isPrivate'] = (bool) $use->is_private;

            $archiveList[] = $item;
        }

        return $archiveList;
    }

    // get content extends
    public static function getContentExtends(int $type, int $id, ?string $langTag = null): array
    {
        $extendQuery = ExtendUsage::with('extend')->type($type)->where('usage_id', $id)->orderBy('rating');

        $extendQuery->whereHas('extend', function ($query) {
            $query->where('is_enabled', true);
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
            $item['accessUrl'] = PluginHelper::fresnsPluginUsageUrl($extendUsage->extend->plugin_fskey, $extendUsage->extend->parameter);
            $item['moreJson'] = $extendUsage->extend->more_json;

            return $item;
        })->groupBy('type');

        $operationList['textBox'] = $extends->get(Extend::TYPE_TEXT_BOX)?->all() ?? [];
        $operationList['infoBox'] = $extends->get(Extend::TYPE_INFO_BOX)?->all() ?? [];
        $operationList['interactionBox'] = $extends->get(Extend::TYPE_INTERACTIVE_BOX)?->all() ?? [];

        return $operationList;
    }

    // get extends by everyone
    public static function getExtendsByEveryone(int $type, ?int $scene = null, ?int $groupId = null, ?string $langTag = null): array
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
    public static function getExtendsByRole(int $type, int $roleId, ?int $scene = null, ?int $groupId = null, ?string $langTag = null): array
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
    public static function getExtendsByGroupAdmin(string $type, ?int $scene = null, ?int $groupId = null, ?string $langTag = null): array
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
    public static function getEditorExtensions(string $type, int $authUserId, string $langTag): array
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

        $fskeys = array_column($allExtends, 'name');
        $fskeys = array_unique($fskeys);
        $newAllExtends = array_intersect_key($allExtends, $fskeys);

        return array_values($newAllExtends);
    }

    // get manage extensions
    public static function getManageExtensions(string $type, string $langTag, ?int $authUserId = null, ?int $groupId = null): array
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

        $fskeys = array_column($allManageExtends, 'name');
        $fskeys = array_unique($fskeys);
        $newManageExtends = array_intersect_key($allManageExtends, $fskeys);

        return array_values($newManageExtends);
    }

    // get user extensions
    public static function getUserExtensions(string $type, int $authUserId, string $langTag): array
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

        $fskeys = array_column($allExtends, 'name');
        $fskeys = array_unique($fskeys);
        $newAllExtends = array_intersect_key($allExtends, $fskeys);
        $newAllExtends = array_values($newAllExtends);

        $userExtensions = [];
        foreach ($newAllExtends as $extend) {
            $badge = ExtendUtility::getPluginBadge($extend['fskey'], $authUserId);

            $extend['badgeType'] = $badge['badgeType'];
            $extend['badgeValue'] = $badge['badgeValue'];

            $userExtensions[] = $extend;
        }

        return $userExtensions;
    }

    // get group extensions
    public static function getGroupExtensions(int $groupId, string $langTag, ?int $authUserId = null): array
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

        $fskeys = array_column($allExtends, 'name');
        $fskeys = array_unique($fskeys);
        $newAllExtends = array_intersect_key($allExtends, $fskeys);
        $newAllExtends = array_values($newAllExtends);

        $groupExtensions = [];
        foreach ($newAllExtends as $extend) {
            $badge = ExtendUtility::getPluginBadge($extend['fskey'], $authUserId);

            $extend['badgeType'] = $badge['badgeType'];
            $extend['badgeValue'] = $badge['badgeValue'];

            $groupExtensions[] = $extend;
        }

        return $groupExtensions;
    }
}
