<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands\Upgrade;

use App\Models\Config;
use App\Models\Language;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class Upgrade5Command extends Command
{
    protected $signature = 'fresns:upgrade-5';

    protected $description = 'upgrade to fresns v2.0.0-beta.5';

    public function __construct()
    {
        parent::__construct();
    }

    // execute the console command
    public function handle()
    {
        $this->composerInstall();
        $this->call('migrate', ['--force' => true]);
        $this->updateData();

        return Command::SUCCESS;
    }

    // update data
    public function updateData(): bool
    {
        // modify lang pack key
        $languagePack = Config::where('item_key', 'language_pack')->first();
        if ($languagePack) {
            $packData = $languagePack->item_value;

            $addPackKeys = [
                [
                    'name' => 'home',
                    'canDelete' => false,
                ],
                [
                    'name' => 'accountPolicies',
                    'canDelete' => false,
                ],
                [
                    'name' => 'userFollowing',
                    'canDelete' => false,
                ],
                [
                    'name' => 'userUnfollow',
                    'canDelete' => false,
                ],
                [
                    'name' => 'contentActive',
                    'canDelete' => false,
                ],
            ];

            $newData = array_merge($packData, $addPackKeys);

            $languagePack->item_value = $newData;
            $languagePack->save();
        }

        // modify lang key
        $langPackContents = Language::where('table_name', 'configs')->where('table_column', 'item_value')->where('table_key', 'language_pack_contents')->get();
        foreach ($langPackContents as $packContent) {
            $content = (object) json_decode($packContent->lang_content, true);

            $langAddContent = match ($packContent->lang_tag) {
                'en' => [
                    'home' => 'Home',
                    'accountPolicies' => 'Privacy & Terms',
                    'userFollowing' => 'Following',
                    'userUnfollow' => 'Unfollow',
                    'contentActive' => 'Active',
                ],
                'zh-Hans' => [
                    'home' => '首页',
                    'accountPolicies' => '隐私权和条款',
                    'userFollowing' => '正在关注',
                    'userUnfollow' => '取消关注',
                    'contentActive' => '活跃',
                ],
                'zh-Hant' => [
                    'home' => '首頁',
                    'accountPolicies' => '私隱權和條款',
                    'userFollowing' => '正在跟隨',
                    'userUnfollow' => '取消跟隨',
                    'contentActive' => '活躍',
                ],
            };

            $langNewContent = (object) array_merge((array) $content, (array) $langAddContent);

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
