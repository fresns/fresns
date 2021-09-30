<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsGroups;

use App\Base\Resources\BaseAdminResource;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsService;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguages;

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

        // Language Name
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

        $descriptionArr = [];
        foreach ($multilingual as $v) {
            $input = [
                'table_name' => FsConfig::CFG_TABLE,
                'table_field' => FsConfig::FORM_FIELDS_MAP['description'],
                'table_id' => $this->id,
                'lang_tag' => $v['key'],
            ];
            $name = FresnsLanguages::where($input)->first();
            $v['lang_content'] = $name['lang_content'] ?? '';
            $descriptionArr[] = $v;
        }
        $permission_decode = json_decode($this->permission, true);
        $publish_post = $permission_decode['publish_post'] ?? 1;
        $publish_comment = $permission_decode['publish_comment'] ?? 1;

        // Default Field
        $default = [
            'id' => $this->id,
            'gid' => $this->uuid,
            'permission_decode' => json_decode($this->permission, true),
            'name' => $nameArr,
            'description' => $descriptionArr,
            'is_enable' => boolval($this->is_enable),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Merger
        $arr = array_merge($formMapFieldsArr, $default);

        return $arr;
    }
}
