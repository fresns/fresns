<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Info;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\FsDb\FresnsPluginBadges\FresnsPluginBadges;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPluginsService;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsagesConfig;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Fresns\Api\Helpers\ApiLanguageHelper;

/**
 * List resource config handle.
 */
class FresnsPluginUsagesResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Field
        $formMap = FresnsPluginUsagesConfig::FORM_FIELDS_MAP;
        $formMapFieldsArr = [];
        foreach ($formMap as $k => $dbField) {
            $formMapFieldsArr[$dbField] = $this->$dbField;
        }

        // Extensions List Info
        $langTag = request()->header('langTag', '');
        $name = ApiLanguageHelper::getLanguagesByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $this->id);
        $type = $this->type;
        $plugin = $this->plugin_unikey;
        $icon = ApiFileHelper::getImageSignUrlByFileIdUrl($this->icon_file_id, $this->icon_file_url);
        $url = FresnsPluginsService::getPluginUsagesUrl($plugin, $this->id);
        $number = $this->editor_number;
        $badgesType = null;
        $badgesValue = null;
        $pluginbades = FresnsPluginBadges::where('plugin_unikey', $this->plugin_unikey)->first();
        if ($pluginbades) {
            $badgesType = $pluginbades['display_type'];
            $badgesValue = $pluginbades['value_text'] ?? $pluginbades['value_number'];
        }
        $sortNumber = [];
        if ($this->type == 4) {
            $postLists = self::getTypePluginUsages('postLists', $this->data_sources);
            $postFollows = self::getTypePluginUsages('postLists', $this->data_sources);
            $postNearbys = self::getTypePluginUsages('postLists', $this->data_sources);
            $sortNumber['postLists'] = $postLists;
            $sortNumber['postFollows'] = $postFollows;
            $sortNumber['postNearbys'] = $postNearbys;
        }

        // Default Field
        $default = [
            'type' => $type,
            'plugin' => $plugin,
            'name' => $name,
            'icon' => $icon == null ? '' : $icon,
            'url' => $url,
            'number' => $number,
            'badgesType' => $badgesType,
            'badgesValue' => $badgesValue,
            'sortNumber' => $sortNumber,
        ];

        // Merger
        $arr = $default;

        return $arr;
    }

    // Get Type Plugin Usages
    public static function getTypePluginUsages($key, $data)
    {
        $sort_number = json_decode($data, true);
        $sortNumber = [];
        $introArr = [];
        if ($sort_number) {
            if ($sort_number[$key]) {
                foreach ($sort_number[$key]['sortNumber'] as $k => &$s) {
                    foreach ($s as &$v) {
                        if (! is_array($v)) {
                            $id = $v;
                        }
                        if (is_array($v)) {
                            $intro = [];
                            foreach ($v as $i) {
                                $intro['id'] = $id;
                                $intro['title'] = $i['title'];
                                $intro['description'] = $i['description'];
                                $introArr[] = $intro;
                            }
                        }
                    }
                }
            }
        }

        return $introArr;
    }
}
