<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Group extends Model
{
    use Traits\LangNameTrait;
    use Traits\LangDescriptionTrait;
    use Traits\GroupServiceTrait;
    use Traits\IsEnableTrait;

    protected $casts = [
        'permission' => 'array',
    ];

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

    public function admins()
    {
        return $this->belongsToMany(User::class, 'group_admins', 'group_id', 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function plugin()
    {
        return $this->belongsTo(Plugin::class, 'plugin_unikey', 'unikey');
    }
}
