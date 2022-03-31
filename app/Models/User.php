<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
    use HasFactory;
    use Traits\UserServiceTrait;

    public function roles()
    {
        return $this->hasMany(UserRole::class, 'user_id', 'id');
    }

    public function stat()
    {
        return $this->hasOne(UserStat::class, 'user_id', 'id');
    }

    public function icon()
    {
        return $this->hasOne(UserIcon::class, 'user_id', 'id');
    }

    public function postLogs()
    {
        return $this->hasMany(PostLog::class, 'user_id', 'id');
    }

    public function commentLogs()
    {
        return $this->hasMany(CommentLog::class, 'user_id', 'id');
    }
}
