<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\AppHelper;
use App\Helpers\CacheHelper;
use App\Models\App;
use App\Utilities\AppUtility;
use App\Utilities\ConfigUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AppManageController extends Controller
{
    public function updateCode(Request $request)
    {
        $app = App::where('fskey', $request->fskey)->first();

        if ($app) {
            $app->upgrade_code = $request->upgradeCode;
            $app->save();

            return $this->updateSuccess();
        }

        return back()->with('failure', __('FsLang::tips.plugin_not_exists'));
    }

    public function pluginCheckStatus()
    {
        AppUtility::checkPluginsStatus();

        return $this->requestSuccess();
    }

    public function pluginUpgrade(Request $request)
    {
        $fskey = $request->fskey;

        // market-manager
        $code = Artisan::call('market:upgrade', [
            'fskey' => $fskey,
            '--install_type' => 'market',
        ]);

        CacheHelper::forgetFresnsKey("fresns_plugin_version_{$fskey}", 'fresnsConfigs');

        $message = __('FsLang::tips.upgradeSuccess');
        if ($code != 0) {
            $message = __('FsLang::tips.installFailure');
        }

        return \response()->json([
            'message' => $message,
            'data' => [
                'output' => Artisan::output()."\n".$message,
            ],
        ], 200);

        return back()->with('failure', __('FsLang::tips.installFailure'));
    }

    public function pluginUpdate(Request $request)
    {
        $fskey = $request->fskey;

        if (empty($fskey)) {
            return back()->with('failure', 'fskey cannot be empty');
        }

        CacheHelper::forgetFresnsKey("fresns_plugin_version_{$fskey}", 'fresnsConfigs');

        $app = App::where('fskey', $fskey)->first();

        if (empty($app)) {
            return back()->with('failure', __('FsLang::tips.plugin_not_exists'));
        }

        $status = $app->is_enabled;

        $newStatus = true;

        if ($status) {
            $exitCode = Artisan::call('plugin:deactivate', [
                'fskey' => $fskey,
            ]);

            $newStatus = false;
        } else {
            $exitCode = Artisan::call('plugin:activate', [
                'fskey' => $fskey,
            ]);
        }

        if ($exitCode != 0) {
            return back()->with('failure', __('FsLang::tips.updateFailure'));
        }

        $app->update([
            'is_enabled' => $newStatus,
        ]);

        CacheHelper::clearConfigCache('fresnsRoute');

        return $this->updateSuccess();
    }

    public function pluginUninstall(Request $request)
    {
        $fskey = $request->fskey;
        $uninstallData = (bool) $request->uninstallData;

        $exitCode = Artisan::call('plugin:uninstall', [
            'fskey' => $fskey,
            '--cleardata' => $uninstallData,
        ]);

        // $exitCode != 0 fail
        $message = __('FsLang::tips.uninstallFailure');

        if ($exitCode == 0) {
            // $exitCode = 0 success
            $message = __('FsLang::tips.uninstallSuccess');

            App::where('fskey', $fskey)->delete();
        }

        CacheHelper::clearConfigCache('fresnsRoute');

        return response(Artisan::output()."\n".$message);
    }

    public function themeUninstall(Request $request)
    {
        $fskey = $request->fskey;
        $deleteData = $request->delete_data;

        if (empty($fskey)) {
            return back()->with('failure', 'fskey cannot be empty');
        }

        $exitCode = Artisan::call('theme:uninstall', [
            'fskey' => $fskey,
        ]);

        // $exitCode != 0 fail
        if ($exitCode != 0) {
            return back()->with('failure', __('FsLang::tips.uninstallFailure'));
        }

        if ($deleteData) {
            $themeJson = AppHelper::getThemeConfig($fskey);

            $functionItems = $themeJson['functionItems'] ?? [];

            $itemKeys = array_map(function ($item) {
                return $item['itemKey'];
            }, $functionItems);

            ConfigUtility::removeFresnsConfigItems($itemKeys);
        }

        App::where('fskey', $fskey)->delete();

        return $this->uninstallSuccess();
    }

    public function appDownload(Request $request)
    {
        $appFskey = $request->fskey;

        if (empty($appFskey)) {
            return \response()->json([
                'code' => 21005,
                'message' => __('FsLang::tips.install_not_entered_key'),
                'data' => null,
            ]);
        }

        $fresnsResp = \FresnsCmdWord::plugin('MarketManager')->appDownload(['fskey' => $appFskey]);

        return \response()->json($fresnsResp->getOrigin());
    }

    public function appDelete(Request $request)
    {
        $fskey = $request->fskey;

        if (empty($fskey)) {
            return back()->with('failure', 'fskey cannot be empty');
        }

        App::where('fskey', $fskey)->delete();

        return $this->deleteSuccess();
    }
}
