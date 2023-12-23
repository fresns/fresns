<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\AppHelper;
use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\App;
use App\Models\Comment;
use App\Models\Config;
use App\Models\Geotag;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\SessionKey;
use App\Models\User;
use App\Utilities\AppUtility;
use App\Utilities\CommandUtility;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function show()
    {
        $currentVersion = AppUtility::currentVersion();

        $adminCount = Account::ofAdmin()->count();
        $keyCount = SessionKey::count();

        $apps = App::all();
        $upgradeCount = App::where('is_upgrade', true)->count();

        $systemInfo = AppHelper::getSystemInfo();
        $databaseInfo = AppHelper::getDatabaseInfo();
        $timezones = DateHelper::fresnsDatabaseTimezoneNames();

        return view('FsView::dashboard.index', compact('currentVersion', 'adminCount', 'keyCount', 'apps', 'upgradeCount', 'systemInfo', 'databaseInfo', 'timezones'));
    }

    public function dashboardData(Request $request)
    {
        $data = match ($request->type) {
            'news' => AppUtility::fresnsNews(),
            'checkVersion' => AppUtility::checkVersion(),
            'accountCount' => Account::count(),
            'userCount' => User::count(),
            'groupCount' => Group::count(),
            'hashtagCount' => Hashtag::count(),
            'geotagCount' => Geotag::count(),
            'postCount' => Post::count(),
            'commentCount' => Comment::count(),
        };

        return response()->json($data);
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
        $upgradeCount = App::where('is_upgrade', true)->count();

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

        return view('FsView::dashboard.events', compact('upgradeCount', 'subscribeList', 'crontabList'));
    }
}
