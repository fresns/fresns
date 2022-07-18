<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Middleware;

use App\Utilities\ConfigUtility;
use Closure;
use Illuminate\Http\Request;

class UserAuthorize
{
    public function handle(Request $request, Closure $next)
    {
        try {
            if (fs_user()->check()) {
                return $next($request);
            } else {
                $userLoginTip = ConfigUtility::getCodeMessage(31601, 'Fresns', current_lang_tag());

                return redirect()->fs_route(route('fresns.account.index'))->withErrors($userLoginTip); //FsLang
            }
        } catch (\Exception $exception) {
            return redirect()->fs_route(route('fresns.account.login'))->withErrors($exception->getMessage());
        }
    }
}
