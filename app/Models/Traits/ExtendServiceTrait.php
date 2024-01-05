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
use App\Models\Extend;

trait ExtendServiceTrait
{
    public function getExtendText(): array
    {
        $extendData = $this;

        $content = $extendData->content;

        $info['eid'] = $extendData->eid;
        $info['type'] = $extendData->view_type;
        $info['typeString'] = StrHelper::extendViewTypeString(Extend::TYPE_TEXT, $extendData->view_type);
        $info['content'] = $content['content'] ?? null;
        $info['isMarkdown'] = (bool) $content['isMarkdown'] ?? false;
        $info['position'] = $extendData->position;
        $info['appUrl'] = PluginHelper::fresnsPluginUsageUrl($extendData->app_fskey, $extendData->url_parameter);

        return $info;
    }

    public function getExtendInfo(?string $langTag = null): array
    {
        $extendData = $this;

        $content = $extendData->content;

        $info['eid'] = $extendData->eid;
        $info['type'] = $extendData->view_type;
        $info['typeString'] = StrHelper::extendViewTypeString(Extend::TYPE_INFO, $extendData->view_type);
        $info['cover'] = FileHelper::fresnsFileUrlByTableColumn($extendData->cover_file_id, $extendData->cover_file_url);
        $info['title'] = StrHelper::languageContent($content['title'] ?? null, $langTag);
        $info['titleColor'] = $content['titleColor'] ?? null;
        $info['descPrimary'] = StrHelper::languageContent($content['descPrimary'] ?? null, $langTag);
        $info['descPrimaryColor'] = $content['descPrimaryColor'] ?? null;
        $info['descSecondary'] = StrHelper::languageContent($content['descSecondary'] ?? null, $langTag);
        $info['descSecondaryColor'] = $content['descSecondaryColor'] ?? null;
        $info['buttonName'] = StrHelper::languageContent($content['buttonName'] ?? null, $langTag);
        $info['buttonColor'] = $content['buttonColor'] ?? null;
        $info['position'] = $extendData->position;
        $info['appUrl'] = PluginHelper::fresnsPluginUsageUrl($extendData->app_fskey, $extendData->url_parameter);

        return $info;
    }

    public function getExtendAction(?string $langTag = null): array
    {
        $extendData = $this;

        $content = $extendData->content;
        $actionItems = $extendData->action_items;

        $info['eid'] = $extendData->eid;
        $info['type'] = $extendData->view_type;
        $info['typeString'] = StrHelper::extendViewTypeString(Extend::TYPE_ACTION, $extendData->view_type);
        $info['title'] = StrHelper::languageContent($content['title'] ?? null, $langTag);
        $info['titleColor'] = $content['titleColor'] ?? null;
        $info['position'] = $extendData->position;
        $info['endDateTime'] = $extendData->ended_at;
        $info['status'] = $extendData->position;
        $info['hasOperated'] = false;
        $info['items'] = $actionItems;

        return $info;
    }
}
