<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\Config;
use App\Models\Plugin;
use App\Models\SessionKey;
use App\Utilities\AppUtility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

        $systemInfo['server'] = php_uname('s').' '.php_uname('r');
        $systemInfo['web'] = $_SERVER['SERVER_SOFTWARE'];

        $phpInfo['version'] = 'PHP '.PHP_VERSION;
        $phpInfo['uploadMaxFileSize'] = ini_get('upload_max_filesize');
        $systemInfo['php'] = $phpInfo;

        $mySqlVersion = 'version()';
        $databaseInfo['version'] = 'MySQL '.DB::select('select version()')[0]->$mySqlVersion;
        $databaseInfo['timezone'] = 'UTC'.DateHelper::fresnsSqlTimezone();
        $databaseInfo['timezoneFromEnv'] = config('app.timezone');
        $mySqlCollation = 'Value';
        $databaseInfo['collation'] = DB::select('show variables like "collation%"')[1]->$mySqlCollation;
        $mySqlSize = 'Size';
        // Size (GB)
        // $databaseInfo['size'] = DB::select('SELECT table_schema AS "Database", SUM(data_length + index_length) / 1024 / 1024 / 1024 AS "Size" FROM information_schema.TABLES GROUP BY table_schema')[1]->$mySqlSize;
        $databaseInfo['size'] = round(DB::select('SELECT table_schema AS "Database", SUM(data_length + index_length) / 1024 / 1024 AS "Size" FROM information_schema.TABLES GROUP BY table_schema')[1]->$mySqlSize, 2).' MB';
        $systemInfo['database'] = $databaseInfo;

        $systemInfo[] = $systemInfo;

        return view('FsView::dashboard.index', compact('newsList', 'params', 'keyCount', 'adminCount', 'plugins', 'currentVersion', 'newVersion', 'systemInfo'));
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
