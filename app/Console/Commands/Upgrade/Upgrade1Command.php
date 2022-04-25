<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands\Upgrade;

use App\Utilities\AppUtility;
use Illuminate\Console\Command;

class Upgrade1Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fresns:upgrade-1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upgrade to 1';

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

        //AppUtility::editVersion('1.5.0', 1);

        return Command::SUCCESS;
    }
}
