<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class UserLike extends Model
{
    const MARK_TYPE_LIKE = 1;
    const MARK_TYPE_DISLIKE = 2;

    const TYPE_USER = 1;
    const TYPE_GROUP = 2;
    const TYPE_HASHTAG = 3;
    const TYPE_POST = 4;
    const TYPE_COMMENT = 5;

    public function scopeMarkType($query, int $type)
    {
        return $query->where('mark_type', $type);
    }

    public function scopeType($query, int $type)
    {
        return $query->where('like_type', $type);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'like_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'like_id', 'id');
    }

    public function hashtag()
    {
        return $this->belongsTo(Hashtag::class, 'like_id', 'id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'like_id', 'id');
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'like_id', 'id');
    }
}
