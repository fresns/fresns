<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Info;

use App\Base\Resources\BaseAdminResource;
use App\Helpers\StrHelper;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguagesService;
use App\Http\FresnsDb\FresnsPlugins\FresnsPlugins;
use App\Http\FresnsDb\FresnsPlugins\FresnsPluginsService;

/**
 * List resource config handle.
 */
class FresnsConfigsResource extends BaseAdminResource
{
    public function toArray($request)
    {
        $itemKey = $this->item_key;
        $itemValue = $this->item_value;
        $isJson = StrHelper::isJson($itemValue);
        if ($isJson == true) {
            $itemValue = json_decode($itemValue, true);
        }
        $itemValue = $itemValue;
        $itemTag = $this->item_tag;
        $itemType = $this->item_type;
        $itemStatus = boolval($this->is_enable);
        $isMultilingual = $this->is_multilingual;

        $langTag = ApiLanguageHelper::getLangTagByHeader();

        // When is_multilingual=1 means that the key is multilingual
        if ($isMultilingual == 1) {
            $itemValue = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', $itemKey, $langTag);
        }

        if ($itemType == 'boolean') {
            if (is_string($itemValue)) {
                $itemValue = boolval($itemValue);
            }
        }

        if ($itemType == 'number') {
            if (is_numeric($itemValue)) {
                $itemValue = intval($itemValue);
            }
        }

        // When item_type=file if the key value starts with http:// or https://, it is output as is without special handling.
        // If it is a number, it is the file ID, with which the file URL is output, or if the file type has anti-blocking enabled, the URL output is requested from the plugin.
        if ($itemType == 'file') {
            if (is_numeric($itemValue)) {
                $item['itemValue'] = ApiFileHelper::getImageSignUrlByFileId($itemValue);
            }
        }

        // When item_type=plugin means it is a plugin unikey value, the plugin URL is output with unikey.
        if ($itemType == 'plugin') {
            $itemValue = FresnsPluginsService::getPluginUrlByUnikey($itemValue);
        }

        // When item_type=plugins means it is a multi-select plugin, separated by English commas.
        if ($itemType == 'plugins') {
            if ($itemValue) {
                foreach ($itemValue as $plugin) {
                    $item = [];
                    $item['code'] = $plugin['code'];
                    $item['url'] = FresnsPluginsService::getPluginUrlByUnikey($plugin['unikey']);
                    $itemArr[] = $item;
                }
                $itemValue = $itemArr;
            }
        }

        // Default Field
        $default = [
            'itemKey' => $itemKey,
            'itemValue' => $itemValue,
            'itemTag' => $itemTag,
            'itemType' => $itemType,
            'itemStatus' => $itemStatus,
        ];

        return $default;
    }
}
