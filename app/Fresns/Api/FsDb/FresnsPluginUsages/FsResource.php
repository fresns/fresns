<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsPluginUsages;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigsService;
use App\Fresns\Api\FsDb\FresnsLanguages\FresnsLanguages;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRoles;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPlugins;

/**
 * List resource config handle.
 */
class FsResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Field
        $formMap = FsConfig::FORM_FIELDS_MAP;
        $formMapFieldsArr = [];
        foreach ($formMap as $k => $dbField) {
            $formMapFieldsArr[$dbField] = $this->$dbField;
        }

        // Plugin Name
        $plugInfo = FresnsPlugins::where('unikey', $this->plugin_unikey)->first();

        // Languages
        // Get default language tag
        $defaultCode = FsService::getDefaultLanguage();
        $langTag = request()->header('langTag', $defaultCode);
        $input = [
            'table_name' => FsConfig::CFG_TABLE,
            'table_column' => FsConfig::FORM_FIELDS_MAP['name'],
            'table_id' => $this->id,
            'lang_tag' => $langTag,
        ];
        $names = FresnsLanguages::where($input)->first();
        if (! $names) {
            $input = [
                'table_name' => FsConfig::CFG_TABLE,
                'table_column' => FsConfig::FORM_FIELDS_MAP['name'],
                'table_id' => $this->id,
            ];
            $names = FresnsLanguages::where($input)->first();
        }
        // Language Name
        $languageArr = FresnsConfigsService::getLanguageStatus();
        $multilingual = $languageArr['languagesOption'];
        $nameArr = [];
        foreach ($multilingual as $v) {
            $input = [
                'table_name' => FsConfig::CFG_TABLE,
                'table_column' => FsConfig::FORM_FIELDS_MAP['name'],
                'table_id' => $this->id,
                'lang_tag' => $v['key'],
            ];
            $name = FresnsLanguages::where($input)->first();
            $v['lang_content'] = $name['lang_content'] ?? null;
            $nameArr[] = $v;
        }

        // User Roles
        $rolesArr = explode(',', $this->roles);
        $roleInfo = FresnsRoles::whereIn('id', $rolesArr)->pluck('name')->toArray();
        $roleNames = implode(',', $roleInfo);

        // Application Scenarios
        $sceneArr = explode(',', $this->scene);
        $sceneNameArr = [];
        if ($sceneArr) {
            foreach (FsConfig::SCONE_OPTION as $v) {
                $arr = [];
                if (in_array($v['key'], $sceneArr)) {
                    $arr = $v['title'];
                    $sceneNameArr[] = $arr;
                }
            }
        }

        $sceneNames = implode(',', $sceneNameArr);
        $parameter = json_decode($this->parameter, true);
        $sort_number = json_decode($this->data_sources, true);

        $newArr = [];
        // sort_number Parameter Filtering
        if (! $sort_number) {
            $arr = [];
            foreach ($multilingual as &$m) {
                $arr['id'] = null;
                $intro = [];
                $intro['langTag'] = $m['key'];
                $intro['text'] = $m['text'];
                $intro['title'] = null;
                $intro['description'] = null;
                $arr['intro'] = $intro;
                $newArr['postLists'][] = $arr;
                $newArr['postFollows'][] = $arr;
                $newArr['postNearbys'][] = $arr;
            }
        } else {
            // sort_number Parameter Filtering
            $arr1 = [];
            foreach ($sort_number as $k => &$s) {
                foreach ($s as &$v) {
                    $introArr = [];
                    foreach ($v['intro'] as $i) {
                        $map[$i['langTag']] = $i;
                    }
                    foreach ($multilingual as $m) {
                        $item = [];
                        $item['title'] = $map[$m['key']]['title'] ?? null;
                        $item['langTag'] = $m['key'];
                        $item['text'] = $m['text'];
                        $item['description'] = $map[$m['key']]['description'] ?? null;
                        $introArr[] = $item;
                    }

                    $v['intro'] = $introArr;
                }
            }
            $newArr = $sort_number;
        }

        // Data source
        $source_parameter = FsConfig::SOURCE_PARAMETER;
        foreach ($source_parameter as &$v) {
            $v['postLists'] = $parameter[$v['nickname']] ?? null;
            $v['sort_number'] = $newArr[$v['nickname']] ?? null;
        }

        // Default Field
        $default = [
            'id' => $this->id,
            'plug_name' => $plugInfo['name'] ?? null,
            'name' => $names['lang_content'] ?? null,
            'nameArr' => $nameArr,
            'roleNames' => $roleNames,
            'roleNamesArr' => $roleInfo,
            'userRolesArr' => $rolesArr,
            'scene' => $sceneArr,
            'sceneNames' => $sceneNames,
            'source_parameter' => $source_parameter,
            'sort_number' => json_decode($this->sort_number, true),
            'is_enable' => boolval($this->is_enable),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Merger
        $arr = array_merge($formMapFieldsArr, $default);

        return $arr;
    }
}
