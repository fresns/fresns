<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsRoles;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Fresns\Api\FsDb\FresnsLanguages\FresnsLanguagesService;

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
            'name' => FresnsLanguagesService::getLanguageByTableId(FresnsRolesConfig::CFG_TABLE, 'name', $this->id),
            'icon' => ApiFileHelper::getImageSignUrlByFileIdUrl($this->icon_file_id, $this->icon_file_url),
            'nicknameColor' => $this->nickname_color,
            'permission' => json_decode($this->permission, true),
        ];

        return $arr;
    }
}
