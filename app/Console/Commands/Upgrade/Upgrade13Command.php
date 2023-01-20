<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands\Upgrade;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class Upgrade13Command extends Command
{
    protected $signature = 'fresns:upgrade-13';

    protected $description = 'upgrade to fresns v2.2.1';

    public function __construct()
    {
        parent::__construct();
    }

    // execute the console command
    public function handle()
    {
        logger('upgrade:fresns-13 composerInstall');
        $this->composerInstall();

        logger('upgrade:fresns-13 migrate');
        $this->call('migrate', ['--force' => true]);

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
}
