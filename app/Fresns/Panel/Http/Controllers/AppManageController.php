<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\App;
use App\Utilities\AppUtility;
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

        if ($request->get('is_enabled') != 0) {
            $exitCode = Artisan::call('market:activate', ['fskey' => $fskey]);
        } else {
            $exitCode = Artisan::call('market:deactivate', ['fskey' => $fskey]);
        }

        return $this->updateSuccess();
    }

    public function pluginUninstall(Request $request)
    {
        if ($request->get('clearData') == 1) {
            $exitCode = Artisan::call('market:remove-plugin', [
                'fskey' => $request->fskey,
                '--cleardata' => true,
            ]);
        } else {
            $exitCode = Artisan::call('market:remove-plugin', [
                'fskey' => $request->fskey,
                '--cleardata' => false,
            ]);
        }

        // $exitCode = 0 success
        // $exitCode != 0 fail

        $message = __('FsLang::tips.uninstallSuccess');
        if ($exitCode != 0) {
            $message = __('FsLang::tips.uninstallFailure');
        }

        return response(Artisan::output()."\n".$message);
    }

    public function themeUpgrade(Request $request)
    {
        $fskey = $request->fskey;
    }

    public function themeUninstall(Request $request)
    {
        $fskey = $request->fskey;
        $deleteData = $request->delete_data;

        if (empty($fskey)) {
            return back()->with('failure', 'fskey cannot be empty');
        }

        // App::where('fskey', $fskey)->delete();

        return $this->deleteSuccess();
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
