<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\AppHelper;
use App\Helpers\CacheHelper;
use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\Comment;
use App\Models\Config;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\Plugin;
use App\Models\Post;
use App\Models\SessionKey;
use App\Models\User;
use App\Utilities\AppUtility;
use App\Utilities\CommandUtility;
use Illuminate\Support\Facades\App;

class DashboardController extends Controller
{
    public function show()
    {
        $overview = [
            'accountCount' => Account::count(),
            'userCount' => User::count(),
            'groupCount' => Group::count(),
            'hashtagCount' => Hashtag::count(),
            'postCount' => Post::count(),
            'commentCount' => Comment::count(),
            'keyCount' => SessionKey::count(),
            'adminCount' => Account::ofAdmin()->count(),
        ];

        $cacheKey = 'fresns_news';
        $cacheTag = 'fresnsSystems';

        $news = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($news)) {
            try {
                $newUrl = AppUtility::BASE_URL.'/v2/news.json';
                $client = new \GuzzleHttp\Client(['verify' => false]);
                $response = $client->request('GET', $newUrl);
                $news = json_decode($response->getBody(), true);
            } catch (\Exception $e) {
                $news = [];
            }

            CacheHelper::put($news, $cacheKey, $cacheTag, 5, now()->addHours(3));
        }

        $newsList = [];
        if ($news) {
            $newsData = collect($news)->where('langTag', App::getLocale())->first();
            $defaultNewsData = collect($news)->where('langTag', config('app.locale'))->first();
            $newsList = $newsData['news'] ?? $defaultNewsData['news'] ?? [];
        }

        $currentVersion = AppUtility::currentVersion();
        $newVersion = AppUtility::newVersion();
        $checkVersion = AppUtility::checkVersion();

        $plugins = Plugin::all();
        $pluginUpgradeCount = Plugin::where('is_upgrade', 1)->count();

        $systemInfo = AppHelper::getSystemInfo();
        $databaseInfo = AppHelper::getDatabaseInfo();
        $timezones = DateHelper::fresnsDatabaseTimezoneNames();

        return view('FsView::dashboard.index', compact('overview', 'pluginUpgradeCount', 'newsList', 'plugins', 'currentVersion', 'newVersion', 'checkVersion', 'systemInfo', 'databaseInfo', 'timezones'));
    }

    public function composerDiagnose()
    {
        $diagnose = CommandUtility::getComposerProcess(['diagnose'])->run()->getOutput();

        return $diagnose;
    }

    public function composerConfigInfo()
    {
        $configInfo = CommandUtility::getComposerProcess(['config', '-g', '-l'])->run()->getOutput();

        return $configInfo;
    }

    // events
    public function eventList()
    {
        $pluginUpgradeCount = Plugin::where('is_upgrade', 1)->count();

        // config keys
        $configKeys = [
            'subscribe_items',
            'crontab_items',
        ];
        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $subscribeList = $params['subscribe_items'];
        $crontabList = $params['crontab_items'];

        return view('FsView::dashboard.events', compact('pluginUpgradeCount', 'subscribeList', 'crontabList'));
    }
}
