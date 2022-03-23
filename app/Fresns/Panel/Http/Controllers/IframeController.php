<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Plugin;
use Illuminate\Http\Request;

class IframeController extends Controller
{
    // Plugin
    public function plugin(Request $request)
    {
        // Plugin Sidebar
        $enablePlugins = Plugin::type(1)->where('is_enable', 1)->get();

        $url = $request->url;

        return view('FsView::iframe.plugin', compact('url', 'enablePlugins'));
    }

    // Client
    public function client(Request $request)
    {
        $url = $request->url;

        return view('FsView::iframe.client', compact('url'));
    }

    // App Store
    public function market(Request $request)
    {
        $url = $request->url;

        return view('FsView::iframe.market', compact('url'));
    }
}
