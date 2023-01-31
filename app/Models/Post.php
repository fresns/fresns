<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Post extends Model
{
    use Traits\PostServiceTrait;
    use Traits\IsEnableTrait;
    use Traits\FsidTrait;

    const DIGEST_NO = 1;
    const DIGEST_GENERAL = 2;
    const DIGEST_BEST = 3;
    const STICKY_NO = 1;
    const STICKY_GROUP = 2;
    const STICKY_GLOBAL = 3;

    protected $dates = [
        'latest_edit_at',
        'latest_comment_at',
    ];

    public function getFsidKey()
    {
        return 'pid';
    }

    public function postAppend()
    {
        return $this->hasOne(PostAppend::class);
    }

    public function postLogs()
    {
        return $this->hasMany(PostLog::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'hashtag_usages', 'usage_id', 'hashtag_id')->wherePivot('usage_type', HashtagUsage::TYPE_POST)->wherePivot('deleted_at', null);
    }

    public function fileUsages()
    {
        return $this->hasMany(FileUsage::class, 'table_id', 'id')->where('table_name', 'posts')->where('table_column', 'id');
    }

    public function extendUsages()
    {
        return $this->hasMany(ExtendUsage::class, 'usage_id', 'id')->where('usage_type', ExtendUsage::TYPE_POST);
    }

    public function users()
    {
        return $this->hasMany(PostUser::class);
    }

    public function allows()
    {
        return $this->hasMany(PostAllow::class);
    }
}
