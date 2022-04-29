<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cookie;

class ChangeLocale
{
    public function handle($request, Closure $next)
    {
        if ($request->lang) {
            Cookie::queue('lang', $request->lang);

            return back()->withInput($request->except('lang'));
        }

        \App::setLocale(Cookie::get('lang', config('app.locale')));

        return $next($request);
    }
}
