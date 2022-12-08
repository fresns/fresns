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

    public function tokenIsValid(Request $request)
    {
        if (! $request->header('token')) {
            return false;
        }

        /** @var \Fresns\CmdWordManager\CmdWordResponse $fresnsResponse */
        $fresnsResponse = \FresnsCmdWord::plugin('Fresns')->verifyUserToken([
            'platformId' => $request->header('platformId'),
            'version' => $request->header('version'),
            'appId' => $request->header('appId'),
            'aid' => $request->header('aid'),
            'aidToken' => $request->header('aidToken'),
            'uid' => $request->header('uid'),
            'uidToken' => $request->header('uidToken'),
        ]);

        if ($fresnsResponse->isErrorResponse()) {
            return false;
        }

        return true;
    }
}
