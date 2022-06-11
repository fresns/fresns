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
use App\Models\PluginBadge;

trait PluginUsageServiceTrait
{
    public function getUsageInfo(?string $langTag = null, ?int $userId = null)
    {
        $usageData = $this;

        $info['plugin'] = $usageData->plugin_unikey;
        $info['name'] = LanguageHelper::fresnsLanguageByTableId('plugin_usages', 'name', $usageData->id, $langTag);
        $info['icon'] = FileHelper::fresnsFileUrlByTableColumn($usageData->icon_file_id, $usageData->icon_file_url);
        $info['url'] = PluginHelper::fresnsPluginUsageUrl($usageData->plugin_unikey, $usageData->id);

        $info['badgesType'] = null;
        $info['badgesValue'] = null;
        $info['editorNumber'] = $usageData->editor_number;

        if (! empty($userId)) {
            $badge = PluginBadge::where('plugin_unikey', $usageData->plugin_unikey)->where('user_id', $userId)->first();
            $info['badgesType'] = $badge->display_type;
            $info['badgesValue'] = match ($badge->display_type) {
                default => null,
                1 => $badge->value_number,
                2 => $badge->value_text,
            };
        }

        $pluginRating['postByAll'] = PluginHelper::pluginRatingHandle('postByAll', $usageData->data_sources, $langTag);
        $pluginRating['postByFollow'] = PluginHelper::pluginRatingHandle('postByFollow', $usageData->data_sources, $langTag);
        $pluginRating['postByNearby'] = PluginHelper::pluginRatingHandle('postByNearby', $usageData->data_sources, $langTag);
        $info['pluginRating'] = $pluginRating;

        return $info;
    }

    public function getIconUrl()
    {
        return FileHelper::fresnsFileUrlByTableColumn($this->icon_file_id, $this->icon_file_url);
    }
}
