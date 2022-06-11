<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Subscribe\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserActivateMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->tokenIsValid($request)) {
            notifyUserActivate([
                'platformId' => $request->header('platformId'),
                'version' => $request->header('version'),
                'appId' => $request->header('appId'),
                'langTag' => $request->header('langTag'),
                'timezone' => $request->header('timezone'),
                'aid' => $request->header('aid'),
                'uid' => $request->header('uid'),
                'deviceInfo' => $request->header('deviceInfo'),
            ]);
        }

        return $next($request);
    }

    public function tokenIsValid(Request $request)
    {
        if (! $request->header('token')) {
            return false;
        }

        /** @var \Fresns\CmdWordManager\CmdWordResponse $fresnsResponse */
        $fresnsResponse = \FresnsCmdWord::plugin('Fresns')->verifySessionToken([
            'platformId' => $request->header('platformId'),
            'aid' => $request->header('aid'),
            'uid' => $request->header('uid'),
            'token' => $request->header('token'),
        ]);

        if ($fresnsResponse->isErrorResponse()) {
            return false;
        }

        return true;
    }
}
