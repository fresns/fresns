<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsPanel;

use App\Base\Resources\BaseAdminResource;
use App\Http\Center\Helper\PluginHelper;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsDb\FresnsPlugins\FresnsPlugins as TweetPlugin;
use App\Http\FresnsDb\FresnsPlugins\FresnsPluginsConfig;

/**
 * List resource config handle.
 */
class FresnsPluginsResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Column
        $formMap = FresnsPluginsConfig::FORM_FIELDS_MAP;
        $formMapFieldsArr = [];
        foreach ($formMap as $k => $dbField) {
            $formMapFieldsArr[$dbField] = $this->$dbField;
        }
        // Plugin download or not
        $pluginConfig = PluginHelper::findPluginConfigClass($this->unikey);
        $isDownload = FresnsPluginsConfig::NO_DOWNLOAD;
        if ($pluginConfig) {
            if ($pluginConfig->uniKey == $this->unikey) {
                $isDownload = FresnsPluginsConfig::DOWNLOAD;
            }
        }
        // Is there a new version
        $isNewVision = FresnsPluginsConfig::NO_NEWVISION;
        $websitePc = '';
        $websiteMobile = '';
        $websitePcPlugin = '';
        $websiteMobilePlugin = '';

        // Website engine association template
        if ($this->type == 1) {
            $websitePc = ApiConfigHelper::getConfigByItemKey($this->unikey.'_Pc');
            $websitePcPlugin = TweetPlugin::where('unikey', $websitePc)->first();
            $websitePcPlugin = $websitePcPlugin['name'] ?? '';
            $websiteMobile = ApiConfigHelper::getConfigByItemKey($this->unikey.'_Mobile');
            $websiteMobilePlugin = TweetPlugin::where('unikey', $websiteMobile)->first();
            $websiteMobilePlugin = $websiteMobilePlugin['name'] ?? '';
        }

        // Default Column
        $default = [
            'key' => $this->id,
            'id' => $this->id,
            'is_enable' => boolval($this->is_enable),
            'disabled' => false,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'more_json' => $this->more_json,
            'more_json_decode' => json_decode($this->more_json, true),
            'isDownload' => $isDownload,
            'isNewVision' => $isNewVision,
            'websitePc' => $websitePc,
            'websiteMobile' => $websiteMobile,
            'websitePcPlugin' => $websitePcPlugin ?? '',
            'websiteMobilePlugin' => $websiteMobilePlugin ?? '',
        ];

        // Merger
        $arr = array_merge($formMapFieldsArr, $default);

        return $arr;
    }
}
