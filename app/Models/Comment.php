<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Comment extends Model
{
    use Traits\CommentServiceTrait;
    use Traits\IsEnableTrait;

    public function commentAppend()
    {
        return $this->hasOne(CommentAppend::class);
    }

    public function commentLogs()
    {
        return $this->hasMany(CommentLog::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    public function postAppend()
    {
        return $this->belongsTo(PostAppend::class, 'post_id', 'post_id');
    }

    public function comments()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function parentComment()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'hashtag_linkeds', 'hashtag_id', 'linked_id')->wherePivot('linked_type', HashtagLinked::TYPE_COMMENT);
    }
}
