<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsUserFollows;

use App\Fresns\Api\Base\Services\BaseAdminService;

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

    // Add data to the user_follows table
    public static function addUserFollow($uid, $markTarget, $markId)
    {
        $input = [
            'user_id' => $uid,
            'follow_type' => $markTarget,
            'follow_id' => $markId,
        ];
        FresnsUserFollows::insert($input);
    }

    // Delete Follow Data
    public static function deleUserFollow($uid, $markTarget, $markId)
    {
        FresnsUserFollows::where('user_id', $uid)->where('follow_type', $markTarget)->where('follow_id', $markId)->delete();
    }
}
