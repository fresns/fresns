<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Role extends Model
{
    use Traits\LangNameTrait;
    use Traits\IsEnableTrait;

    protected $guarded = ['id'];

    protected $casts = [
        'permission' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'user_id', 'role_id');
    }
}
