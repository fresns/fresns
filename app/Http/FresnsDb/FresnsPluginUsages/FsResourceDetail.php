<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsPluginUsages;

use App\Base\Resources\BaseAdminResource;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsService;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguages;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;

/**
 * Detail resource config handle.
 */
class FsResourceDetail extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Field
        $formMap = FsConfig::FORM_FIELDS_MAP;
        $formMapFieldsArr = [];
        foreach ($formMap as $k => $dbField) {
            $formMapFieldsArr[$dbField] = $this->$dbField;
        }

        // Languages
        $languageArr = FresnsConfigsService::getLanguageStatus();
        $multilingual = $languageArr['languagesOption'];
        $nameArr = [];
        foreach ($multilingual as $v) {
            $input = [
                'table_name' => FsConfig::CFG_TABLE,
                'table_field' => FsConfig::FORM_FIELDS_MAP['name'],
                'table_id' => $this->id,
                'lang_tag' => $v['key'],
            ];
            $name = FresnsLanguages::where($input)->first();
            $v['lang_content'] = $name['lang_content'] ?? '';
            $nameArr[] = $v;
        }

        // Member Roles
        $member_rolesArr = [];
        $roleNames = '';
        if ($this->member_roles) {
            $member_rolesArr = explode(',', $this->member_roles);
            $roleInfo = FresnsMemberRoles::whereIn('id', $member_rolesArr)->pluck('name')->toArray();
            $roleNames = implode(',', $roleInfo);
        }

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

        // Default Field
        $default = [
            'id' => $this->id,
            'name' => $nameArr,
            'roleInfo' => $roleInfo,
            'roleNames' => $roleNames,
            'scene' => $sceneArr,
            'memberRolesArr' => $member_rolesArr,
            'sceneNames' => $sceneNames,
            'is_enable' => boolval($this->is_enable),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Merger
        $arr = array_merge($formMapFieldsArr, $default);

        return $arr;
    }
}
