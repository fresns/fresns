<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands;

use App\Models\Plugin;
use App\Utilities\AppUtility;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;

class PhysicalUpgradeFresns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fresns:physical-upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'physical upgrade fresns';

    protected $currentVersion;

    protected $newVersion;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function updateOutput($content = '')
    {
        $this->info($content);
        $output = cache('physicalUpgradeOutput');
        $output .= $content;

        return Cache::put('physicalUpgradeOutput', $output);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Cache::put('physicalUpgrading', 1);
        // Check if an upgrade is needed
        if (! $this->checkVersion()) {
            Cache::forget('physicalUpgrading');

            return $this->info('Already the latest version of Fresns');
        }

        try {
            $this->upgradeCommand();
            $this->pluginPublish();
            $this->pluginComposerInstall();
            $this->pluginEnable();

            $this->upgradeFinish();
        } catch (\Exception $e) {
            $this->info($e->getMessage());
        }

        $this->clear();

        return Command::SUCCESS;
    }

    public function pluginPublish()
    {
        $plugins = Plugin::whereIn('type', [1, 4])->get();
        $plugins->map(function ($plugin) {
            if ($plugin->type == 1) {
                \Artisan::call('plugin:publish', ['plugin' => $plugin->unikey]);
            } else {
                \Artisan::call('theme:publish', ['plugin' => $plugin->unikey]);
            }
            $this->updateOutput(\Artisan::output());
        });

        return true;
    }

    public function pluginComposerInstall()
    {
        \Artisan::call('plugin:composer-install');
        $this->updateOutput(\Artisan::output());

        return true;
    }

    public function pluginEnable()
    {
        $plugins = Plugin::where('is_enable', 1)->get();
        $plugins->map(function ($plugin) {
            \Artisan::call('plugin:activate', ['plugin' => $plugin->unikey]);
            $this->updateOutput(\Artisan::output());
        });

        return true;
    }

    public function checkVersion(): bool
    {
        $this->newVersion = AppUtility::newVersion();
        $this->currentVersion = AppUtility::currentVersion();

        if (($this->currentVersion['versionInt'] ?? 0) >= ($this->newVersion['versionInt'] ?? 0)) {
            return false;
        }

        return true;
    }

    public function updateStep(string $step): bool
    {
        // upgrade step
        return Cache::put('physicalUpgradeStep', $step);
    }

    public function clear()
    {
        logger('upgrade:clear');

        \Artisan::call('config:clear');
        $this->updateOutput(\Artisan::output());
        \Artisan::call('cache:clear');
    }

    public function upgradeCommand()
    {
        logger('upgrade:upgrade command');

        $currentVersionInt = $this->currentVersion['versionInt'] ?? 0;
        $newVersionInt = $this->newVersion['versionInt'] ?? 0;

        if (! $currentVersionInt || ! $newVersionInt) {
            return false;
        }

        $versionInt = $currentVersionInt;
        while ($versionInt < $newVersionInt) {
            $versionInt++;
            $command = 'fresns:upgrade-'.$versionInt;
            if (\Artisan::has($command)) {
                $this->call($command);
            }
        }

        \Artisan::call('migrate');
        $this->updateOutput(\Artisan::output());

        return true;
    }

    public function upgradeFinish(): bool
    {
        $version = $this->newVersion['version'];
        $versionInt = $this->newVersion['versionInt'];

        AppUtility::editVersion($version, $versionInt);

        return true;
    }
}
