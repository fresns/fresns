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

class Upgrade6Command extends Command
{
    protected $signature = 'fresns:upgrade-6';

    protected $description = 'upgrade to fresns v2.0.0-beta.6';

    public function __construct()
    {
        parent::__construct();
    }

    // execute the console command
    public function handle()
    {
        logger('upgrade:fresns-6 composerInstall');
        $this->composerInstall();

        logger('upgrade:fresns-6 updateData');
        $this->updateData();

        return Command::SUCCESS;
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

    // update data
    public function updateData(): bool
    {
        // modify lang pack key
        $languagePack = Config::where('item_key', 'language_pack')->first();
        if ($languagePack) {
            $packData = $languagePack->item_value;

            $addPackKeys = [
                [
                    'name' => 'automatic',
                    'canDelete' => false,
                ],
                [
                    'name' => 'discover',
                    'canDelete' => false,
                ],
                [
                    'name' => 'darkMode',
                    'canDelete' => false,
                ],
                [
                    'name' => 'admin',
                    'canDelete' => false,
                ],
                [
                    'name' => 'groupAdmin',
                    'canDelete' => false,
                ],
                [
                    'name' => 'userMy',
                    'canDelete' => false,
                ],
                [
                    'name' => 'userMe',
                    'canDelete' => false,
                ],
                [
                    'name' => 'contentLatestCommentTime',
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
                    'automatic' => 'Automatic',
                    'discover' => 'Discover',
                    'darkMode' => 'Dark Mode',
                    'admin' => 'Administrator',
                    'groupAdmin' => 'Administrator',
                    'userMy' => 'My',
                    'userMe' => 'Me',
                    'contentLatestCommentTime' => 'Latest Comment Time',
                ],
                'zh-Hans' => [
                    'automatic' => '自动',
                    'discover' => '发现',
                    'darkMode' => '深色模式',
                    'admin' => '管理员',
                    'groupAdmin' => '小组管理员',
                    'userMy' => '我的',
                    'userMe' => '我',
                    'contentLatestCommentTime' => '最新评论时间',
                ],
                'zh-Hant' => [
                    'automatic' => '自動',
                    'discover' => '發現',
                    'darkMode' => '深色模式',
                    'admin' => '管理員',
                    'groupAdmin' => '社團管理員',
                    'userMy' => '我的',
                    'userMe' => '我',
                    'contentLatestCommentTime' => '最新留言時間',
                ],
            };

            $langNewContent = (object) array_merge((array) $content, (array) $langAddContent);

            $packContent->lang_content = json_encode($langNewContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
            $packContent->save();
        }

        return true;
    }
}
