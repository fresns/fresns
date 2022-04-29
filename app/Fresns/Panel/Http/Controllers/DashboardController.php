<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\AppHelper;
use App\Helpers\DateHelper;
use App\Helpers\InteractiveHelper;
use App\Models\Account;
use App\Models\Config;
use App\Models\Plugin;
use App\Models\SessionKey;
use App\Utilities\AppUtility;
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

        $systemInfo = AppHelper::getSystemInfo();
        $databaseInfo = AppHelper::getMySqlInfo();
        $timezones = DateHelper::fresnsSqlTimezoneNames();

        return view('FsView::dashboard.index', compact('overview', 'newsList', 'keyCount', 'adminCount', 'plugins', 'currentVersion', 'newVersion', 'checkVersion', 'systemInfo', 'databaseInfo', 'timezones'));
    }

    /**
     * @return RedirectResponse
     */
    public function cacheClear(): RedirectResponse
    {
        \Artisan::call('view:cache');
        \Artisan::call('cache:clear');
        \Artisan::call('config:cache');

        return back()->with('success', 'ok');
    }
}
