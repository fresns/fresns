<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\AppHelper;
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
        $defaultNewsData = collect($news)->where('langTag', config('FsConfig.defaultLangTag'))->first();
        $newsList = $newsData['news'] ?? $defaultNewsData['news'];

        $currentVersion = AppUtility::currentVersion();
        $newVersion = AppUtility::newVersion();
        $checkVersion = AppUtility::checkVersion();

        $configKeys = [
            'accounts_count',
            'users_count',
            'groups_count',
            'hashtags_count',
            'posts_count',
            'comments_count',
        ];
        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $keyCount = SessionKey::count();
        $adminCount = Account::ofAdmin()->count();
        $plugins = Plugin::all();

        $systemInfo = AppHelper::getSystemInfo();
        $databaseInfo = AppHelper::getMySqlInfo();

        return view('FsView::dashboard.index', compact('newsList', 'params', 'keyCount', 'adminCount', 'plugins', 'currentVersion', 'newVersion', 'checkVersion', 'systemInfo', 'databaseInfo'));
    }

    /**
     * @return RedirectResponse
     */
    public function cacheClear(): RedirectResponse
    {
        Cache::clear();

        return back()->with('success', 'ok');
    }
}
