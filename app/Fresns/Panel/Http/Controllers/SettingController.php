<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateConfigRequest;
use App\Models\Config;

class SettingController extends Controller
{
    public function show()
    {
        $domainConfig = Config::where('item_key', 'backend_domain')->first();
        $domain = optional($domainConfig)->item_value;

        $pathConfig = Config::where('item_key', 'panel_path')->first();
        $path = optional($pathConfig)->item_value ?: 'admin';

        return view('FsView::dashboard.settings', compact('domain', 'path'));
    }

    public function update(UpdateConfigRequest $request)
    {
        if ($request->path && \Str::startsWith($request->path, config('FsConfig.route_blacklist'))) {
            return back()->with('failure', __('FsLang::tips.secure_entry_route_conflicts'));
        }

        if ($request->domain) {
            $domainConfig = Config::where('item_key', 'backend_domain')->firstOrNew();
            $domainConfig->item_value = $request->domain;
            $domainConfig->save();
        }

        if ($request->path) {
            $pathConfig = Config::where('item_key', 'panel_path')->firstOrNew();
            $pathConfig->item_value = trim($request->path, '/');
            $pathConfig->save();
        }

        return $this->updateSuccess();
    }
}
