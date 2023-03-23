<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Install\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DetectionRequestProtocol
{
    public function handle(Request $request, Closure $next)
    {
        if (\request()->secure()) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        return $next($request);
    }
}
