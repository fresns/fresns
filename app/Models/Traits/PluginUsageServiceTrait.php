<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;

trait PluginUsageServiceTrait
{
    public function getUsageInfo(?string $langTag = null)
    {
        $usageData = $this;

        $info['unikey'] = $usageData->plugin_unikey;
        $info['name'] = LanguageHelper::fresnsLanguageByTableId('plugin_usages', 'name', $usageData->id, $langTag) ??  $usageData->name;
        $info['icon'] = FileHelper::fresnsFileUrlByTableColumn($usageData->icon_file_id, $usageData->icon_file_url);
        $info['url'] = PluginHelper::fresnsPluginUsageUrl($usageData->plugin_unikey, $usageData->parameter);

        $info['badgeType'] = null;
        $info['badgeValue'] = null;

        $info['editorToolbar'] = (bool) $usageData->editor_toolbar;
        $info['editorNumber'] = $usageData->editor_number;

        $pluginRating['postByAll'] = PluginHelper::pluginDataRatingHandle('postByAll', $usageData->data_sources, $langTag);
        $pluginRating['postByFollow'] = PluginHelper::pluginDataRatingHandle('postByFollow', $usageData->data_sources, $langTag);
        $pluginRating['postByNearby'] = PluginHelper::pluginDataRatingHandle('postByNearby', $usageData->data_sources, $langTag);
        $pluginRating['commentByAll'] = PluginHelper::pluginDataRatingHandle('commentByAll', $usageData->data_sources, $langTag);
        $pluginRating['commentByFollow'] = PluginHelper::pluginDataRatingHandle('commentByFollow', $usageData->data_sources, $langTag);
        $pluginRating['commentByNearby'] = PluginHelper::pluginDataRatingHandle('commentByNearby', $usageData->data_sources, $langTag);
        $info['pluginRating'] = $pluginRating;

        return $info;
    }

    public function getIconUrl()
    {
        return FileHelper::fresnsFileUrlByTableColumn($this->icon_file_id, $this->icon_file_url);
    }
}
