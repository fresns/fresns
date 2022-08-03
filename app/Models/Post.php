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
        return $this->belongsToMany(Hashtag::class, 'hashtag_usages', 'usage_id', 'hashtag_id')->wherePivot('usage_type', HashtagUsage::TYPE_POST);
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
