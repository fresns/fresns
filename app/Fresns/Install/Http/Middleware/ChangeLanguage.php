<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Install\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class ChangeLanguage
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($currentLang = \request()->input('install_lang')) {
            Cookie::queue('install_lang', $currentLang);
        }

        \App::setLocale(Cookie::get('install_lang', config('app.locale')));

        $request->headers->set('install_lang', \App::getLocale());

        return $next($request);
    }
}
