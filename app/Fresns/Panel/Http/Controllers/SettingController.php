<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\CacheHelper;
use App\Helpers\PrimaryHelper;
use App\Models\App;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'panel_configs',
            'build_type',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $upgradeCount = App::where('is_upgrade', true)->count();

        return view('FsView::dashboard.settings', compact('params', 'upgradeCount'));
    }

    public function update(Request $request)
    {
        if ($request->path && Str::startsWith($request->path, config('FsConfig.route_blacklist'))) {
            return back()->with('failure', __('FsLang::tips.secure_entry_route_conflicts'));
        }

        if ($request->panel_path) {
            $path = Str::of($request->panel_path)->trim()->toString();
            $path = Str::of($path)->rtrim('/');

            $panelConfigs = Config::where('item_key', 'panel_configs')->firstOrNew();

            $itemValue = $panelConfigs->item_value;
            $itemValue['path'] = $path;

            $panelConfigs->item_value = $itemValue;
            $panelConfigs->save();
        }

        if ($request->build_type) {
            $buildConfig = Config::where('item_key', 'build_type')->firstOrNew();
            $buildConfig->item_value = $request->build_type;
            $buildConfig->save();

            CacheHelper::forgetFresnsConfigs('build_type');

            CacheHelper::forgetFresnsKey('fresns_new_version', 'fresnsSystems');
        }

        return $this->updateSuccess();
    }

    // caches page
    public function caches()
    {
        $upgradeCount = App::where('is_upgrade', true)->count();

        return view('FsView::dashboard.caches', compact('upgradeCount'));
    }

    // cacheAllClear
    public function cacheAllClear()
    {
        CacheHelper::clearAllCache();

        return $this->requestSuccess();
    }

    // cacheSelectClear
    public function cacheSelectClear(Request $request)
    {
        switch ($request->type) {
            case 'config':
                if ($request->fresnsSystem) {
                    CacheHelper::clearConfigCache('fresnsSystem');
                }

                if ($request->fresnsConfig) {
                    CacheHelper::clearConfigCache('fresnsConfig');
                }

                if ($request->fresnsExtend) {
                    CacheHelper::clearConfigCache('fresnsExtend');
                }

                if ($request->fresnsView) {
                    CacheHelper::clearConfigCache('fresnsView');
                }

                if ($request->fresnsRoute) {
                    CacheHelper::clearConfigCache('fresnsRoute');
                }

                if ($request->fresnsEvent) {
                    CacheHelper::clearConfigCache('fresnsEvent');
                }

                if ($request->fresnsSchedule) {
                    CacheHelper::clearConfigCache('fresnsSchedule');
                }

                if ($request->fresnsTemporaryData) {
                    CacheHelper::clearConfigCache('fresnsTemporaryData');
                }
                break;

            case 'data':
                CacheHelper::clearDataCache($request->cacheType, $request->cacheFsid);

                if ($request->cacheType == 'user') {
                    $account = PrimaryHelper::fresnsModelByFsid('account', $request->cacheFsid);
                    $user = PrimaryHelper::fresnsModelByFsid('user', $request->cacheFsid);

                    CacheHelper::forgetFresnsMultilingual("fresns_web_account_{$account?->aid}", 'fresnsWeb');
                    CacheHelper::forgetFresnsMultilingual("fresns_web_user_{$user?->uid}", 'fresnsWeb');
                    CacheHelper::forgetFresnsMultilingual("fresns_web_user_overview_{$user?->uid}", 'fresnsWeb');
                }
                break;

            default:
                return back()->with('failure', __('FsLang::tips.requestFailure'));
                break;
        }

        return $this->requestSuccess();
    }
}
