<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSiteModel
{
    public function handle(Request $request, Closure $next)
    {
        if (fs_api_config('site_mode') == 'private' && fs_user()->guest()) {
            return view('private');
        }

        return $next($request);
    }
}
