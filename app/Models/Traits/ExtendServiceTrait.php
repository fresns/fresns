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
    public function getExtendInfo(?string $langTag = null): array
    {
        $extendData = $this;

        $content = $extendData->content;

        $item['eid'] = $extendData->eid;
        $item['type'] = $extendData->view_type;
        $item['typeString'] = StrHelper::extendViewTypeString($extendData->view_type);
        $item['image'] = FileHelper::fresnsFileUrlByTableColumn($extendData->image_file_id, $extendData->image_file_url);
        switch ($extendData->type) {
            case Extend::TYPE_TEXT:
                $item['content'] = $content['content'] ?? null;
                $item['isMarkdown'] = $content['isMarkdown'] ?? false;
                break;

            case Extend::TYPE_INFO:
                $item['title'] = StrHelper::languageContent($content['title'] ?? null, $langTag);
                $item['titleColor'] = $content['titleColor'] ?? null;
                $item['descPrimary'] = StrHelper::languageContent($content['descPrimary'] ?? null, $langTag);
                $item['descPrimaryColor'] = $content['descPrimaryColor'] ?? null;
                $item['descSecondary'] = StrHelper::languageContent($content['descSecondary'] ?? null, $langTag);
                $item['descSecondaryColor'] = $content['descSecondaryColor'] ?? null;
                $item['buttonName'] = StrHelper::languageContent($content['buttonName'] ?? null, $langTag);
                $item['buttonColor'] = $content['buttonColor'] ?? null;
                break;

            case Extend::TYPE_ACTION:
                $actionItems = $extendData->action_items;

                $actionItemArr = [];
                foreach ($actionItems as $actionItem) {
                    if (empty($actionItem['key'] ?? null)) {
                        continue;
                    }

                    $ai['name'] = StrHelper::languageContent($actionItem['name'] ?? null, $langTag);
                    $ai['key'] = $actionItem['key'];
                    $ai['value'] = $actionItem['value'] ?? null;
                    $ai['hasOperated'] = false;

                    $actionItemArr[] = $ai;
                }

                $item['title'] = StrHelper::languageContent($content['title'] ?? null, $langTag);
                $item['titleColor'] = $content['titleColor'] ?? null;
                $item['endDateTime'] = $extendData->ended_at;
                $item['status'] = $extendData->ended_at->isPast();
                $item['actionUserCount'] = 0;
                $item['hasOperated'] = false;
                $item['items'] = $actionItemArr;
                break;
        }
        $item['position'] = $extendData->position;
        $item['appUrl'] = PluginHelper::fresnsPluginUsageUrl($extendData->app_fskey, $extendData->url_parameter);

        return $item;
    }
}
