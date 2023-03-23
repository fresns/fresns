<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class AccountWalletLog extends Model
{
    const TYPE_IN_RECHARGE = 1;
    const TYPE_IN_FREEZE = 2;
    const TYPE_IN_TRANSACTION = 3;
    const TYPE_DE_WITHDRAW = 4;
    const TYPE_DE_UNFREEZE = 5;
    const TYPE_DE_TRANSACTION = 6;

    use Traits\IsEnableTrait;

    public function scopeType($query, int $type)
    {
        return $query->where('type', $type);
    }
}
