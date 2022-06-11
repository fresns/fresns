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
use App\Models\PluginUsage;
use App\Models\Icon;
use App\Models\IconLinked;
use App\Models\Tip;
use App\Models\TipLinked;
use App\Models\Extend;
use App\Models\ExtendLinked;
use App\Models\Plugin;

class ExtendUtility
{
    // get plugin usage
    public static function getPluginExtends(int $type, ?int $groupId = null, ?int $scene = null, ?int $userId = null, ?string $langTag = null)
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigByItemKey('default_language');

        if ($type == 6) {
            $extendArr = PluginUsage::where('type', $type)->where('group_id', $groupId)->orderBy('rating')->get();
        } else {
            $extendArr = PluginUsage::where('type', $type)->when($scene, function ($query, $scene) {
                $query->where('scene', 'like', "%$scene%");
            })->orderBy('rating')->get();
        }

        $extendList = null;
        foreach ($extendArr as $extend) {
            if ($extend->is_group_admin == 1) {

                if (! empty($userId) && ! empty($groupId)) {
                    $adminCheck = PermissionUtility::checkUserGroupAdmin($groupId, $userId);
                } else {
                    $adminCheck = false;
                }

                if ($adminCheck) {
                    $extendList[] = $extend->getUsageInfo($langTag, $userId);
                }
            } else {

                if (! empty($userId) && ! empty($extend->roles)) {
                    $roleArr = explode(',', $extend->roles);
                    $permCheck = PermissionUtility::checkUserRolePerm($userId, $roleArr);
                } else {
                    $permCheck = false;
                }

                if (empty($extend->roles) || $permCheck) {
                    $extendList[] = $extend->getUsageInfo($langTag, $userId);
                }
            }
        }

        return $extendList;
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

    // get icons
    public static function getIcons(int $type, int $id, ?string $langTag = null)
    {
        $iconLinkedArr = IconLinked::where('linked_type', $type)->where('linked_id', $id)->get()->toArray();
        $iconArr = Icon::whereIn('id', array_column($iconLinkedArr, 'icon_id'))->isEnable()->get();

        $iconList = null;
        foreach ($iconArr as $icon) {
            foreach ($iconLinkedArr as $iconLinked) {
                if ($iconLinked['icon_id'] !== $icon['id']) {
                    continue;
                }
                $item['code'] = $iconLinked['icon_code'];
                $item['name'] = LanguageHelper::fresnsLanguageByTableId('icons', 'name', $icon['id'], $langTag);
                $item['icon'] = FileHelper::fresnsFileUrlByTableColumn($icon['icon_file_id'], $icon['icon_file_url']);
                $item['iconActive'] = FileHelper::fresnsFileUrlByTableColumn($icon['active_icon_file_id'], $icon['active_icon_file_url']);
                $item['type'] = $icon['type'];
                $item['url'] = ! empty($icon['plugin_unikey']) ? PluginHelper::fresnsPluginUrlByUnikey($icon['plugin_unikey']) : null;
            }

            $iconList[] = $item;
        }

        return $iconList;
    }

    // get tips
    public static function getTips(int $type, int $id, ?string $langTag = null)
    {
        $tipLinkedArr = TipLinked::where('linked_type', $type)->where('linked_id', $id)->get()->toArray();
        $tipArr = Tip::whereIn('id', array_column($tipLinkedArr, 'tip_id'))->isEnable()->get();

        $tipList = null;
        foreach ($tipArr as $tip) {
            $item['icon'] = FileHelper::fresnsFileUrlByTableColumn($tip['icon_file_id'], $tip['icon_file_url']);
            $item['content'] = LanguageHelper::fresnsLanguageByTableId('tips', 'content', $tip->id, $langTag);
            $item['style'] = $tip->style;
            $item['type'] = $tip->type;
            $item['url'] = ! empty($tip->plugin_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($tip->plugin_unikey) : null;

            $tipList[] = $item;
        }

        return $tipList;
    }

    // get extends
    public static function getExtends(int $type, int $id, ?string $langTag = null)
    {
        $extendLinkedArr = ExtendLinked::where('linked_type', $type)->where('linked_id', $id)->orderBy('rating')->get()->toArray();
        $extendArr = Extend::whereIn('id', array_column($extendLinkedArr, 'extend_id'))->isEnable()->get();

        $extendList = null;
        foreach ($extendArr as $extend) {
            $item['eid'] = $extend->eid;
            $item['frameType'] = $extend->frame_type;
            $item['framePosition'] = $extend->frame_position;
            $item['textContent'] = $extend->text_content;
            $item['textIsMarkdown'] = $extend->text_is_markdown;
            $item['cover'] = FileHelper::fresnsFileUrlByTableColumn($extend['cover_file_id'], $extend['cover_file_url']);
            $item['title'] = LanguageHelper::fresnsLanguageByTableId('extends', 'title', $extend->id, $langTag);
            $item['titleColor'] = $extend->title_color;
            $item['descPrimary'] = LanguageHelper::fresnsLanguageByTableId('extends', 'desc_primary', $extend->id, $langTag);
            $item['descPrimaryColor'] = $extend->desc_primary_color;
            $item['descSecondary'] = LanguageHelper::fresnsLanguageByTableId('extends', 'desc_secondary', $extend->id, $langTag);
            $item['descSecondaryColor'] = $extend->desc_secondary_color;
            $item['btnName'] = LanguageHelper::fresnsLanguageByTableId('extends', 'btn_name', $extend->id, $langTag);
            $item['type'] = $extend->extend_type;
            $item['target'] = $extend->extend_target;
            $item['value'] = $extend->extend_value;
            $item['support'] = $extend->extend_support;
            $item['moreJson'] = $extend->more_json;

            $extendList[] = $item;
        }

        return $extendList;
    }
}
