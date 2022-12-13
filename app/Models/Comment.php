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
    use Traits\FsidTrait;

    protected $dates = [
        'latest_edit_at',
        'latest_comment_at',
    ];

    public function getFsidKey()
    {
        return 'cid';
    }

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
        return $this->belongsToMany(Hashtag::class, 'hashtag_usages', 'usage_id', 'hashtag_id')->wherePivot('usage_type', HashtagUsage::TYPE_COMMENT)->wherePivot('deleted_at', null);
    }

    public function fileUsages()
    {
        return $this->hasMany(FileUsage::class, 'table_id', 'id')->where('table_name', 'comments')->where('table_column', 'id');
    }

    public function extendUsages()
    {
        return $this->hasMany(ExtendUsage::class, 'usage_id', 'id')->where('usage_type', ExtendUsage::TYPE_COMMENT);
    }
}
