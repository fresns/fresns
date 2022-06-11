<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class UserRole extends Model
{
    const TYPE_ADMIN = 1;
    const TYPE_SYSTEM = 2;
    const TYPE_USER = 3;

    protected $guarded = ['id'];

    public function scopeType($query, int $type)
    {
        return $query->where('type', $type);
    }

    public function roleInfo()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}
