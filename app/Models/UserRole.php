<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class UserRole extends Model
{
    const TYPE_GENERAL = 0;
    const TYPE_MAIN = 1;

    protected $dates = [
        'expired_at',
    ];

    public function scopeType($query, int $type)
    {
        return $query->where('type', $type);
    }

    public function roleInfo()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function restoreRole()
    {
        return $this->belongsTo(Role::class, 'restore_role_id', 'id');
    }
}
