<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Group extends Model
{
    const PRIVACY_PUBLIC = 1;
    const PRIVACY_PRIVATE = 2;

    const VISIBILITY_VISIBLE = 1;
    const VISIBILITY_HIDDEN = 2;

    const PRIVATE_OPTION_UNRESTRICTED = 1;
    const PRIVATE_OPTION_HIDE_ALL = 2;
    const PRIVATE_OPTION_HIDE_NEW = 3;

    const FOLLOW_METHOD_API = 1;
    const FOLLOW_METHOD_PLUGIN = 2;
    const FOLLOW_METHOD_CLOSE = 3;

    use Traits\FsidTrait;
    use Traits\IsEnabledTrait;
    use Traits\GroupServiceTrait;

    protected $casts = [
        'name' => 'json',
        'description' => 'json',
        'more_info' => 'json',
        'permissions' => 'json',
    ];

    protected $dates = [
        'last_post_at',
        'last_comment_at',
    ];

    public function getFsidKey()
    {
        return 'gid';
    }

    public function parentGroup()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function groups()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function allGroups()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')->with('groups');
    }

    public function flattenGroups()
    {
        $groups = collect([$this]);

        $this->allGroups->each(function ($group) use ($groups) {
            $groups->push($group->flattenGroups());
        });

        return $groups->flatten();
    }

    public function admins()
    {
        return $this->belongsToMany(User::class, 'group_admins', 'group_id', 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function followByApp()
    {
        return $this->belongsTo(App::class, 'follow_app_fskey', 'fskey');
    }
}
