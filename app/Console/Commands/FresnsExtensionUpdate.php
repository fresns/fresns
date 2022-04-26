<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands;

use App\Models\Plugin;
use Illuminate\Support\Facades\Http;

class FresnsExtensionUpdate extends FresnsExtensionRequire
{
    protected $signature = 'fresns:update {unikey}';

    protected $description = 'update fresns extensions';

    public function handle()
    {
        return parent::handle();
    }

    public function getPlugin()
    {
        $unikey = $this->argument('unikey');

        return Plugin::where('unikey', $unikey)->firstOrFail();
    }

    public function getPluginFromMarket()
    {
        $plugin = $this->getPlugin();

        return Http::market()->get('/api/extensions/v1/download', [
            'unikey' => $plugin->unikey,
            'version' => $plugin->version,
            'upgradeCode' => $plugin->upgrade_code,
        ]);
    }
}
