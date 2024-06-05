<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class UserStat extends Model
{
    public function profile()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id')->wherePivotNull('deleted_at');
    }

    public function mainRole()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id')
            ->wherePivot('is_main', true)
            ->wherePivotNull('deleted_at')
            ->wherePivotNull('expired_at')
            ->orWherePivot('expired_at', '>=', now());
    }

    public function mainUserRole()
    {
        return $this->belongsTo(UserRole::class, 'user_id', 'user_id')
            ->where('is_main', true)
            ->whereNull('deleted_at')
            ->whereNull('expired_at')
            ->orWhere('expired_at', '>=', now());
    }
}
