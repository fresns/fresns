<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Info;

use App\Base\Resources\BaseAdminResource;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsDb\FresnsEmojis\FresnsEmojis;
use App\Http\FresnsDb\FresnsEmojis\FresnsEmojisConfig;

/**
 * List resource config handle.
 */
class FresnsEmojisResource extends BaseAdminResource
{
    public function toArray($request)
    {
        $emojisArr = FresnsEmojis::where('is_enable', 1)->where('parent_id', $this->id)->get([
            'code',
            'image_file_url',
            'name',
            'image_file_id',
        ])->toArray();
        $itemArr = [];
        foreach ($emojisArr as $v) {
            $item = [];
            $item['code'] = $v['code'];
            // $item['name'] = $v['name'];
            $item['image'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['image_file_id'], $v['image_file_url']);
            $itemArr[] = $item;
        }

        // Default Field
        $default = [
            'name' => ApiLanguageHelper::getLanguagesByTableId(FresnsEmojisConfig::CFG_TABLE, 'name', $this->id),
            'image' => ApiFileHelper::getImageSignUrlByFileIdUrl($this->image_file_id, $this->image_file_url),
            'count' => count($itemArr),
            'emoji' => $itemArr,
        ];

        return $default;
    }
}
