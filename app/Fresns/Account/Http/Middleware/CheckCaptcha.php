<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Account\Http\Middleware;

use App\Fresns\Api\Exceptions\ResponseException;
use App\Helpers\ConfigHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CheckCaptcha
{
    public function handle(Request $request, Closure $next)
    {
        $captcha = ConfigHelper::fresnsConfigByItemKey('account_center_captcha');

        $type = $captcha['type'] ?? null;
        $siteKey = $captcha['siteKey'] ?? null;
        $secretKey = $captcha['secretKey'] ?? null;

        if (empty($type) || empty($siteKey) || empty($secretKey)) {
            return $next($request);
        }

        $langTag = Cookie::get('fresns_account_center_lang_tag') ?? ConfigHelper::fresnsConfigDefaultLangTag();

        $errorCode = 0;

        switch ($type) {
            case 'turnstile':
                // Turnstile (Cloudflare)
                // https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
                break;

            case 'reCAPTCHA':
                // reCAPTCHA (Google)
                // https://developers.google.com/recaptcha/docs/verify
                break;

            case 'hCaptcha':
                // hCaptcha (Intuition Machines)
                // https://docs.hcaptcha.com/#verify-the-user-response-server-side
                break;
        }

        if ($errorCode) {
            throw new ResponseException($errorCode);
        }

        return $next($request);
    }
}
