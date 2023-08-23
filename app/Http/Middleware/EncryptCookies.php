<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
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
        'fresns_ulid',
        'fresns_lang_tag',
        'fresns_aid',
        'fresns_aid_token',
        'fresns_uid',
        'fresns_uid_token',

        // website one engine
        'fresns_one_ulid',
        'fresns_one_lang_tag',
        'fresns_one_aid',
        'fresns_one_aid_token',
        'fresns_one_uid',
        'fresns_one_uid_token',

        // website two engine
        'fresns_two_ulid',
        'fresns_two_lang_tag',
        'fresns_two_aid',
        'fresns_two_aid_token',
        'fresns_two_uid',
        'fresns_two_uid_token',

        // website three engine
        'fresns_three_ulid',
        'fresns_three_lang_tag',
        'fresns_three_aid',
        'fresns_three_aid_token',
        'fresns_three_uid',
        'fresns_three_uid_token',

        // website editor
        'fresns_draft',
    ];
}
