<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PluginsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('plugins')->delete();

        \DB::table('plugins')->insert([
            0 => [
                'id' => 1,
                'unikey' => 'ThemeFrame',
                'type' => 4,
                'name' => 'Theme Frame',
                'description' => 'Fresns theme framework to showcase web-side functionality and interaction flow.',
                'version' => '2.0.0-alpha.7',
                'author' => 'Fresns',
                'author_link' => 'https://fresns.org',
                'scene' => null,
                'plugin_host' => null,
                'access_path' => null,
                'settings_path' => null,
                'theme_functions' => 1,
                'is_upgrade' => 0,
                'upgrade_code' => null,
                'upgrade_version' => null,
                'is_enable' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ]);
    }
}
