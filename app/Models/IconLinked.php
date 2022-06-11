<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class IconLinked extends Model
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

    public function iconInfo()
    {
        return $this->belongsTo(Icon::class, 'icon_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'linked_id', 'id')->where('linked_type', IconLinked::TYPE_USER);
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'linked_id', 'id')->where('linked_type', IconLinked::TYPE_GROUP);
    }

    public function hashtag()
    {
        return $this->belongsTo(Hashtag::class, 'linked_id', 'id')->where('linked_type', IconLinked::TYPE_HASHTAG);
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'linked_id', 'id')->where('linked_type', IconLinked::TYPE_POST);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'linked_id', 'id')->where('linked_type', IconLinked::TYPE_COMMENT);
    }
}
