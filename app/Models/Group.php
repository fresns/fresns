<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Group extends Model
{
    const TYPE_CATEGORY = 1;
    const TYPE_GROUP = 2;
    const TYPE_SUBLEVEL_GROUP = 3;

    const SUBLEVEL_PUBLIC = 1;
    const SUBLEVEL_PRIVATE = 0;

    const MODE_PUBLIC = 1;
    const MODE_PRIVATE = 2;

    const PRIVATE_OPTION_UNRESTRICTED = 1;
    const PRIVATE_OPTION_HIDE_ALL = 2;
    const PRIVATE_OPTION_HIDE_NEW = 3;

    const FIND_VISIBLE = 1;
    const FIND_HIDDEN = 2;

    const FOLLOW_FRESNS = 1;
    const FOLLOW_PLUGIN = 2;
    const FOLLOW_CLOSE = 3;

    use Traits\GroupServiceTrait;
    use Traits\IsEnabledTrait;
    use Traits\FsidTrait;

    protected $casts = [
        'permissions' => 'json',
    ];

    public function getFsidKey()
    {
        return 'gid';
    }

    public function scopeTypeCategory($query)
    {
        return $query->where('type', 1);
    }

    public function scopeTypeGroup($query)
    {
        return $query->where('type', 2);
    }

    public function category()
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

    public function followByPlugin()
    {
        return $this->belongsTo(Plugin::class, 'plugin_fskey', 'fskey');
    }
}
