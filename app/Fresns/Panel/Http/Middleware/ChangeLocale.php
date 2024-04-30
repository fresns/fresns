<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class ChangeLocale
{
    public function handle(Request $request, Closure $next)
    {
        $langTag = $request->lang ?? $request->language ?? $request->locale;

        if ($langTag) {
            Cookie::queue(Cookie::forever('fresns_panel_locale', $langTag, '/'));

            return back()->withInput($request->except('fresns_panel_locale'));
        }

        App::setLocale(Cookie::get('fresns_panel_locale', config('app.locale')));

        $request->headers->set('fresns_panel_locale', App::getLocale());

        return $next($request);
    }
}
