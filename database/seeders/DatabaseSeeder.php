<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Initial data
        $this->call(CodeMessagesTableSeeder::class);
        $this->call(ConfigsTableSeeder::class);
        $this->call(DomainsTableSeeder::class);
        $this->call(LanguagesTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(PluginUsagesTableSeeder::class);

        // Test data (account and user)
        //$this->call(AccountsTableSeeder::class);
        //$this->call(AccountWalletsTableSeeder::class);
        //$this->call(UsersTableSeeder::class);
        //$this->call(UserStatsTableSeeder::class);
        //$this->call(UserRolesTableSeeder::class);
    }
}
