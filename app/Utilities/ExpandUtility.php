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
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Helpers\UserHelper;
use App\Models\PluginBadge;
use App\Models\PluginUsage;

class ExpandUtility
{
    public static function getPluginExpands(int $type, ?string $gid = null, ?int $scene = null, ?int $uid = null, ?string $langTag = '')
    {
        $groupId = ! empty($gid) ? PrimaryHelper::fresnsGroupIdByGid($gid) : null;
        $userId = ! empty($uid) ? PrimaryHelper::fresnsUserIdByUid($uid) : 0;
        $langTag = $langTag ?: ConfigHelper::fresnsConfigByItemKey('default_language');

        if ($type == 6) {
            $expandArr = PluginUsage::where('type', 6)->where('group_id', $groupId)->get();
        } else {
            $expandArr = PluginUsage::where('type', $type)
            ->when($scene, function ($query, $scene) {
                $query->where('scene', 'like', "%$scene%");
            })
            ->get();
        }

        $expandList = [];
        foreach ($expandArr as $expand) {
            if ($expand->is_group_admin == 1) {
                $adminCheck = false;

                if ($uid && $gid) {
                    $adminCheck = UserHelper::fresnsUserGroupAdminCheck($uid, $gid);
                }

                if ($adminCheck) {
                    $expandList[] = self::getExpandItemById($expand->id, $userId, $langTag);
                }
            } else {
                $permCheck = false;

                if ($uid) {
                    $roleArr = $expand->roles ? StrHelper::commaStringToArray($expand->roles) : [];
                    $permCheck = UserHelper::fresnsUserRolePermCheck($uid, $roleArr);
                }

                if (empty($expand->roles)) {
                    $expandList[] = self::getExpandItemById($expand->id, $userId, $langTag);
                } elseif ($permCheck) {
                    $expandList[] = self::getExpandItemById($expand->id, $userId, $langTag);
                }
            }
        }

        return $expandList;
    }

    // get expand by id
    public static function getExpandItemById(int $usageId, int $userId = 0, string $langTag)
    {
        $usage = PluginUsage::where('id', $usageId)->first();
        $badge = PluginBadge::where('plugin_unikey', $usage['plugin_unikey'])->where('user_id', $userId)->first();

        $expand['plugin'] = $usage['plugin_unikey'];
        $expand['name'] = LanguageHelper::fresnsLanguageByTableId('plugin_usages', 'name', $usage['id'], $langTag);
        $expand['icon'] = FileHelper::fresnsFileImageUrlByColumn($usage['icon_file_id'], $usage['icon_file_url'], 'imageConfigUrl') ?? null;
        $expand['url'] = PluginHelper::fresnsPluginUsageUrl($usage['plugin_unikey'], $usage['id']);
        $expand['badgesType'] = $badge['display_type'] ?? null;
        $expand['badgesValue'] = match ($expand['badgesType']) {
            default => null,
            1 => $badge['value_number'],
            2 => $badge['value_text'],
        };
        $expand['editorNumber'] = $usage['editor_number'];
        $postByAll = self::getRankNumber('postByAll', $usage['data_sources'], $langTag);
        $postByFollow = self::getRankNumber('postByFollow', $usage['data_sources'], $langTag);
        $postByNearby = self::getRankNumber('postByNearby', $usage['data_sources'], $langTag);
        $rankNumber = array_merge($postByAll, $postByFollow, $postByNearby);
        $expand['rankNumber'] = $rankNumber;

        return $expand;
    }

    // get expand list by ids
    public static function getExpandItemListByIds(array $usageIds, int $userId = 0, string $langTag)
    {
        $expandList = [];
        foreach ($usageIds as $id) {
            $expandList[] = self::getExpandItemById($id, $userId, $langTag);
        }

        return $expandList;
    }

    public static function getRankNumber(string $key, array $dataSources, string $langTag)
    {
        $rankNumberArr = $dataSources[$key]['rankNumber'];

        $rankNumber = [];
        foreach ($rankNumberArr as $arr) {
            $item['id'] = $arr['id'];
            $item['title'] = collect($arr['intro'])->where('langTag', $langTag)->first()['title'] ?? null;
            $item['description'] = collect($arr['intro'])->where('langTag', $langTag)->first()['description'] ?? null;
            $rankNumber[] = $item;
        }

        return $rankNumber;
    }
}
