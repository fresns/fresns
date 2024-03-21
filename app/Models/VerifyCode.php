<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class VerifyCode extends Model
{
    const TYPE_EMAIL = 1;
    const TYPE_SMS = 2;

    const TEMPLATE_GENERAL = 1;
    const TEMPLATE_REGISTER_ACCOUNT = 2;
    const TEMPLATE_EDIT_PROFILE = 3;
    const TEMPLATE_CHANGE_EMAIL_OR_PHONE = 4;
    const TEMPLATE_RESET_LOGIN_PASSWORD = 5;
    const TEMPLATE_RESET_WALLET_PASSWORD = 6;
    const TEMPLATE_LOGIN_ACCOUNT = 7;
    const TEMPLATE_DELETE_ACCOUNT = 8;

    use Traits\IsEnabledTrait;

    protected $dates = [
        'expired_at',
    ];
}
