<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class UserExtcreditsLog extends Model
{
    public function profile()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
