<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsMemberRoles;

use App\Base\Resources\BaseAdminResource;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguagesService;

/**
 * List resource config handle.
 */
class FsResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // Default Field
        $arr = [
            'rid' => $this->id,
            'type' => $this->type,
            'name' => FresnsLanguagesService::getLanguageByTableId(FresnsMemberRolesConfig::CFG_TABLE, 'name', $this->id),
            'icon' => ApiFileHelper::getImageSignUrlByFileIdUrl($this->icon_file_id, $this->icon_file_url),
            'nicknameColor' => $this->nickname_color,
            'permission' => json_decode($this->permission, true),
        ];

        return $arr;
    }
}
