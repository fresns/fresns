<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsUserRoles;

use App\Fresns\Api\Base\Services\BaseAdminService;
use App\Fresns\Api\Helpers\ApiConfigHelper;

class FsService extends BaseAdminService
{
    public function __construct()
    {
        $this->model = new FsModel();
        $this->resource = FsResource::class;
        $this->resourceDetail = FsResourceDetail::class;
    }

    public function common()
    {
        $common = parent::common();

        return $common;
    }

    // get roleId
    public static function getUserRoles($userId)
    {
        $roleTime = date('Y-m-d H:i:s', time());
        $roleId = null;
        $roleRels = FresnsUserRoles::where('user_id', $userId)->where('is_main', 1)->first();
        if (empty($roleRels)) {
            return $roleId;
        }
        if (! empty($roleRels['expired_at'])) {
            if ($roleTime > $roleRels['expired_at']) {
                if (empty($roleRels['restore_role_id'])) {
                    $roleId = ApiConfigHelper::getConfigByItemKey('default_role');
                } else {
                    $roleId = $roleRels['restore_role_id'];
                }
            } else {
                $roleId = $roleRels['role_id'];
            }
        } else {
            $roleId = $roleRels['role_id'];
        }

        return $roleId;
    }
}
