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
use Illuminate\Support\Facades\Http;

class FresnsExtensionRequire extends Command
{
    protected $signature = 'fresns:require {unikey}';

    protected $description = 'require fresns extensions';

    public function getPluginFromMarket()
    {
        return Http::market()->get('/api/extensions/v1/download', [
            'unikey' => $this->argument('unikey'),
        ]);
    }

    public function handle()
    {
        AppUtility::macroMarketHeader();

        // request market api
        $pluginResponse = $this->getPluginFromMarket();

        if ($pluginResponse->failed()) {
            $this->error('Error: request failed (host or api)');

            return;
        }

        if ($pluginResponse->json('code') !== 0) {
            $this->error($pluginResponse->json('message'));

            return;
        }

        // get install file (zip)
        $zipBall = $pluginResponse->json('data.zipBall');

        $filename = uniqid().'.'.pathinfo($pluginResponse->json('data.zipBall'), PATHINFO_EXTENSION);

        // get file
        $zipBallResponse = Http::get($zipBall);

        if ($zipBallResponse->failed()) {
            $this->error('Error: file download failed');

            return;
        }

        // save file
        file_put_contents($filepath = storage_path("extensions/$filename"), $zipBallResponse->body());

        // get install command
        $command = match ($pluginResponse->json('data.installType')) {
            default => 'plugin:install',
            'theme' => 'theme:install',
        };

        // install command
        $this->call($command, [
            'path' => $filepath,
            '--force' => true,
        ]);

        // Update the upgrade_code field of the plugin table
        Plugin::where('unikey', $pluginResponse->json('data.unikey'))->update([
            'upgrade_code' => $pluginResponse->json('data.upgradeCode'),
        ]);

        return 0;
    }
}
