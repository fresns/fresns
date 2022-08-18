<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class UserBlock extends Model
{
    const TYPE_USER = 1;
    const TYPE_GROUP = 2;
    const TYPE_HASHTAG = 3;
    const TYPE_POST = 4;
    const TYPE_COMMENT = 5;

    public function scopeType($query, int $type)
    {
        return $query->where('block_type', $type);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'block_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'block_id', 'id');
    }

    public function hashtag()
    {
        return $this->belongsTo(Hashtag::class, 'block_id', 'id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'block_id', 'id');
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'block_id', 'id');
    }
}
