<?php

namespace Database\Seeders;

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
        // \App\Models\User::factory(10)->create();
        $this->call(CodeMessagesTableSeeder::class);
        $this->call(ConfigsTableSeeder::class);
        $this->call(DomainsTableSeeder::class);
        $this->call(LanguagesTableSeeder::class);
        $this->call(MemberRolesTableSeeder::class);
        $this->call(PluginUsagesTableSeeder::class);
    }
}
