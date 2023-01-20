<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        'install_lang',
        'panel_lang',

        // website engine
        'fresns_uuid',
        'fresns_lang_tag',
        'fresns_aid',
        'fresns_aid_token',
        'fresns_uid',
        'fresns_uid_token',

        // website single engine
        'fresns_single_uuid',
        'fresns_single_lang_tag',
        'fresns_single_aid',
        'fresns_single_aid_token',
        'fresns_single_uid',
        'fresns_single_uid_token',
    ];
}
