<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Notification extends Model
{
    const TYPE_SYSTEM = 1;
    const TYPE_RECOMMEND = 2;
    const TYPE_LIKE = 3;
    const TYPE_DISLIKE = 4;
    const TYPE_FOLLOW = 5;
    const TYPE_BLOCK = 6;
    const TYPE_MENTION = 7;
    const TYPE_COMMENT = 8;

    const ACTION_TYPE_LIKE = 1;
    const ACTION_TYPE_DISLIKE = 2;
    const ACTION_TYPE_FOLLOW = 3;
    const ACTION_TYPE_BLOCK = 4;
    const ACTION_TYPE_PUBLISH = 5;
    const ACTION_TYPE_EDIT = 6;
    const ACTION_TYPE_DELETE = 7;
    const ACTION_TYPE_STICKY = 8;
    const ACTION_TYPE_DIGEST = 9;
    const ACTION_TYPE_MANAGE = 10;

    const ACTION_OBJECT_USER = 1;
    const ACTION_OBJECT_GROUP = 2;
    const ACTION_OBJECT_HASHTAG = 3;
    const ACTION_OBJECT_POST = 4;
    const ACTION_OBJECT_COMMENT = 5;
    const ACTION_OBJECT_POST_LOG = 6;
    const ACTION_OBJECT_COMMENT_LOG = 7;
    const ACTION_OBJECT_EXTEND = 8;

    public function scopeType($query, int $type)
    {
        return $query->where('type', $type);
    }

    public function actionUser()
    {
        return $this->belongsTo(User::class, 'action_user_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'action_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'action_id', 'id');
    }

    public function hashtag()
    {
        return $this->belongsTo(Hashtag::class, 'action_id', 'id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'action_id', 'id');
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'action_id', 'id');
    }

    public function postLog()
    {
        return $this->belongsTo(PostLog::class, 'action_id', 'id');
    }

    public function commentLog()
    {
        return $this->belongsTo(CommentLog::class, 'action_id', 'id');
    }

    public function extend()
    {
        return $this->belongsTo(Extend::class, 'action_id', 'id');
    }
}
