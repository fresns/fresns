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
            Cookie::queue('panel_lang', $request->lang);

            return back()->withInput($request->except('panel_lang'));
        }

        \App::setLocale(Cookie::get('panel_lang', config('app.locale')));

        $request->headers->set('panel_lang', \App::getLocale());

        return $next($request);
    }
}
