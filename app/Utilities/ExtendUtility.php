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
use App\Helpers\StrHelper;
use App\Models\ArchiveUsage;
use App\Models\Extend;
use App\Models\ExtendUsage;
use App\Models\Operation;
use App\Models\OperationUsage;
use App\Models\Plugin;
use App\Models\PluginUsage;

class ExtendUtility
{
    // get plugin usages
    public static function getPluginUsages(int $type, ?int $groupId = null, ?int $scene = null, ?int $userId = null, ?string $langTag = null)
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        if ($type == 6) {
            $extendArr = PluginUsage::where('usage_type', $type)->where('group_id', $groupId)->orderBy('rating')->get();
        } else {
            $extendArr = PluginUsage::where('usage_type', $type)->when($scene, function ($query, $scene) {
                $query->where('scene', 'like', "%$scene%");
            })->orderBy('rating')->get();
        }

        $extendList = [];
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
    public static function getExtends(int $type, int $id, ?string $langTag = null)
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
        $operationList['interactiveBox'] = $extends->get(Extend::TYPE_INTERACTIVE_BOX)?->all() ?? [];

        return $operationList;
    }
}
