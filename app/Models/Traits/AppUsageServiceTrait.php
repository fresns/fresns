<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\FileHelper;
use App\Helpers\PluginHelper;
use App\Helpers\StrHelper;

trait AppUsageServiceTrait
{
    public function getIconUrl(): ?string
    {
        return FileHelper::fresnsFileUrlByTableColumn($this->icon_file_id, $this->icon_file_url);
    }

    public function getUsageInfo(?string $langTag = null): array
    {
        $usageData = $this;

        $info['fskey'] = $usageData->app_fskey;
        $info['icon'] = FileHelper::fresnsFileUrlByTableColumn($usageData->icon_file_id, $usageData->icon_file_url);
        $info['name'] = StrHelper::languageContent($usageData->name, $langTag);
        $info['appUrl'] = PluginHelper::fresnsPluginUsageUrl($usageData->app_fskey, $usageData->parameter);

        $info['isInToolbar'] = (bool) $usageData->editor_toolbar;

        return $info;
    }
}
