<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Service;

use App\Helpers\InteractiveHelper;
use App\Models\Post;
use App\Models\User;

class PostService
{
    public function getPostDetail($postId, $langTag, $timezone)
    {
        $post = Post::withTrashed()->find($postId);
        $user = User::withTrashed()->find($post->user_id);

        $userProfile = $user->getUserProfile($timezone);
        $userMainRole = $user->getUserMainRole($timezone, $langTag);
        $userInteractive = InteractiveHelper::fresnsPostInteractive($langTag);

        $item['user'] = array_merge($userProfile, $userMainRole);
        $item['commentSetting'] = [];
        $item['icons'] = [];
        $item['location'] = [];
        $item['attachCount'] = [];
        $item['files'] = [];
        $item['extends'] = [];
        $item['group'] = [];

        $detail = array_merge($postInfo, $item, $userInteractive);

        return $detail;
    }
}
