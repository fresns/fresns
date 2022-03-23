<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Middleware;

use Closure;

class ChangeLocale
{
    public function handle($request, Closure $next)
    {
        if ($request->lang) {
            $cookie = \Cookie::forever('lang', $request->lang);

            return back()->exceptInput('lang')->withCookie($cookie);
        }
        $locale = \Cookie::get('lang', 'zh-Hans');

        \App::setLocale($locale);

        \View::share('locale', \App::getLocale());

        return $next($request);
    }
}
