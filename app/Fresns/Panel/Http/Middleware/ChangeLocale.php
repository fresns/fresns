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
        if ($currentLang = \request()->input('lang')) {
            \Cookie::queue('lang', $currentLang);
        }

        \App::setLocale($locale = \Cookie::get('lang', 'zh-Hans'));
        \View::share('locale', $locale);

        return $next($request);
    }
}
