<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class VerifyCode extends Model
{
    const TEMPLATE_GENERAL = 1;
    const TEMPLATE_REGISTER = 2;
    const TEMPLATE_EDIT = 3;
    const TEMPLATE_CHANGE = 4;
    const TEMPLATE_RESET_LOGIN_PASSWORD = 5;
    const TEMPLATE_RESET_WALLET_PASSWORD = 6;
    const TEMPLATE_LOGIN = 7;
    const TEMPLATE_DELETE_ACCOUNT = 8;

    use Traits\IsEnableTrait;

    protected $dates = [
        'expired_at',
    ];
}
