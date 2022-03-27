<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands\Upgrade;

use Illuminate\Console\Command;

class Upgrade5Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fresns:upgrade-5';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upgrade to 5';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->call('db:seed', [
            '--class' => 'UserRolesTableSeeder',
        ]);

        return Command::SUCCESS;
    }
}
