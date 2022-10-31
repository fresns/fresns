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
use App\Helpers\StrHelper;

trait ExtendServiceTrait
{
    public function getExtendInfo(?string $langTag = null)
    {
        $extendData = $this;

        $info['eid'] = $extendData->eid;
        $info['type'] = $extendData->type;
        $info['textContent'] = $extendData->text_content;
        $info['textIsMarkdown'] = (bool) $extendData->text_is_markdown;
        $info['infoType'] = $extendData->info_type;
        $info['infoTypeString'] = StrHelper::infoTypeString($extendData->info_type);
        $info['cover'] = FileHelper::fresnsFileUrlByTableColumn($extendData->cover_file_id, $extendData->cover_file_url);
        $info['title'] = LanguageHelper::fresnsLanguageByTableId('extends', 'title', $extendData->id, $langTag) ?? $extendData->title;
        $info['titleColor'] = $extendData->title_color;
        $info['descPrimary'] = LanguageHelper::fresnsLanguageByTableId('extends', 'desc_primary', $extendData->id, $langTag) ?? $extendData->desc_primary;
        $info['descPrimaryColor'] = $extendData->desc_primary_color;
        $info['descSecondary'] = LanguageHelper::fresnsLanguageByTableId('extends', 'desc_secondary', $extendData->id, $langTag) ?? $extendData->desc_secondary;
        $info['descSecondaryColor'] = $extendData->desc_secondary_color;
        $info['buttonName'] = LanguageHelper::fresnsLanguageByTableId('extends', 'button_name', $extendData->id, $langTag) ?? $extendData->button_name;
        $info['buttonColor'] = $extendData->button_color;
        $info['position'] = $extendData->position;
        $info['accessUrl'] = PluginHelper::fresnsPluginUsageUrl($extendData->plugin_unikey, $extendData->parameter);
        $info['moreJson'] = $extendData->more_json;

        return $info;
    }
}
