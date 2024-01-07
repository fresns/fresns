<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class AccountWalletLog extends Model
{
    const TYPE_IN_RECHARGE = 1;
    const TYPE_IN_FREEZE = 2;
    const TYPE_IN_TRANSACTION = 3;
    const TYPE_IN_REVERSAL = 4;
    const TYPE_DE_WITHDRAW = 5;
    const TYPE_DE_UNFREEZE = 6;
    const TYPE_DE_TRANSACTION = 7;
    const TYPE_DE_REVERSAL = 8;

    const STATE_PENDING = 1;
    const STATE_PROCESSING = 2;
    const STATE_SUCCESS = 3;
    const STATE_FAILED = 4;
    const STATE_REVERSED = 5;

    protected $casts = [
        'more_info' => 'json',
    ];

    public function scopeType($query, int $type)
    {
        return $query->where('type', $type);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'object_user_id');
    }
}
