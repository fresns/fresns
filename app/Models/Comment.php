<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Comment extends Model
{
    use Traits\FsidTrait;
    use Traits\IsEnabledTrait;
    use Traits\CommentServiceTrait;

    const PRIVACY_PUBLIC = 1;
    const PRIVACY_PRIVATE = 2;
    const PRIVACY_PRIVATE_BY_POST = 3;

    const DIGEST_NO = 1;
    const DIGEST_GENERAL = 2;
    const DIGEST_PREMIUM = 3;

    const STICKY_NO = 0;
    const STICKY_YES = 1;

    protected $casts = [
        'more_info' => 'json',
        'permissions' => 'json',
    ];

    protected $dates = [
        'last_edit_at',
        'last_comment_at',
    ];

    public function getFsidKey()
    {
        return 'cid';
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function fileUsages()
    {
        return $this->hasMany(FileUsage::class, 'table_id', 'id')->where('table_name', 'comments')->where('table_column', 'id');
    }

    public function extendUsages()
    {
        return $this->hasMany(ExtendUsage::class, 'usage_id', 'id')->where('usage_type', ExtendUsage::TYPE_COMMENT);
    }

    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'hashtag_usages', 'usage_id', 'hashtag_id')->wherePivot('usage_type', HashtagUsage::TYPE_COMMENT)->wherePivotNull('deleted_at');
    }

    public function hashtagUsages()
    {
        return $this->hasMany(HashtagUsage::class, 'usage_id', 'id')->where('usage_type', HashtagUsage::TYPE_COMMENT);
    }

    public function geotag()
    {
        return $this->belongsTo(Geotag::class, 'geotag_id', 'id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    public function parentComment()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function commentLogs()
    {
        return $this->hasMany(CommentLog::class);
    }
}
