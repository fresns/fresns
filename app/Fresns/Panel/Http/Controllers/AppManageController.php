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
    public function install(Request $request)
    {
        $installMethod = $request->install_method;

        switch ($installMethod) {
            // fskey
            case 'inputFskey':
                $pluginFskey = $request->plugin_fskey;

                if (empty($pluginFskey)) {
                    return back()->with('failure', __('FsLang::tips.install_not_entered_key'));
                }

                // market-manager
                $exitCode = Artisan::call('market:require', [
                    'fskey' => $pluginFskey,
                    '--install_type' => 'market',
                ]);
                $output = Artisan::output();
                break;

                // directory
            case 'inputDirectory':
                $pluginDirectory = $request->plugin_directory;

                if (empty($pluginDirectory)) {
                    return back()->with('failure', __('FsLang::tips.install_not_entered_directory'));
                }

                // plugin-manager
                $exitCode = Artisan::call('market:require', [
                    'fskey' => $pluginDirectory,
                    '--install_type' => 'local',
                ]);
                $output = Artisan::output();
                break;

                // zipball
            case 'inputZipball':
                $pluginZipball = null;
                $file = $request->file('plugin_zipball');
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

    public function upgrade(Request $request)
    {
        $fskey = $request->get('fskey');

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

    public function update(Request $request)
    {
        if ($request->get('is_enabled') != 0) {
            $exitCode = Artisan::call('market:activate', ['fskey' => $request->plugin]);
        } else {
            $exitCode = Artisan::call('market:deactivate', ['fskey' => $request->plugin]);
        }

        return $this->updateSuccess();
    }

    public function uninstall(Request $request)
    {
        if ($request->get('clearData') == 1) {
            $exitCode = Artisan::call('market:remove-plugin', [
                'fskey' => $request->plugin,
                '--cleardata' => true,
            ]);
        } else {
            $exitCode = Artisan::call('market:remove-plugin', [
                'fskey' => $request->plugin,
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

    public function checkStatus()
    {
        AppUtility::checkPluginsStatus();

        return $this->requestSuccess();
    }

    public function updateCode(Request $request)
    {
        $app = App::where('fskey', $request->input('fskey'))->first();

        if ($app) {
            $app->upgrade_code = $request->upgradeCode;
            $app->save();

            return $this->updateSuccess();
        }

        return back()->with('failure', __('FsLang::tips.plugin_not_exists'));
    }

    public function appDownload(Request $request)
    {
        $appFskey = $request->app_fskey;

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
        $fskey = $request->app_fskey;

        if (empty($fskey)) {
            return back()->with('failure', 'fskey cannot be empty');
        }

        App::where('fskey', $fskey)->delete();

        return $this->deleteSuccess();
    }
}
