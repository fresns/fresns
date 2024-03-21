<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Account\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class FresnsCallback
{
    public function handle(Request $request, Closure $next)
    {
        $postMessageKey = $request->callbackKey;
        if ($postMessageKey) {
            Cookie::queue('fresns_callback_key', $postMessageKey);
        }

        $callbackUlid = $request->callbackUlid;
        if ($callbackUlid) {
            Cookie::queue('fresns_callback_ulid', $callbackUlid);
        }

        $redirectURL = $request->redirectURL;
        if ($redirectURL) {
            Cookie::queue('fresns_redirect_url', $redirectURL);
        }

        return $next($request);
    }
}
