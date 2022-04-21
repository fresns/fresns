<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\ConfigHelper;
use App\Models\CodeMessage;
use Illuminate\Http\Request;

class CodeMessageController extends Controller
{
    public function index()
    {
        $languageConfig = ConfigHelper::fresnsConfigByItemKeys(['language_status', 'default_language', 'language_menus']);

        return view('FsView::clients.code-messages', compact('languageConfig'));
    }
}
