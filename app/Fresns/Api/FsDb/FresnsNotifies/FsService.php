<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsNotifies;

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

    /**
     * Insert Notification.
     *
     * @param [type] $mark_id / Object: fsid or slug
     * @param [type] $user_id / Current User
     * @param [type] $source_type / 1.System 2.Follow 3.Like 4.Comment 5.Mention 6.Recommend
     * @param [type] $markTarget / Object: 1.User 2.Group 3.Hashtag 4.Post 5.Comment
     * @param [type] $source_brief / brief content
     * @param [type] $source_class / Types of triggered content: 1.Post 2.Comment
     * @param [type] $source_id / Source content id: post (posts > id) or comment (comments > id)
     * @return void
     */
    public static function markNotifies(
        $mark_id,
        $user_id,
        $source_type,
        $markTarget = null,
        $source_brief = null,
        $source_class = null,
        $source_id = null
    ) {
        // Produce only one notification for the same object (liking someone or following someone) in a day
        // Avoid frequent creation and cancellation operations that generate interference notifications
        // source_type: 2.Follow 3.Like
        $count = FresnsNotifies::where('user_id', $mark_id)->where('source_type', $source_type)->where('source_class',
            $source_class)->where('source_id', $source_id)->whereDate('created_at', date('Y-m-d', time()))->count();
        if ($count >= 1) {
            return false;
        }

        $input = [
            'user_id' => $mark_id,
            'source_type' => $source_type,
            'source_brief' => $source_brief,
            'source_id' => $source_id,
            'source_class' => $source_class,
            'source_user_id' => $user_id,
        ];
        FresnsNotifies::insert($input);
    }
}
