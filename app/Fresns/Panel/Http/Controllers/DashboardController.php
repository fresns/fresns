<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\AppHelper;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\InteractiveHelper;
use App\Models\Account;
use App\Models\Plugin;
use App\Models\SessionKey;
use App\Utilities\AppUtility;
use App\Utilities\CommandUtility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function show()
    {
        $overview = InteractiveHelper::fresnsOverview();

        $news = Cache::remember('news', 86400, function () {
            try {
                $newUrl = AppUtility::getApiHost().'/news.json';
                $client = new \GuzzleHttp\Client(['verify' => false]);
                $response = $client->request('GET', $newUrl);
                $news = json_decode($response->getBody(), true);
            } catch (\Exception $e) {
                $news = [];
            }

            return $news;
        });

        $newsData = collect($news)->where('langTag', \App::getLocale())->first();
        $defaultNewsData = collect($news)->where('langTag', config('app.locale'))->first();
        $newsList = $newsData['news'] ?? $defaultNewsData['news'] ?? [];

        $currentVersion = AppUtility::currentVersion();
        $newVersion = AppUtility::newVersion();
        $checkVersion = AppUtility::checkVersion();

        $keyCount = SessionKey::count();
        $adminCount = Account::ofAdmin()->count();
        $plugins = Plugin::all();
        $pluginUpgradeCount = Plugin::where('is_upgrade', 1)->count();

        $systemInfo = AppHelper::getSystemInfo();
        $databaseInfo = AppHelper::getMySqlInfo();
        $timezones = DateHelper::fresnsDatabaseTimezoneNames();

        return view('FsView::dashboard.index', compact('overview', 'pluginUpgradeCount', 'newsList', 'keyCount', 'adminCount', 'plugins', 'currentVersion', 'newVersion', 'checkVersion', 'systemInfo', 'databaseInfo', 'timezones'));
    }

    public function composerDiagnose()
    {
        $diagnose = CommandUtility::getComposerProcess(['diagnose'])->run()->getOutput();

        return $diagnose;
    }

    public function composerConfigInfo()
    {
        $configInfo = CommandUtility::getComposerProcess(['config', '-g', '--list'])->run()->getOutput();

        return $configInfo;
    }

    /**
     * @return RedirectResponse
     */
    public function cacheClear(): RedirectResponse
    {
        CacheHelper::clearAllCache();

        return back()->with('success', 'ok');
    }

    public function eventList()
    {
        $pluginUpgradeCount = Plugin::where('is_upgrade', 1)->count();

        $subscribeList = ConfigHelper::fresnsConfigByItemKey('subscribe_items');
        $crontabList = ConfigHelper::fresnsConfigByItemKey('crontab_items');

        return view('FsView::dashboard.events', compact('pluginUpgradeCount', 'subscribeList', 'crontabList'));
    }
}
