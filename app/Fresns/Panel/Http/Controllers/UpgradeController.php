<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use Symfony\Component\Process\PhpExecutableFinder;

class UpgradeController extends Controller
{
    public function show()
    {
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

        $upgradeStep = cache('upgradeStep');

        $steps = [
            1 => __('FsLang::panel.upgrade_step_1'),
            2 => __('FsLang::panel.upgrade_step_2'),
            3 => __('FsLang::panel.upgrade_step_3'),
            4 => __('FsLang::panel.upgrade_step_4'),
            5 => __('FsLang::panel.upgrade_step_5'),
            6 => __('FsLang::panel.upgrade_step_6'),
        ];

        if ($upgradeStep && cache('currentVersion')) {
            $currentVersion = cache('currentVersion');
        }

        return view('FsView::dashboard.upgrade', compact('currentVersion', 'version', 'upgradeStep', 'steps'));
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
        if (cache('currentVersion')) {
            return $this->successResponse('upgrade');
        }

        // Composer does not exist
        if ((null === shell_exec('command -v composer')) &&
            (null === shell_exec('command -v /usr/bin/composer'))) {
            abort(403, 'composer command not found');
        }

        \Cache::put('upgradeStep', 1);

        exec($phpPath.' '.base_path('artisan').' fresns:upgrade > /dev/null &');

        return $this->successResponse('upgrade');
    }
}
