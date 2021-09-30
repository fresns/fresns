<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsNotifies;

use App\Base\Services\BaseAdminService;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollowsConfig;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikesConfig;
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

    /**
     * Insert Notification.
     *
     * @param [type] $mark_id / Object: uuid or slug
     * @param [type] $member_id / Current Member
     * @param [type] $source_type / 1.System 2.Follow 3.Like 4.Comment 5.Mention 6.Recommend
     * @param [type] $markTarget / Object: 1.Member 2.Group 3.Hashtag 4.Post 5.Comment
     * @param [type] $source_brief / brief content
     * @param [type] $source_class / Types of triggered content: 1.Post 2.Comment
     * @param [type] $source_id / Source content id: post (posts > id) or comment (comments > id)
     * @return void
     */
    public static function markNotifies(
        $mark_id,
        $member_id,
        $source_type,
        $markTarget = null,
        $source_brief = null,
        $source_class = null,
        $source_id = null
    ) {
        // Produce only one notification for the same object (liking someone or following someone) in a day
        // Avoid frequent creation and cancellation operations that generate interference notifications
        // source_type: 2.Follow 3.Like
        $count = FresnsNotifies::where('member_id', $mark_id)->where('source_type', $source_type)->where('source_class',
            $source_class)->where('source_id', $source_id)->whereDate('created_at', date('Y-m-d', time()))->count();
        if ($count >= 1) {
            return false;
        }

        $input = [
            'member_id' => $mark_id,
            'source_type' => $source_type,
            'source_brief' => $source_brief,
            'source_id' => $source_id,
            'source_class' => $source_class,
            'source_member_id' => $member_id,
        ];
        FresnsNotifies::insert($input);
    }
}
