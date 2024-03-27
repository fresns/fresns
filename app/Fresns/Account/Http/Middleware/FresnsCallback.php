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
        if ($postMessageKey && $postMessageKey != '{postMessageKey}') {
            Cookie::queue(Cookie::make('fresns_callback_key', $postMessageKey, null, '/', null, false, false));
        }

        if ($postMessageKey == '{postMessageKey}') {
            Cookie::queue(Cookie::forget('fresns_callback_key'));
        }

        $callbackUlid = $request->callbackUlid;
        if ($callbackUlid && $callbackUlid != '{callbackUlid}') {
            Cookie::queue(Cookie::make('fresns_callback_ulid', $callbackUlid, null, '/', null, false, false));
        }

        if ($callbackUlid == '{callbackUlid}') {
            Cookie::queue(Cookie::forget('fresns_callback_ulid'));
        }

        $redirectURL = $request->redirectURL;
        if ($redirectURL && $redirectURL != '{redirectUrl}') {
            Cookie::queue(Cookie::make('fresns_redirect_url', $redirectURL, null, '/', null, false, false));
        }

        if ($redirectURL == '{redirectUrl}') {
            Cookie::queue(Cookie::forget('fresns_redirect_url'));
        }

        return $next($request);
    }
}
