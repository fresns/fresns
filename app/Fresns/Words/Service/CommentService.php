<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Service;

use App\Helpers\InteractiveHelper;
use App\Models\Comment;
use App\Models\User;

class CommentService
{
    public function getCommentDetail($commentId, $langTag, $timezone)
    {
        $comment = Comment::withTrashed()->find($commentId);
        $user = User::withTrashed()->find($comment->user_id);

        $userProfile = $user->getUserProfile($timezone);
        $userMainRole = $user->getUserMainRole($timezone, $langTag);
        $userInteractive = InteractiveHelper::fresnsPostInteractive($langTag);

        $item['user'] = array_merge($userProfile, $userMainRole);
        $item['commentPreviews'] = [];
        $item['commentBtn'] = [];
        $item['replyTo'] = [];
        $item['icons'] = [];
        $item['location'] = [];
        $item['attachCount'] = [];
        $item['files'] = [];
        $item['extends'] = [];
        $item['atPost'] = [];

        $detail = array_merge($commentInfo, $item, $userInteractive);

        return $detail;
    }
}
