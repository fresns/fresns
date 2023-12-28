<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\App;
use App\Models\Config;
use App\Models\SessionKey;
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

        // config keys
        $configKeys = [
            'platforms',
            'webengine_status',
            'webengine_api_type',
            'webengine_api_host',
            'webengine_api_app_id',
            'webengine_api_app_key',
            'webengine_key_id',
            'webengine_view_desktop',
            'webengine_view_mobile',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        $params = [];
        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $desktopFskey = $params['webengine_view_desktop'] ?? null;
        $mobileFskey = $params['webengine_view_mobile'] ?? null;

        $desktopThemeName = $themes->firstWhere('fskey', $desktopFskey)?->name ?? '';
        $mobileThemeName = $themes->firstWhere('fskey', $mobileFskey)?->name ?? '';

        $keys = SessionKey::where('type', SessionKey::TYPE_CORE)->where('platform_id', SessionKey::PLATFORM_WEB_RESPONSIVE)->isEnabled()->get();

        return view('FsView::app-center.themes', compact('themes', 'params', 'desktopThemeName', 'mobileThemeName', 'keys'));
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
