<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Middleware;

use App\Fresns\Api\Exceptions\ResponseException;
use App\Helpers\PrimaryHelper;
use Closure;
use Illuminate\Http\Request;

class CheckReadOnly
{
    public function handle(Request $request, Closure $next)
    {
        $appId = \request()->header('X-Fresns-App-Id');
        $keyInfo = PrimaryHelper::fresnsModelByFsid('key', $appId);

        if ($keyInfo?->is_read_only) {
            $blacklist = config('FsApiBlacklist.disableForReadOnly');
            $currentRouteName = \request()->route()->getName();

            if (in_array($currentRouteName, $blacklist)) {
                throw new ResponseException(31305);
            }
        }

        return $next($request);
    }
}
