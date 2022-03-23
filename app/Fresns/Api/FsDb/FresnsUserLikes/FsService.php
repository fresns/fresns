<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsUserLikes;

use App\Fresns\Api\Base\Services\BaseAdminService;
use App\Fresns\Api\FsDb\FresnsUserStats\FresnsUserStatsConfig;
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

    // Add data to the user_likes table
    public static function addUserLike($user_id, $mark_target, $mark_id, $user_count = null, $me_count = null)
    {
        $input = [
            'user_id' => $user_id,
            'like_type' => $mark_target,
            'like_id' => $mark_id,
        ];

        FresnsUserLikes::insert($input);
        if ($user_count) {
            DB::table(FresnsUserStatsConfig::CFG_TABLE)->where('user_id', $user_id)->increment($user_count);
        }
        if ($me_count) {
            DB::table(FresnsUserStatsConfig::CFG_TABLE)->where('user_id', $mark_id)->increment($me_count);
        }
    }

    // Delete Like Data
    public static function deleUserLike($user_id, $mark_target, $mark_id)
    {
        DB::table(FresnsUserLikesConfig::CFG_TABLE)->where('like_type', $mark_target)->where('user_id', $user_id)->where('like_id', $mark_id)->update(['deleted_at' => date('Y-m-d H:i:s', time())]);
    }
}
