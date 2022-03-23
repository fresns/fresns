<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Account;

class FsConfig
{
    // Wallet Type
    const PLUGIN_USAGERS_TYPE_1 = 1;
    const PLUGIN_USAGERS_TYPE_2 = 2;

    // Main Role
    const USER_ROLE_REL_TYPE_2 = 2;

    // Password verification rules
    const PASSWORD_NUMBER = 1; // Digital
    const PASSWORD_LOWERCASE_LETTERS = 2; // Lowercase letters
    const PASSWORD_CAPITAL_LETTERS = 3; // Capital letters
    const PASSWORD_SYMBOL = 4; // Symbols
}
