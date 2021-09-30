<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsMemberFollows;

use App\Base\Services\BaseAdminService;

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

    // Add data to the member_follows table
    public static function addMemberFollow($mid, $markTarget, $markId)
    {
        $input = [
            'member_id' => $mid,
            'follow_type' => $markTarget,
            'follow_id' => $markId,
        ];
        FresnsMemberFollows::insert($input);
    }

    // Delete Follow Data
    public static function deleMemberFollow($mid, $markTarget, $markId)
    {
        FresnsMemberFollows::where('member_id', $mid)->where('follow_type', $markTarget)->where('follow_id', $markId)->delete();
    }
}
