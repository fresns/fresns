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

class VerifyAccountToken
{
    public function handle(Request $request, Closure $next)
    {
        $langTag = Cookie::get('fresns_account_center_lang_tag') ?? ConfigHelper::fresnsConfigDefaultLangTag();
        $request->headers->set('X-Fresns-Client-Lang-Tag', $langTag);

        $service = ConfigHelper::fresnsConfigByItemKey('account_center_service');

        if ($service) {
            throw new ResponseException(33100);
        }

        $platformId = Cookie::get('fresns_account_center_platform_id');
        $aid = Cookie::get('fresns_account_center_aid');
        $aidToken = Cookie::get('fresns_account_center_aid_token');

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->verifyAccountToken([
            'platformId' => $platformId,
            'aid' => $aid,
            'aidToken' => $aidToken,
        ]);

        if ($fresnsResp->isErrorResponse()) {
            throw new ResponseException($fresnsResp->getCode());
        }

        $request->attributes->add([
            'fresns_account_center_platform_id' => $platformId,
            'fresns_account_center_aid' => $aid,
            'fresns_account_center_aid_token' => $aidToken,
        ]);

        return $next($request);
    }
}
