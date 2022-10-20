<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use Browser;

class PortalController extends Controller
{
    public function index()
    {
        $content = fs_db_config('portal_4') ?? (
            Browser::isMobile() ? fs_db_config('portal_3') : fs_db_config('portal_2')
        );

        return view('portal.index', compact('content'));
    }
}
