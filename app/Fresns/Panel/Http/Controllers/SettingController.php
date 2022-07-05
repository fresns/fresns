<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateConfigRequest;
use App\Helpers\ConfigHelper;
use App\Models\Config;
use App\Models\Plugin;

class SettingController extends Controller
{
    public function show()
    {
        $buildType = ConfigHelper::fresnsConfigByItemKey('build_type');
        $systemUrl = ConfigHelper::fresnsConfigByItemKey('system_url');
        $panelPath = ConfigHelper::fresnsConfigByItemKey('panel_path') ?? 'admin';

        $pluginUpgradeCount = Plugin::where('is_upgrade', 1)->count();

        return view('FsView::dashboard.settings', compact('buildType', 'systemUrl', 'panelPath', 'pluginUpgradeCount'));
    }

    public function update(UpdateConfigRequest $request)
    {
        if ($request->path && \Str::startsWith($request->path, config('FsConfig.route_blacklist'))) {
            return back()->with('failure', __('FsLang::tips.secure_entry_route_conflicts'));
        }

        if ($request->build_type) {
            $buildConfig = Config::where('item_key', 'build_type')->firstOrNew();
            $buildConfig->item_value = $request->build_type;
            $buildConfig->save();
        }

        if ($request->systemUrl) {
            $systemUrl = Config::where('item_key', 'system_url')->firstOrNew();
            $systemUrl->item_value = $request->systemUrl;
            $systemUrl->save();
        }

        if ($request->panelPath) {
            $pathConfig = Config::where('item_key', 'panel_path')->firstOrNew();
            $pathConfig->item_value = trim($request->panelPath, '/');
            $pathConfig->save();
        }

        return $this->updateSuccess();
    }
}
