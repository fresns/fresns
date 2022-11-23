<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands\Upgrade;

use App\Models\Config;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class Upgrade2Command extends Command
{
    protected $signature = 'fresns:upgrade-2';

    protected $description = 'upgrade to 2';

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
        $packagistMirrors = Config::where('item_key', 'packagist_mirrors')->first();
        $itemValue = json_decode('[{"name":"Global, CloudFlare","repo":"https://packagist.pages.dev"},{"name":"Africa, South Africa","repo":"https://packagist.co.za"},{"name":"Asia, China Tencent","repo":"https://mirrors.tencent.com/composer/"},{"name":"Asia, India","repo":"https://packagist.in"},{"name":"Asia, Indonesia","repo":"https://packagist.phpindonesia.id"},{"name":"Asia, Japan","repo":"https://packagist.jp"},{"name":"Asia, South Korea","repo":"https://packagist.kr"},{"name":"Asia, Thailand","repo":"https://packagist.mycools.in.th/"},{"name":"Asia, Taiwan","repo":"https://packagist.tw/"},{"name":"Europe, Finland","repo":"https://packagist.fi"},{"name":"Europe, Germany","repo":"https://packagist.hesse.im"},{"name":"South America, Brazil","repo":"https://packagist.com.br"}]', true);

        if (! $packagistMirrors) {
            $fresnsItems = Config::where('item_key', 'fresns_items')->first();

            if ($fresnsItems) {
                $fresnsItems->item_key = 'packagist_mirrors';
                $fresnsItems->item_value = $itemValue;
                $fresnsItems->item_type = 'array';
                $fresnsItems->item_tag = 'systems';
                $fresnsItems->is_multilingual = 0;
                $fresnsItems->is_custom = 0;
                $fresnsItems->is_api = 0;
                $fresnsItems->save();
            } else {
                $newConfig = new Config;
                $newConfig->item_key = 'packagist_mirrors';
                $newConfig->item_value = $itemValue;
                $newConfig->item_type = 'array';
                $newConfig->item_tag = 'systems';
                $newConfig->is_multilingual = 0;
                $newConfig->is_custom = 0;
                $newConfig->is_api = 0;
                $newConfig->save();
            }
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
