<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands;

use Fresns\PluginManager\Support\Process;
use Illuminate\Console\Command;

class WebsiteEngineUninstall extends Command
{
    protected $signature = 'fresns:uninstall-website';

    protected $description = 'Uninstall fresns website engine';

    public function __construct()
    {
        parent::__construct();
    }

    // composer remove fresns/website-engine
    public function handle()
    {
        $websiteEngineExists = class_exists(\Fresns\WebsiteEngine\Providers\WebsiteEngineServiceProvider::class);

        if (! $websiteEngineExists) {
            $this->info('Website engine already uninstalled');

            return Command::SUCCESS;
        }

        $httpProxy = config('app.http_proxy');

        $process = Process::run(<<<"SHELL"
            export http_proxy=$httpProxy https_proxy=$httpProxy
            echo http_proxy=\$http_proxy
            echo https_proxy=\$https_proxy
            echo "current user:" `whoami`
            echo "home path permission is:" `ls -ld ~`
            echo ""

            #test -f ~/.config/composer/composer.json && echo 1 || (mkdir -p ~/.config/composer && echo "{}" > ~/.config/composer/composer.json)
            #echo ""

            echo "global composer.json content": `cat ~/.config/composer/composer.json`
            echo ""

            echo "PATH:" `echo \$PATH`
            echo ""

            echo "php:" `which php` "\n version" `php -v`
            echo "composer:" `which composer` "\n version" `composer --version`
            echo "git:" `which git` "\n version" `git --version`
            echo ""

            # install command
            composer diagnose
            composer remove fresns/website-engine
        SHELL, $this->output);

        if (! $process->isSuccessful()) {
            $this->error('Failed to remove fresns/website-engine');

            return Command::FAILURE;
        }

        $this->info('Website Engine uninstallation Successful');

        return Command::SUCCESS;
    }
}
