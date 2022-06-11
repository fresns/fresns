<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class HashtagLinked extends Model
{
    const TYPE_USER = 1;
    const TYPE_GROUP = 2;
    const TYPE_HASHTAG = 3;
    const TYPE_POST = 4;
    const TYPE_COMMENT = 5;

    public function scopeType($query, int $type)
    {
        return $query->where('linked_type', $type);
    }

    public function hashtagInfo()
    {
        return $this->belongsTo(Hashtag::class, 'hashtag_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'linked_id', 'id')->where('linked_type', HashtagLinked::TYPE_USER);
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'linked_id', 'id')->where('linked_type', HashtagLinked::TYPE_GROUP);
    }

    public function hashtag()
    {
        return $this->belongsTo(Hashtag::class, 'linked_id', 'id')->where('linked_type', HashtagLinked::TYPE_HASHTAG);
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'linked_id', 'id')->where('linked_type', HashtagLinked::TYPE_POST);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'linked_id', 'id')->where('linked_type', HashtagLinked::TYPE_COMMENT);
    }
}
