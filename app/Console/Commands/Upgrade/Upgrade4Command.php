<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands\Upgrade;

use App\Models\Config;
use App\Models\Language;
use App\Utilities\ArrUtility;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class Upgrade4Command extends Command
{
    protected $signature = 'fresns:upgrade-4';

    protected $description = 'upgrade to fresns v2.0.0-beta.4';

    public function __construct()
    {
        parent::__construct();
    }

    // execute the console command
    public function handle()
    {
        $this->updateData();
        $this->call('migrate', ['--force' => true]);
        $this->composerInstall();

        return Command::SUCCESS;
    }

    // update data
    public function updateData(): bool
    {
        // add config key
        $cacheDatetime = Config::where('item_key', 'cache_datetime')->first();
        if (! $cacheDatetime) {
            $newConfig = new Config;
            $newConfig->item_key = 'cache_datetime';
            $newConfig->item_value = null;
            $newConfig->item_type = 'string';
            $newConfig->item_tag = 'systems';
            $newConfig->is_multilingual = 0;
            $newConfig->is_custom = 0;
            $newConfig->is_api = 1;
            $newConfig->save();
        }

        // modify config tag
        $configs = Config::where('item_tag', 'interactives')->get();
        foreach ($configs as $config) {
            $config->item_tag = 'interactions';
            $config->save();
        }

        // modify lang pack key
        $languagePack = Config::where('item_key', 'language_pack')->first();
        if ($languagePack) {
            $packData = $languagePack->item_value;

            $newPackData = ArrUtility::editValue($packData, 'name', 'notificationEmpty', 'listEmpty');

            $languagePack->item_value = $newPackData;
            $languagePack->save();
        }

        // modify lang key
        $langPackContents = Language::where('table_name', 'configs')->where('table_column', 'item_value')->where('table_key', 'language_pack_contents')->get();
        foreach ($langPackContents as $packContent) {
            $content = (object) json_decode($packContent->lang_content, true);

            $newContent = ArrUtility::editKey($content, 'notificationEmpty', 'listEmpty');

            $langAddContent = match ($packContent->lang_tag) {
                'en' => [
                    'listEmpty' => 'The list is empty, no content at the moment.',
                ],
                'zh-Hans' => [
                    'listEmpty' => '列表为空，暂无内容',
                ],
                'zh-Hant' => [
                    'listEmpty' => '列表為空，暫無內容',
                ],
            };

            $langNewContent = (object) array_merge((array) $newContent, (array) $langAddContent);

            $packContent->lang_content = json_encode($langNewContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
            $packContent->save();
        }

        return true;
    }

    // composer install
    public function composerInstall()
    {
        $composerPath = 'composer';

        if (! $this->commandExists($composerPath)) {
            $composerPath = '/usr/bin/composer';
        }

        $process = new Process([$composerPath, 'install'], base_path());
        $process->setTimeout(0);
        $process->start();

        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                $this->info("\nRead from stdout: ".$data);
            } else { // $process::ERR === $type
                $this->info("\nRead from stderr: ".$data);
            }
        }
    }

    // check composer
    public function commandExists($commandName)
    {
        ob_start();
        passthru("command -v $commandName", $code);
        ob_end_clean();

        return (0 === $code) ? true : false;
    }
}
