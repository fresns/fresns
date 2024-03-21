<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Account\Http\Middleware;

use App\Helpers\ConfigHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class SetHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $langTag = Cookie::get('fresns_account_center_lang_tag') ?? ConfigHelper::fresnsConfigDefaultLangTag();

        $request->headers->set('X-Fresns-Client-Lang-Tag', $langTag);

        return $next($request);
    }
}
