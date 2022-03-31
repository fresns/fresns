<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class UserRole extends Model
{
    protected $guarded = ['id'];

    public function info()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}
