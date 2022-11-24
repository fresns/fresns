<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateConfigRequest;
use App\Models\Config;
use App\Models\Plugin;

class SettingController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'build_type',
            'system_url',
            'panel_path',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $pluginUpgradeCount = Plugin::where('is_upgrade', 1)->count();

        return view('FsView::dashboard.settings', compact('params', 'pluginUpgradeCount'));
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
