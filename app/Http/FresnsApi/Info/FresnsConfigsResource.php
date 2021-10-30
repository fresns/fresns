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
            $plugin = FresnsPlugins::where('unikey', $itemValue)->first();
            if ($plugin) {
                if (! empty($plugin['plugin_domain'])) {
                    $domain = $plugin['plugin_domain'];
                } else {
                    $domain = ApiConfigHelper::getConfigByItemKey('backend_domain');
                }
                $value['unikey'] = $plugin['unikey'];
                $value['url'] = $domain.$plugin['access_path'];
                $itemValue = $value;
            }
        }

        // When item_type=plugins means it is a multi-select plugin, separated by English commas.
        if ($itemType == 'plugins') {
            $unikeyArr = explode(',', $itemValue);
            $pluginArr = FresnsPlugins::whereIn('unikey', $unikeyArr)->get([
                'unikey',
                'plugin_domain',
                'access_path',
            ])->toArray();
            if ($pluginArr) {
                $domain = ApiConfigHelper::getConfigByItemKey('backend_domain');
                $itArr = [];
                foreach ($pluginArr as $v) {
                    $it = [];
                    $it['unikey'] = $v['unikey'];
                    if (! empty($v['plugin_domain'])) {
                        $domain = $v['plugin_domain'];
                    }
                    $it['url'] = $domain.$v['access_path'] ?? '';
                    $itArr[] = $it;
                }
                $itemValue = $itArr;
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
