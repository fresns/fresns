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
        Cache::put('physicalUpgradeOutput', '');

        // Check if an upgrade is needed
        $checkVersion = AppUtility::checkVersion();
        if (! $checkVersion) {
            Cache::forget('physicalUpgrading');

            return $this->info('No new version, Already the latest version of Fresns.');
        }

        try {
            $this->updateOutput('Step 1/5: update data'."\n");
            AppUtility::executeUpgradeCommand();

            $this->updateOutput("\n".'Step 2/5: install plugins composer'."\n");
            $this->pluginComposerInstall();

            $this->updateOutput("\n".'Step 3/5: publish and activate plugins or themes'."\n");
            $this->pluginPublishAndActivate();

            $this->updateOutput("\n".'Step 4/5: update version'."\n");
            $this->upgradeFinish();

            $this->updateOutput("\n".'Step 5/5: clear cache'."\n");
            $this->clear();
        } catch (\Exception $e) {
            logger($e->getMessage());
            $this->info($e->getMessage());
        }

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

    // step 2: composer all plugins
    public function pluginComposerInstall()
    {
        try {
            \Artisan::call('plugin:composer-update');
            $this->updateOutput(\Artisan::output());
        } catch (\Exception $e) {
            logger($e->getMessage());
            $this->info($e->getMessage());
        }

        return true;
    }

    // step 3: publish and activate plugins or themes
    public function pluginPublishAndActivate()
    {
        $plugins = Plugin::all();

        $plugins->map(function ($plugin) {
            try {
                if ($plugin->type == 4) {
                    \Artisan::call('theme:publish', ['plugin' => $plugin->unikey]);
                    $this->updateOutput(\Artisan::output());

                    if ($plugin->is_enable) {
                        \Artisan::call('theme:activate', ['plugin' => $plugin->unikey]);
                        $this->updateOutput(\Artisan::output());
                    }
                } else {
                    \Artisan::call('plugin:publish', ['plugin' => $plugin->unikey]);
                    $this->updateOutput(\Artisan::output());

                    if ($plugin->is_enable) {
                        \Artisan::call('plugin:activate', ['plugin' => $plugin->unikey]);
                        $this->updateOutput(\Artisan::output());
                    }
                }
            } catch (\Exception $e) {
                logger($e->getMessage());
                $this->info($e->getMessage());
            }
        });

        return true;
    }

    // step 4: edit fresns version info
    public function upgradeFinish(): bool
    {
        $newVersion = AppHelper::VERSION;
        $newVersionInt = AppHelper::VERSION_INT;

        AppUtility::editVersion($newVersion, $newVersionInt);

        return true;
    }

    // step 5: clear cache
    public function clear()
    {
        logger('upgrade:clear');

        \Artisan::call('config:clear');
        $this->updateOutput(\Artisan::output());

        $output = cache('physicalUpgradeOutput');
        \Artisan::call('cache:clear');

        $this->updateOutput($output.\Artisan::output());

        $this->updateOutput("\n".__('FsLang::tips.upgradeSuccess'));
    }
}
