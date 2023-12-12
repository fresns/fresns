<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;

trait PluginUsageServiceTrait
{
    public function getUsageInfo(?string $langTag = null): array
    {
        $usageData = $this;

        $info['fskey'] = $usageData->plugin_fskey;
        $info['name'] = LanguageHelper::fresnsLanguageByTableId('plugin_usages', 'name', $usageData->id, $langTag) ?? $usageData->name;
        $info['icon'] = FileHelper::fresnsFileUrlByTableColumn($usageData->icon_file_id, $usageData->icon_file_url);
        $info['url'] = PluginHelper::fresnsPluginUsageUrl($usageData->plugin_fskey, $usageData->parameter);

        $info['badgeType'] = null;
        $info['badgeValue'] = null;

        $info['editorToolbar'] = (bool) $usageData->editor_toolbar;
        $info['editorNumber'] = $usageData->editor_number;

        return $info;
    }

    public function getIconUrl(): ?string
    {
        return FileHelper::fresnsFileUrlByTableColumn($this->icon_file_id, $this->icon_file_url);
    }
}
