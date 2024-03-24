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
            Cookie::queue(Cookie::make('fresns_callback_key', $postMessageKey, null, '/', null, false, false));
        }

        $callbackUlid = $request->callbackUlid;
        if ($callbackUlid) {
            Cookie::queue(Cookie::make('fresns_callback_ulid', $callbackUlid, null, '/', null, false, false));
        }

        $redirectURL = $request->redirectURL;
        if ($redirectURL) {
            Cookie::queue(Cookie::make('fresns_redirect_url', $redirectURL, null, '/', null, false, false));
        }

        return $next($request);
    }
}
