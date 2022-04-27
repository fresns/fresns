<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\ConfigHelper;
use App\Models\CodeMessage;
use App\Models\Plugin;
use Illuminate\Http\Request;

class CodeMessageController extends Controller
{
    public function index()
    {
        $languageConfig = ConfigHelper::fresnsConfigByItemKeys(['language_status', 'default_language', 'language_menus']);

        $unikeyData = CodeMessage::where('plugin_unikey', '!=', 'Fresns')->get('plugin_unikey')->toArray();
        $unikeyArr = array_column($unikeyData, 'plugin_unikey');
        $unikeys = array_unique($unikeyArr);
        $pluginsList = Plugin::whereIn('unikey', $unikeys)->get(['unikey', 'name'])->toArray();

        $codeMessages = CodeMessage::where('lang_tag', $languageConfig['default_language'])->paginate(20);

        return view('FsView::clients.code-messages', compact('languageConfig', 'pluginsList', 'codeMessages'));
    }
}
