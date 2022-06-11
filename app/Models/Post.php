<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

use App\Utilities\PermissionUtility;

class Post extends Model
{
    use Traits\PostServiceTrait;
    use Traits\IsEnableTrait;

    protected $guarded = ['id'];

    public function scopeBeforeExpiredAtOrNotLimit($query, ?User $user)
    {
        if (! $user) {
            return $query;
        }

        $userConfig = PermissionUtility::getUserExpireInfo($user->id);

        return $query->when(! $userConfig['userStatus'] && $userConfig['expireAfter'] == 2, function ($query) use ($user) {
            $query->where('created_at', '<=', $user->expired_at ?? now());
        });
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
        return $this->belongsToMany(Hashtag::class, 'hashtag_linkeds', 'hashtag_id', 'linked_id')->wherePivot('linked_type', HashtagLinked::TYPE_POST);
    }

    public function users()
    {
        return $this->hasMany(PostUser::class);
    }

    public function allowUsers()
    {
        return $this->hasMany(PostAllow::class)->where('type', 1);
    }

    public function allowRoles()
    {
        return $this->hasMany(PostAllow::class)->where('type', 2);
    }
}
