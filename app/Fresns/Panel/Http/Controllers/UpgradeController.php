<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\AppHelper;
use App\Helpers\CacheHelper;
use App\Models\Config;
use App\Models\Plugin;
use App\Utilities\AppUtility;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\PhpExecutableFinder;

class UpgradeController extends Controller
{
    // view page
    public function show()
    {
        $currentVersion = AppUtility::currentVersion();
        $newVersion = AppUtility::newVersion();
        $checkVersion = AppUtility::checkVersion();
        $appVersion = AppHelper::VERSION;
        $versionCheckTime = Config::where('item_key', 'check_version_datetime')->first()?->item_value;

        $pluginsData = Plugin::type(1)->where('is_upgrade', 1)->get();
        $appsData = Plugin::type(2)->where('is_upgrade', 1)->get();
        $enginesData = Plugin::type(3)->where('is_upgrade', 1)->get();
        $themesData = Plugin::type(4)->where('is_upgrade', 1)->get();
        $pluginUpgradeCount = Plugin::where('is_upgrade', 1)->count();

        $autoUpgradeSteps = [
            1 => __('FsLang::tips.auto_upgrade_step_1'),
            2 => __('FsLang::tips.auto_upgrade_step_2'),
            3 => __('FsLang::tips.auto_upgrade_step_3'),
            4 => __('FsLang::tips.auto_upgrade_step_4'),
            5 => __('FsLang::tips.auto_upgrade_step_5'),
            6 => __('FsLang::tips.auto_upgrade_step_6'),
        ];

        $manualUpgradeSteps = [
            1 => __('FsLang::tips.manual_upgrade_step_1'),
            2 => __('FsLang::tips.manual_upgrade_step_2'),
            3 => __('FsLang::tips.manual_upgrade_step_3'),
            4 => __('FsLang::tips.manual_upgrade_step_4'),
            5 => __('FsLang::tips.manual_upgrade_step_5'),
            6 => __('FsLang::tips.manual_upgrade_step_6'),
            7 => __('FsLang::tips.manual_upgrade_step_7'),
        ];

        $autoUpgradeStepInt = cache('autoUpgradeStep');
        $manualUpgradeStepInt = cache('manualUpgradeStep');

        if ($autoUpgradeStepInt == 6 || $manualUpgradeStepInt == 7) {
            $autoUpgradeStepInt = null;
            $manualUpgradeStepInt = null;
        }

        return view('FsView::dashboard.upgrade', compact(
            'currentVersion',
            'newVersion',
            'checkVersion',
            'appVersion',
            'versionCheckTime',
            'pluginsData',
            'appsData',
            'enginesData',
            'themesData',
            'pluginUpgradeCount',
            'autoUpgradeSteps',
            'autoUpgradeStepInt',
            'manualUpgradeSteps',
            'manualUpgradeStepInt',
        ));
    }

    // check fresns and extensions version
    public function checkFresnsVersion()
    {
        CacheHelper::forgetFresnsKeys([
            'fresns_current_version',
            'fresns_new_version',
        ], 'fresnsSystems');

        Cache::forget('autoUpgradeStep');
        Cache::forget('manualUpgradeStep');

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkExtensionsVersion();

        if ($fresnsResp->isSuccessResponse()) {
            return $this->requestSuccess();
        }

        return back()->with('failure', $fresnsResp->getMessage());
    }

    // auto upgrade fresns
    public function autoUpgrade()
    {
        $phpPath = (new PhpExecutableFinder)->find();
        if (! $phpPath) {
            abort(403, 'php command not found');
        }

        // If the upgrade is already in progress, the upgrade button is not displayed
        if (cache('autoUpgradeStep')) {
            return $this->successResponse('upgrade');
        }

        \Cache::put('autoUpgradeStep', 1);

        passthru($phpPath.' '.base_path('artisan').' fresns:upgrade > /dev/null &');

        return $this->successResponse('upgrade');
    }

    // manual upgrade fresns
    public function manualUpgrade()
    {
        $phpPath = (new PhpExecutableFinder)->find();
        if (! $phpPath) {
            abort(403, 'php command not found');
        }

        // If the upgrade is already in progress, the upgrade button is not displayed
        if (cache('manualUpgradeStep')) {
            return $this->successResponse('upgrade');
        }
        \Cache::put('manualUpgradeStep', 1);

        passthru($phpPath.' '.base_path('artisan').' fresns:manual-upgrade > /dev/null &');

        return $this->successResponse('upgrade');
    }

    // get upgrade step info
    public function upgradeInfo()
    {
        $upgradeInfo = [
            'autoUpgradeStep' => cache('autoUpgradeStep'),
            'autoUpgradeTip' => cache('autoUpgradeTip') ?? '',
            'manualUpgradeStep' => cache('manualUpgradeStep'),
            'manualUpgradeTip' => cache('manualUpgradeTip') ?? '',
        ];

        return response()->json($upgradeInfo);
    }
}
