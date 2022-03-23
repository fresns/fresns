<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Info;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\FsDb\FresnsStickers\FresnsStickers;
use App\Fresns\Api\FsDb\FresnsStickers\FresnsStickersConfig;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Fresns\Api\Helpers\ApiLanguageHelper;

/**
 * List resource config handle.
 */
class FresnsStickersResource extends BaseAdminResource
{
    public function toArray($request)
    {
        $stickersArr = FresnsStickers::where('is_enable', 1)->where('parent_id', $this->id)->get([
            'code',
            'image_file_id',
            'image_file_url',
            'name',
        ])->toArray();
        $itemArr = [];
        foreach ($stickersArr as $v) {
            $item = [];
            $item['code'] = $v['code'];
            // $item['name'] = $v['name'];
            $item['image'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['image_file_id'], $v['image_file_url']);
            $itemArr[] = $item;
        }

        // Default Field
        $default = [
            'name' => ApiLanguageHelper::getLanguagesByTableId(FresnsStickersConfig::CFG_TABLE, 'name', $this->id),
            'image' => ApiFileHelper::getImageSignUrlByFileIdUrl($this->image_file_id, $this->image_file_url),
            'count' => count($itemArr),
            'sticker' => $itemArr,
        ];

        return $default;
    }
}
