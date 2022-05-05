<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\AppHelper;
use App\Helpers\ConfigHelper;
use App\Models\Plugin;
use App\Utilities\AppUtility;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\PhpExecutableFinder;

class UpgradeController extends Controller
{
    public function show()
    {
        $currentVersion = AppUtility::currentVersion();
        $newVersion = AppUtility::newVersion();
        $checkVersion = AppUtility::checkVersion();
        $appVersion = AppHelper::VERSION;
        $versionCheckTime = ConfigHelper::fresnsConfigByItemKey('check_version_datetime');

        $upgradeStep = cache('upgradeStep');
        $physicalUpgrading = cache('physicalUpgrading');

        $steps = [
            1 => __('FsLang::tips.upgrade_step_1'),
            2 => __('FsLang::tips.upgrade_step_2'),
            3 => __('FsLang::tips.upgrade_step_3'),
            4 => __('FsLang::tips.upgrade_step_4'),
            5 => __('FsLang::tips.upgrade_step_5'),
            6 => __('FsLang::tips.upgrade_step_6'),
        ];

        if ($upgradeStep && cache('currentVersion')) {
            $currentVersion = cache('currentVersion');
        }

        $pluginUpgradeCount = Plugin::where('is_upgrade', 1)->count();
        $pluginsData = Plugin::type(1)->where('is_upgrade', 1)->get();
        $appsData = Plugin::type(2)->where('is_upgrade', 1)->get();
        $enginesData = Plugin::type(3)->where('is_upgrade', 1)->get();
        $themesData = Plugin::type(4)->where('is_upgrade', 1)->get();

        return view('FsView::dashboard.upgrade', compact('currentVersion', 'newVersion', 'checkVersion', 'appVersion', 'versionCheckTime', 'upgradeStep', 'steps', 'physicalUpgrading', 'pluginUpgradeCount', 'pluginsData', 'appsData', 'enginesData', 'themesData'));
    }

    public function checkFresnsVersion()
    {
        Cache::forget('currentVersion');
        Cache::forget('newVersion');

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkExtensionsVersion();

        if ($fresnsResp->isSuccessResponse()) {
            return $this->requestSuccess();
        }

        return back()->with('failure', $fresnsResp->getMessage());
    }

    public function upgradeInfo()
    {
        return response()->json([
            'upgrade_step' => cache('upgradeStep'),
        ]);
    }

    public function upgrade()
    {
        $phpPath = (new PhpExecutableFinder)->find();
        if (! $phpPath) {
            abort(403, 'php command not found');
        }

        // If the upgrade is already in progress, the upgrade button is not displayed
        if (cache('upgradeStep')) {
            return $this->successResponse('upgrade');
        }

        \Cache::put('upgradeStep', 1);

        passthru($phpPath.' '.base_path('artisan').' fresns:upgrade > /dev/null &');

        return $this->successResponse('upgrade');
    }

    public function physicalUpgrade()
    {
        $phpPath = (new PhpExecutableFinder)->find();
        if (! $phpPath) {
            abort(403, 'php command not found');
        }

        // If the upgrade is already in progress, the upgrade button is not displayed
        if (cache('physicalUpgrading')) {
            return $this->successResponse('upgrade');
        }
        \Cache::put('physicalUpgrading', 1);

        passthru($phpPath.' '.base_path('artisan').' fresns:physical-upgrade > /dev/null &');

        return $this->successResponse('upgrade');
    }

    public function physicalUpgradeInfo()
    {
        return response()->json([
            'upgradeContent' => cache('physicalUpgradeOutput'),
            'physicalUpgrading' => cache('physicalUpgrading'),
        ]);
    }
}
