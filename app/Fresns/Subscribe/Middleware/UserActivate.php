<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Subscribe\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserActivate
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->tokenIsValid($request)) {
            notifyUserActivate();
        }

        return $next($request);
    }

    public function tokenIsValid()
    {
        if (empty(\request()->header('X-Fresns-Uid-Token'))) {
            return false;
        }

        /** @var \Fresns\CmdWordManager\CmdWordResponse $fresnsResponse */
        $fresnsResponse = \FresnsCmdWord::plugin('Fresns')->verifyUserToken([
            'platformId' => \request()->header('X-Fresns-Client-Platform-Id'),
            'aid' => \request()->header('X-Fresns-Aid'),
            'aidToken' => \request()->header('X-Fresns-Aid-Token'),
            'uid' => \request()->header('X-Fresns-Uid'),
            'uidToken' => \request()->header('X-Fresns-Uid-Token'),
        ]);

        if ($fresnsResponse->isErrorResponse()) {
            return false;
        }

        return true;
    }
}
