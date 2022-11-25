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

class Upgrade3Command extends Command
{
    protected $signature = 'fresns:upgrade-3';

    protected $description = 'upgrade to 3';

    public function __construct()
    {
        parent::__construct();
    }

    // execute the console command
    public function handle()
    {
        $this->updateData();
        $this->composerInstall();

        return Command::SUCCESS;
    }

    // update data
    public function updateData()
    {
        // modify cookie prefix
        $cookiePrefix = Config::where('item_key', 'engine_cookie_prefix')->first();

        if (! $cookiePrefix) {
            $engineService = Config::where('item_key', 'engine_service')->first();

            if ($engineService) {
                $engineService->item_key = 'engine_cookie_prefix';
                $engineService->item_value = 'fresns_';
                $engineService->item_type = 'string';
                $engineService->item_tag = 'websites';
                $engineService->is_multilingual = 0;
                $engineService->is_custom = 0;
                $engineService->is_api = 1;
                $engineService->save();
            } else {
                $newConfig = new Config;
                $newConfig->item_key = 'engine_cookie_prefix';
                $newConfig->item_value = 'fresns_';
                $newConfig->item_type = 'string';
                $newConfig->item_tag = 'websites';
                $newConfig->is_multilingual = 0;
                $newConfig->is_custom = 0;
                $newConfig->is_api = 1;
                $newConfig->save();
            }
        }

        // modify account cookies status
        $accountCookieStatus = Config::where('item_key', 'account_cookie_status')->first();
        if ($accountCookieStatus) {
            $accountCookieStatus->item_key = 'account_cookies_status';
            $accountCookieStatus->save();
        }

        // modify account cookies policies
        $accountCookie = Config::where('item_key', 'account_cookie')->first();
        if ($accountCookie) {
            $accountCookie->item_key = 'account_cookies';
            $accountCookie->save();

            $langContent = Language::where('table_name', 'configs')->where('table_column', 'item_value')->where('table_key', 'account_cookie')->get();
            foreach ($langContent as $lang) {
                $lang->table_key = 'account_cookies';
                $lang->save();
            }
        }

        // modify lang pack key
        $languagePack = Config::where('item_key', 'language_pack')->first();
        if ($languagePack) {
            $packData = $languagePack->item_value;

            $newPackData = ArrUtility::editValue($packData, 'name', 'accountPoliciesCookie', 'accountPoliciesCookies');
            $newPackData = ArrUtility::editValue($newPackData, 'name', 'accountRestore', 'accountRecallDelete');

            $addPackKeys = [
                [
                    "name" => "executionDate",
                    "canDelete" => false
                ],
                [
                    "name" => "accountApplyDelete",
                    "canDelete" => false
                ],
                [
                    "name" => "accountWaitDelete",
                    "canDelete" => false
                ],
            ];

            $newData = array_merge($newPackData, $addPackKeys);

            $languagePack->item_value = json_encode($newData);
            $languagePack->save();
        }

        // modify lang key
        $langPackContents = Language::where('table_name', 'configs')->where('table_column', 'item_value')->where('table_key', 'language_pack_contents')->get();
        foreach ($langPackContents as $packContent) {
            $content = (object) json_decode($packContent->lang_content, true);

            $newContent = ArrUtility::editKey($content, 'accountPoliciesCookie', 'accountPoliciesCookies');
            $newContent = ArrUtility::editKey($newContent, 'accountRestore', 'accountRecallDelete');

            $langAddContent = match ($packContent->lang_tag) {
                'en' => [
                    'executionDate' => 'Execution Date',
                    'accountApplyDelete' => 'Apply Delete Account',
                    'accountWaitDelete' => 'Delete account wait execution',
                    'accountRecallDelete' => 'Recall Delete Account',
                ],
                'zh-Hans' => [
                    'executionDate' => '执行日期',
                    'accountDelete' => '注销账号',
                    'accountApplyDelete' => '申请注销',
                    'accountWaitDelete' => '账号注销等待执行中',
                    'accountRecallDelete' => '撤销注销',
                ],
                'zh-Hant' => [
                    'executionDate' => '執行日期',
                    'accountDelete' => '註銷賬號',
                    'accountApplyDelete' => '申請註銷',
                    'accountWaitDelete' => '賬號註銷等待執行中',
                    'accountRecallDelete' => '撤銷註銷',
                ],
            };

            $langNewContent = (object) array_merge((array) $newContent, (array) $langAddContent);

            $packContent->lang_content = json_encode($langNewContent);
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
