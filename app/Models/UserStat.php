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
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id')->wherePivot('deleted_at', null);
    }

    public function mainRole()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id')
            ->wherePivot('deleted_at', null)
            ->wherePivot('is_main', true)
            ->wherePivot('expired_at', null)
            ->orWherePivot('expired_at', '>=', now());
    }
}
