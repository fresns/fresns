<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsRoles;

use App\Fresns\Api\Base\Services\BaseAdminService;

class FsService extends BaseAdminService
{
    protected $needCommon = false;

    public function __construct()
    {
        $this->model = new FsModel();
        $this->resource = FsResource::class;
        $this->resourceDetail = FsResourceDetail::class;
    }

    // Get permission for map
    public static function getPermissionMap($permissionArr)
    {
        $permissionMap = [];
        foreach ($permissionArr as $v) {
            if (empty($v['permKey'])) {
                return [];
                break;
            }
            $permissionMap[$v['permKey']] = $v['permValue'];
        }

        return $permissionMap;
    }
}
