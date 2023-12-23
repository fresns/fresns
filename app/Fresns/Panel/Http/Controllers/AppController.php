<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\App;
use Illuminate\Http\Request;

class AppController extends Controller
{
    // plugins
    public function plugins(Request $request)
    {
        $type = $request->type;

        $pluginQuery = App::type(App::TYPE_PLUGIN);

        $isEnabled = match ($request->status) {
            'active' => 1,
            'inactive' => 0,
            default => null,
        };

        if (! is_null($isEnabled)) {
            $pluginQuery->isEnabled($isEnabled);
        }

        $plugins = $pluginQuery->latest()->paginate(30);

        $enableCount = App::type(App::TYPE_PLUGIN)->isEnabled()->count();
        $disableCount = App::type(App::TYPE_PLUGIN)->isEnabled(false)->count();

        return view('FsView::app-center.plugins', compact('plugins', 'enableCount', 'disableCount', 'isEnabled'));
    }

    // themes
    public function themes(Request $request)
    {
        $themes = App::type(App::TYPE_THEME)->latest()->get();

        return view('FsView::app-center.themes', compact('themes'));
    }

    // apps
    public function apps(Request $request)
    {
        $apps = App::whereIn('type', [App::TYPE_APP_REMOTE, App::TYPE_APP_DOWNLOAD])->latest()->paginate(30);

        return view('FsView::app-center.apps', compact('apps'));
    }

    // iframe
    public function iframe(Request $request)
    {
        $url = $request->url;

        return view('FsView::app-center.iframe', compact('url'));
    }
}
