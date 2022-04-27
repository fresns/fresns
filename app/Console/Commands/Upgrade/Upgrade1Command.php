<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands\Upgrade;

use Illuminate\Console\Command;

class Upgrade1Command extends Command
{
    protected $signature = 'fresns:upgrade-1';

    protected $description = 'upgrade to 1';

    public function __construct()
    {
        parent::__construct();
    }

    // execute the console command
    public function handle()
    {
        $this->call('migrate');

        $this->call('db:seed', [
            '--class' => 'UserRolesTableSeeder',
        ]);

        return Command::SUCCESS;
    }
}
