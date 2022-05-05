<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands\Upgrade;

use App\Models\CodeMessage;
use App\Models\Config;
use App\Utilities\ConfigUtility;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class Upgrade3Command extends Command
{
    protected $signature = 'fresns:upgrade-3';

    protected $description = 'upgrade to 3';

    protected $codeMessages = [
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21000,
            'lang_tag' => 'en',
            'message' => 'Unconfigured plugin',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21000,
            'lang_tag' => 'en',
            'message' => 'Unconfigured plugin',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21001,
            'lang_tag' => 'en',
            'message' => 'Plugin does not exist',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21002,
            'lang_tag' => 'en',
            'message' => 'Command word does not exist',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21003,
            'lang_tag' => 'en',
            'message' => 'Command word unknown error',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21004,
            'lang_tag' => 'en',
            'message' => 'Command word not responding',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21005,
            'lang_tag' => 'en',
            'message' => 'Command word request parameter error',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21006,
            'lang_tag' => 'en',
            'message' => 'Command word execution request error',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21007,
            'lang_tag' => 'en',
            'message' => 'Command word response result is incorrect',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21008,
            'lang_tag' => 'en',
            'message' => 'Data anomalies, queries not available or data duplication',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21009,
            'lang_tag' => 'en',
            'message' => 'Execution anomalies, missing files or logging errors',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21000,
            'lang_tag' => 'zh-Hans',
            'message' => '未配置插件',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21001,
            'lang_tag' => 'zh-Hans',
            'message' => '插件不存在',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21002,
            'lang_tag' => 'zh-Hans',
            'message' => '命令字不存在',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21003,
            'lang_tag' => 'zh-Hans',
            'message' => '命令字未知错误',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21004,
            'lang_tag' => 'zh-Hans',
            'message' => '命令字无响应',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21005,
            'lang_tag' => 'zh-Hans',
            'message' => '命令字请求参数错误',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21006,
            'lang_tag' => 'zh-Hans',
            'message' => '命令字执行请求出错',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21007,
            'lang_tag' => 'zh-Hans',
            'message' => '命令字响应结果不正确',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21008,
            'lang_tag' => 'zh-Hans',
            'message' => '数据异常，查询不到或者数据重复',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21009,
            'lang_tag' => 'zh-Hans',
            'message' => '执行异常，文件丢失或者记录错误',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21000,
            'lang_tag' => 'zh-Hant',
            'message' => '未配置外掛',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21001,
            'lang_tag' => 'zh-Hant',
            'message' => '外掛不存在',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21002,
            'lang_tag' => 'zh-Hant',
            'message' => '命令字不存在',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21003,
            'lang_tag' => 'zh-Hant',
            'message' => '命令字未知錯誤',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21004,
            'lang_tag' => 'zh-Hant',
            'message' => '命令字無響應',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21005,
            'lang_tag' => 'zh-Hant',
            'message' => '命令字請求參數錯誤',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21006,
            'lang_tag' => 'zh-Hant',
            'message' => '命令字執行請求出錯',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21007,
            'lang_tag' => 'zh-Hant',
            'message' => '命令字響應結果不正確',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21008,
            'lang_tag' => 'zh-Hant',
            'message' => '資料異常，查詢不到或者資料重複',
        ],
        [
            'plugin_unikey' => 'CmdWord',
            'code' => 21009,
            'lang_tag' => 'zh-Hant',
            'message' => '執行異常，文件丟失或者記錄錯誤',
        ],
    ];

    protected function fresnsProcess(callable $callback)
    {
        foreach ($this->codeMessages as $item) {
            $callback($item);
        }
    }

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
        $this->addData();

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
    public function updateData()
    {
        $checkVersionDatetime = Config::where('item_key', 'check_version_datetime')->first();

        if ($checkVersionDatetime) {
            return true;
        }

        $configDb = Config::where('item_key', 'citys')->first();
        if ($configDb) {
            $configDb->item_key = 'check_version_datetime';
            $configDb->item_type = 'string';
            $configDb->item_tag = 'systems';
            $configDb->is_api = 0;
            $configDb->save();
        } else {
            ConfigUtility::addFresnsConfigItems([
                [
                    'item_key' => 'check_version_datetime',
                    'item_value' => null,
                    'item_type' => 'string',
                    'item_tag' => 'systems',
                ],
            ]);
        }

        return true;
    }

    // add data
    public function addData()
    {
        $this->fresnsProcess(function ($item) {
            CodeMessage::firstOrCreate([
                'plugin_unikey' => $item['plugin_unikey'],
                'code' => $item['code'],
                'lang_tag' => $item['lang_tag'],
            ], $item);
        });
    }
}
