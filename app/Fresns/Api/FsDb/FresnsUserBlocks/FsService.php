<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsUserBlocks;

use App\Fresns\Api\Base\Services\BaseAdminService;
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

    // Add data to the user_blocks table
    public static function addUserBlock($uid, $markTarget, $markId)
    {
        $input = [
            'user_id' => $uid,
            'block_type' => $markTarget,
            'block_id' => $markId,
        ];
        FresnsUserBlocks::insert($input);
    }

    // Delete Block Data
    public static function deleUserBlock($uid, $markTarget, $markId)
    {
        DB::table(FresnsUserBlocksConfig::CFG_TABLE)->where('block_type', $markTarget)->where('user_id', $uid)->where('block_id', $markId)->update(['deleted_at' => date('Y-m-d H:i:s', time())]);
    }
}
