<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class AccountWallet extends Model
{
    use Traits\IsEnabledTrait;

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
}
