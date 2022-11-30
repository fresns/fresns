<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console;

use App\Helpers\ConfigHelper;
use App\Models\Plugin;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

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
        // Cache::tags(['fresnsSystems'])
        $cronArr = Cache::remember('fresns_crontab_items', now()->addDays(), function () {
            return ConfigHelper::fresnsConfigByItemKey('crontab_items');
        });

        foreach ($cronArr as $cron) {
            $pluginStatus = Plugin::where('unikey', $cron['unikey'])->isEnable()->first();

            if (! empty($pluginStatus)) {
                $schedule->call(function () use ($cron) {
                    \FresnsCmdWord::plugin($cron['unikey'])->{$cron['cmdWord']}();
                })->cron($cron['cronTableFormat']);
            }
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
