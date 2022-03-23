<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Account;
use App\Models\Config;
use App\Models\Plugin;
use App\Models\SessionKey;

class DashboardController extends Controller
{
    public function show()
    {
        $news = \Cache::remember('news', 86400, function () {
            $newUrl = config('FsConfig.news_url');
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', $newUrl);
            $news = json_decode($response->getBody(), true);

            return $news;
        });
        $news = collect($news)->where('langTag', \App::getLocale())->first();

        $currentVersion = json_decode(file_get_contents(base_path('fresns.json')), true);

        $version = \Cache::remember('version', 3600, function () {
            try {
                $upgradeUrl = config('FsConfig.version_url');
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', $upgradeUrl);
                $version = json_decode($response->getBody(), true);
            } catch (\Exception $e) {
                $version = [];
            }

            return $version;
        });

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

        return view('FsView::dashboard.index', compact('news', 'params', 'keyCount', 'adminCount', 'plugins', 'currentVersion', 'version'));
    }
}
