<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console;

use App\Helpers\CacheHelper;
use App\Models\Config;
use App\Models\Plugin;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $cacheKey = 'fresns_crontab_items';
        $cacheTag = 'fresnsSystems';

        $cronArr = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($cronArr)) {
            $cronConfig = Config::where('item_key', 'crontab_items')->first();

            $cronArr = $cronConfig?->item_value ?? [];

            CacheHelper::put($cronArr, $cacheKey, $cacheTag);
        }

        foreach ($cronArr as $cron) {
            $plugin = Plugin::where('unikey', $cron['unikey'])->isEnable()->first();

            if (empty($plugin)) {
                continue;
            }

            $schedule->call(function () use ($cron) {
                logger("schedule: {$cron['unikey']} - {$cron['cmdWord']}");

                \FresnsCmdWord::plugin($cron['unikey'])->{$cron['cmdWord']}();
            })->cron($cron['cronTableFormat']);
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        // require base_path('routes/console.php');
    }

    public function has($command)
    {
        return $this->getArtisan()->has($command);
    }
}
