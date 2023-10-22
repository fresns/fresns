<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\Middleware;

use App\Helpers\ConfigHelper;
use Illuminate\Contracts\Encryption\Encrypter;
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
    ];

    public function __construct(Encrypter $encrypter)
    {
        parent::__construct($encrypter);

        try {
            $cookiePrefix = ConfigHelper::fresnsConfigByItemKey('website_cookie_prefix') ?? 'fresns_';
        } catch (\Exception $e) {
            $cookiePrefix = 'fresns_';
        }

        $this->except = array_merge($this->except, [
            "{$cookiePrefix}ulid",
            "{$cookiePrefix}lang_tag",
            "{$cookiePrefix}aid",
            "{$cookiePrefix}aid_token",
            "{$cookiePrefix}uid",
            "{$cookiePrefix}uid_token",
        ]);
    }
}
