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
use Illuminate\Support\Facades\View;

class FresnsCallback
{
    public function handle(Request $request, Closure $next)
    {
        $postMessageKey = $request->callbackKey;
        if ($postMessageKey && $postMessageKey != '{postMessageKey}') {
            Cookie::queue(Cookie::make('fresns_post_message_key', $postMessageKey, null, '/', null, false, false));
        }
        View::share('postMessageKey', $postMessageKey ?: Cookie::get('fresns_post_message_key'));

        if ($postMessageKey == '{postMessageKey}') {
            Cookie::queue(Cookie::forget('fresns_post_message_key'));
        }

        $redirectURL = $request->redirectURL;
        if ($redirectURL) {
            $redirectURL = urldecode($redirectURL);

            if ($redirectURL == '{redirectUrl}') {
                Cookie::queue(Cookie::forget('fresns_account_center_callback_redirect_url'));
            } else {
                Cookie::queue('fresns_account_center_callback_redirect_url', $redirectURL);
            }
        }

        return $next($request);
    }
}
