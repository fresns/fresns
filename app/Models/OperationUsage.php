<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class OperationUsage extends Model
{
    const TYPE_USER = 1;
    const TYPE_GROUP = 2;
    const TYPE_HASHTAG = 3;
    const TYPE_POST = 4;
    const TYPE_COMMENT = 5;
    const TYPE_POST_LOG = 6;
    const TYPE_COMMENT_LOG = 7;

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
        return $this->belongsTo(User::class, 'usage_id', 'id')->where('usage_type', OperationUsage::TYPE_USER);
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'usage_id', 'id')->where('usage_type', OperationUsage::TYPE_GROUP);
    }

    public function hashtag()
    {
        return $this->belongsTo(Hashtag::class, 'usage_id', 'id')->where('usage_type', OperationUsage::TYPE_HASHTAG);
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'usage_id', 'id')->where('usage_type', OperationUsage::TYPE_POST);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'usage_id', 'id')->where('usage_type', OperationUsage::TYPE_COMMENT);
    }
}
