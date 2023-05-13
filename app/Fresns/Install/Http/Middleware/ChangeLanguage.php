<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Install\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class ChangeLanguage
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($currentLang = \request()->input('install_lang')) {
            Cookie::queue('install_lang', $currentLang);
        }

        App::setLocale(Cookie::get('install_lang', config('app.locale')));

        $request->headers->set('install_lang', App::getLocale());

        return $next($request);
    }
}
