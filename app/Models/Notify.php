<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Notify extends Model
{
    const TYPE_SYSTEM = 1;
    const TYPE_RECOMMEND = 2;
    const TYPE_LIKE = 3;
    const TYPE_FOLLOW = 4;
    const TYPE_MENTION = 5;
    const TYPE_COMMENT = 6;

    const ACTION_TYPE_USER = 1;
    const ACTION_TYPE_GROUP = 2;
    const ACTION_TYPE_HASHTAG = 3;
    const ACTION_TYPE_POST = 4;
    const ACTION_TYPE_COMMENT = 5;

    public function actionUser()
    {
        return $this->belongsTo(User::class, 'action_user_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'action_id', 'id')->where('action_type', Notify::ACTION_TYPE_USER);
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'action_id', 'id')->where('action_type', Notify::ACTION_TYPE_GROUP);
    }

    public function hashtag()
    {
        return $this->belongsTo(Hashtag::class, 'action_id', 'id')->where('action_type', Notify::ACTION_TYPE_HASHTAG);
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'action_id', 'id')->where('action_type', Notify::ACTION_TYPE_POST);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'action_id', 'id')->where('action_type', Notify::ACTION_TYPE_POST);
    }
}
