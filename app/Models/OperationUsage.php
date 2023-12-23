<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class OperationUsage extends Model
{
    const TYPE_USER = 1;
    const TYPE_GROUP = 2;
    const TYPE_HASHTAG = 3;
    const TYPE_GEOTAG = 4;
    const TYPE_POST = 5;
    const TYPE_COMMENT = 6;
    const TYPE_POST_LOG = 7;
    const TYPE_COMMENT_LOG = 8;

    public function scopeType($query, int $type)
    {
        return $query->where('usage_type', $type);
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class, 'operation_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'usage_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'usage_id', 'id');
    }

    public function hashtag()
    {
        return $this->belongsTo(Hashtag::class, 'usage_id', 'id');
    }

    public function geotag()
    {
        return $this->belongsTo(Geotag::class, 'usage_id', 'id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'usage_id', 'id');
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'usage_id', 'id');
    }
}
