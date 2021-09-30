<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsMemberShields;

use App\Base\Services\BaseAdminService;
use Illuminate\Support\Facades\DB;

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

    // Add data to the member_shields table
    public static function addMemberShield($mid, $markTarget, $markId)
    {
        $input = [
            'member_id' => $mid,
            'shield_type' => $markTarget,
            'shield_id' => $markId,
        ];
        FresnsMemberShields::insert($input);
    }

    // Delete Shield Data
    public static function deleMemberShield($mid, $markTarget, $markId)
    {
        DB::table(FresnsMemberShieldsConfig::CFG_TABLE)->where('shield_type', $markTarget)->where('member_id', $mid)->where('shield_id', $markId)->update(['deleted_at' => date('Y-m-d H:i:s', time())]);
    }
}
