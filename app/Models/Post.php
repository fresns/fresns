<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Post extends Model
{
    use Traits\PostServiceTrait;
    use Traits\IsEnabledTrait;
    use Traits\FsidTrait;

    const DIGEST_NO = 1;
    const DIGEST_GENERAL = 2;
    const DIGEST_PREMIUM = 3;
    const STICKY_NO = 1;
    const STICKY_GROUP = 2;
    const STICKY_GLOBAL = 3;

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
        return 'pid';
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function fileUsages()
    {
        return $this->hasMany(FileUsage::class, 'table_id', 'id')->where('table_name', 'posts')->where('table_column', 'id');
    }

    public function extendUsages()
    {
        return $this->hasMany(ExtendUsage::class, 'usage_id', 'id')->where('usage_type', ExtendUsage::TYPE_POST);
    }

    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'hashtag_usages', 'usage_id', 'hashtag_id')->wherePivot('usage_type', HashtagUsage::TYPE_POST)->wherePivotNull('deleted_at');
    }

    public function hashtagUsages()
    {
        return $this->hasMany(HashtagUsage::class, 'usage_id', 'id')->where('usage_type', HashtagUsage::TYPE_POST);
    }

    public function geotag()
    {
        return $this->belongsTo(Geotag::class, 'geotag_usages', 'usage_id', 'geotag_id')->wherePivot('usage_type', GeotagUsage::TYPE_POST)->wherePivotNull('deleted_at');
    }

    public function users()
    {
        return $this->hasMany(PostUser::class);
    }

    public function auths()
    {
        return $this->hasMany(PostAuth::class);
    }

    public function parentPost()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function postLogs()
    {
        return $this->hasMany(PostLog::class);
    }
}
