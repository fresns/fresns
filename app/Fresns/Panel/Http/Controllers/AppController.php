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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

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
            'webengine_installed',
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

    public function install(Request $request)
    {
        $installMethod = $request->install_method;

        switch ($installMethod) {
            // fskey
            case 'inputFskey':
                $appFskey = $request->app_fskey;

                if (empty($appFskey)) {
                    return back()->with('failure', __('FsLang::tips.install_not_entered_key'));
                }

                // market-manager
                $exitCode = Artisan::call('market:require', [
                    'fskey' => $appFskey,
                    '--install_type' => 'market',
                ]);
                $output = Artisan::output();
                break;

                // directory
            case 'inputDirectory':
                $appDirectory = $request->app_directory;

                if (empty($appDirectory)) {
                    return back()->with('failure', __('FsLang::tips.install_not_entered_directory'));
                }

                $isTheme = Str::contains($appDirectory, 'themes/');

                // plugin-manager
                $exitCode = Artisan::call('market:require', [
                    'fskey' => $appDirectory,
                    '--install_type' => 'local',
                ]);
                $output = Artisan::output();
                break;

                // zipball
            case 'inputZipball':
                $pluginZipball = null;
                $file = $request->file('app_zipball');
                if ($file && $file->isValid()) {
                    $dir = config('markets.paths.uploads');
                    $filename = $file->hashName();
                    $file->move($dir, $filename);

                    $pluginZipball = "$dir/$filename";
                }

                if (empty($pluginZipball)) {
                    return back()->with('failure', __('FsLang::tips.install_not_upload_zip'));
                }

                // plugin-manager
                $exitCode = Artisan::call('market:require', [
                    'fskey' => $pluginZipball,
                    '--install_type' => 'local',
                ]);
                $output = Artisan::output();
                break;
        }

        if ($exitCode == 0) {
            return \response($output."\n ".__('FsLang::tips.installSuccess'));
        }

        if ($output == '') {
            $output = __('FsLang::tips.viewLog')."\n ".' /storage/logs';
        }

        return \response($output."\n ".__('FsLang::tips.installFailure'));
    }

    // iframe
    public function iframe(Request $request)
    {
        $url = $request->url;

        return view('FsView::app-center.iframe', compact('url'));
    }
}
