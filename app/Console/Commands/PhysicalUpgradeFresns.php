<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands;

use App\Helpers\AppHelper;
use App\Models\Plugin;
use App\Utilities\AppUtility;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PhysicalUpgradeFresns extends Command
{
    protected $signature = 'fresns:physical-upgrade';

    protected $description = 'physical upgrade fresns';

    public function __construct()
    {
        parent::__construct();
    }

    // execute the console command
    public function handle()
    {
        Cache::put('physicalUpgrading', 1);

        // Check if an upgrade is needed
        $checkVersion = AppUtility::checkVersion();
        if (! $checkVersion) {
            Cache::forget('physicalUpgrading');

            return $this->info('No new version, Already the latest version of Fresns.');
        }

        try {
            AppUtility::executeUpgradeCommand();
            $this->pluginPublish();
            $this->pluginComposerInstall();
            $this->pluginEnable();
            $this->upgradeFinish();
        } catch (\Exception $e) {
            logger($e->getMessage());
            $this->info($e->getMessage());
        }

        $this->clear();

        return Command::SUCCESS;
    }

    // output artisan info
    public function updateOutput($content = '')
    {
        $this->info($content);
        $output = cache('physicalUpgradeOutput');
        $output .= $content;

        return Cache::put('physicalUpgradeOutput', $output);
    }

    // step 1: execute the version command
    // try AppUtility executeUpgradeCommand()

    // step 2: publish plugins or themes
    public function pluginPublish()
    {
        $plugins = Plugin::all();
        $plugins->map(function ($plugin) {
            if ($plugin->type == 4) {
                \Artisan::call('theme:publish', ['plugin' => $plugin->unikey]);
            } else {
                \Artisan::call('plugin:publish', ['plugin' => $plugin->unikey]);
            }
            $this->updateOutput(\Artisan::output());
        });

        return true;
    }

    // step 3: composer all plugins
    public function pluginComposerInstall()
    {
        \Artisan::call('plugin:composer-install');
        $this->updateOutput(\Artisan::output());

        return true;
    }

    // step 4: activate plugin
    public function pluginEnable()
    {
        $plugins = Plugin::where('is_enable', 1)->get();
        $plugins->map(function ($plugin) {
            \Artisan::call('plugin:activate', ['plugin' => $plugin->unikey]);
            $this->updateOutput(\Artisan::output());
        });

        return true;
    }

    // step 5: edit fresns version info
    public function upgradeFinish(): bool
    {
        $newVersion = AppHelper::VERSION;
        $newVersionInt = AppHelper::VERSION_INT;

        AppUtility::editVersion($newVersion, $newVersionInt);

        return true;
    }

    // step 6: clear cache
    public function clear()
    {
        logger('upgrade:clear');

        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');

        $this->updateOutput(\Artisan::output());
    }
}
