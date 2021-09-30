<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsPanel;

use App\Base\Resources\BaseAdminResource;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigs;
use App\Http\FresnsDb\FresnsSessionKeys\FresnsSessionKeysConfig;

/**
 * List resource config handle.
 */
class FresnsKeysResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Column
        $formMap = FresnsSessionKeysConfig::FORM_FIELDS_MAP;
        $formMapFieldsArr = [];
        foreach ($formMap as $k => $dbField) {
            $formMapFieldsArr[$dbField] = $this->$dbField;
        }
        $platforms = FresnsConfigs::where('item_key', 'platforms')->first(['item_value']);
        // Platform Config Data
        $platforms = json_decode($platforms['item_value'], true);
        $platformName = '';
        foreach ($platforms as $p) {
            if ($this->platform_id == $p['id']) {
                $platformName = $p['name'];
            }
        }
        $typeName = $this->type == 1 ? 'Fresns API' : 'Plugin API';

        // Default Column
        $default = [
            'key' => $this->id,
            'id' => $this->id,
            'platformName' => $platformName,
            'typeName' => $typeName,
            'more_json' => $this->more_json,
            'more_json_decode' => json_decode($this->more_json, true),
            'disabled' => false,
            'is_enable' => boolval($this->is_enable),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Merger
        $arr = array_merge($formMapFieldsArr, $default);

        return $arr;
    }
}
