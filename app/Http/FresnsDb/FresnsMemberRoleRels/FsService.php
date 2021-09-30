<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsMemberRoleRels;

use App\Base\Services\BaseAdminService;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;

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
    public static function getMemberRoleRels($memberId)
    {
        $roleTime = date('Y-m-d H:i:s', time());
        $roleId = null;
        $roleRels = FresnsMemberRoleRels::where('member_id', $memberId)->where('type', 2)->first();
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
