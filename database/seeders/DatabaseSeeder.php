<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Initial data
        $this->call(AppUsagesTableSeeder::class);
        $this->call(CodeMessagesTableSeeder::class);
        $this->call(ConfigsTableSeeder::class);
        $this->call(DomainsTableSeeder::class);
        $this->call(LanguagePacksTableSeeder::class);
        $this->call(RolesTableSeeder::class);
    }
}
