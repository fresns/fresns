<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class UserLog extends Model
{
    use Traits\IsEnabledTrait;

    const TYPE_UID = 1;
    const TYPE_USERNAME = 2;
    const TYPE_NICKNAME = 3;
    const TYPE_AVATAR = 4;
    const TYPE_BANNER = 5;
    const TYPE_BIO = 6;
    const TYPE_VERIFIED_DESC = 7;

    public function profile()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
