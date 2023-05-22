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
use Illuminate\Support\Facades\Cookie;
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

        $pluginsData = Plugin::type(Plugin::TYPE_PLUGIN)->where('is_upgrade', 1)->get();
        $appsData = Plugin::type(Plugin::TYPE_PANEL)->where('is_upgrade', 1)->get();
        $enginesData = Plugin::type(Plugin::TYPE_ENGINE)->where('is_upgrade', 1)->get();
        $themesData = Plugin::type(Plugin::TYPE_THEME)->where('is_upgrade', 1)->get();
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

        $autoUpgradeStepInt = Cache::get('autoUpgradeStep');
        $manualUpgradeStepInt = Cache::get('manualUpgradeStep');

        if ($autoUpgradeStepInt == 6 || $manualUpgradeStepInt == 7) {
            $autoUpgradeStepInt = null;
            $manualUpgradeStepInt = null;
        }

        $langTag = Cookie::get('panel_lang', config('app.locale'));
        $manualUpgradeGuide = AppUtility::WEBSITE_URL.'/guide/upgrade.html#manual-upgrade';
        if ($langTag == 'zh-Hans') {
            $manualUpgradeGuide = AppUtility::WEBSITE_ZH_HANS_URL.'/guide/upgrade.html#%E6%89%8B%E5%8A%A8%E5%8D%87%E7%BA%A7';
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
            'manualUpgradeGuide',
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
        if (Cache::get('autoUpgradeStep')) {
            return $this->successResponse('upgrade');
        }

        Cache::put('autoUpgradeStep', 1);

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
        if (Cache::get('manualUpgradeStep')) {
            return $this->successResponse('upgrade');
        }
        Cache::put('manualUpgradeStep', 1);

        passthru($phpPath.' '.base_path('artisan').' fresns:manual-upgrade > /dev/null &');

        return $this->successResponse('upgrade');
    }

    // get upgrade step info
    public function upgradeInfo()
    {
        $upgradeInfo = [
            'autoUpgradeStep' => Cache::get('autoUpgradeStep'),
            'autoUpgradeTip' => Cache::get('autoUpgradeTip') ?? '',
            'manualUpgradeStep' => Cache::get('manualUpgradeStep'),
            'manualUpgradeTip' => Cache::get('manualUpgradeTip') ?? '',
        ];

        return response()->json($upgradeInfo);
    }
}
