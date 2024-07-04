<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands;

use App\Models\Config;
use Illuminate\Console\Command;

class Panel extends Command
{
    protected $signature = 'fresns:panel';

    protected $description = 'View the panel information';

    public function handle()
    {
        if (\PHP_SAPI != 'cli') {
            return $this->warn('Please execute the command in the terminal.');
        }

        $panelConfigs = Config::where('item_key', 'panel_configs')->first();

        $appUrl = config('app.url');
        $loginPath = $panelConfigs?->item_value['path'] ?? 'admin';

        $this->info("App URL: {$appUrl}");
        $this->info("Login Path: {$loginPath}");
        $this->info("Panel Link: {$appUrl}/fresns/{$loginPath}");
    }
}
