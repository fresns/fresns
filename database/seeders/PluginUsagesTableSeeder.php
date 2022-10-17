<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PluginUsagesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('plugin_usages')->delete();

        \DB::table('plugin_usages')->insert([
            0 => [
                'id' => 1,
                'usage_type' => 4,
                'plugin_unikey' => 'All',
                'name' => 'All',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'scene' => null,
                'editor_toolbar' => 0,
                'editor_number' => null,
                'data_sources' => '{"postByAll": {"pluginRating": [], "pluginUnikey": ""}, "postByFollow": {"pluginRating": [], "pluginUnikey": ""}, "postByNearby": {"pluginRating": [], "pluginUnikey": ""}}',
                'is_group_admin' => 0,
                'group_id' => null,
                'roles' => null,
                'parameter' => null,
                'rating' => 1,
                'can_delete' => 0,
                'is_enable' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            1 => [
                'id' => 2,
                'usage_type' => 4,
                'plugin_unikey' => 'Text',
                'name' => 'Text',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'scene' => null,
                'editor_toolbar' => 0,
                'editor_number' => null,
                'data_sources' => '{"postByAll": {"pluginRating": [], "pluginUnikey": ""}, "postByFollow": {"pluginRating": [], "pluginUnikey": ""}, "postByNearby": {"pluginRating": [], "pluginUnikey": ""}}',
                'is_group_admin' => 0,
                'group_id' => null,
                'roles' => null,
                'parameter' => null,
                'rating' => 2,
                'can_delete' => 0,
                'is_enable' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            2 => [
                'id' => 3,
                'usage_type' => 4,
                'plugin_unikey' => 'Image',
                'name' => 'Image',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'scene' => null,
                'editor_toolbar' => 0,
                'editor_number' => null,
                'data_sources' => '{"postByAll": {"pluginRating": [], "pluginUnikey": ""}, "postByFollow": {"pluginRating": [], "pluginUnikey": ""}, "postByNearby": {"pluginRating": [], "pluginUnikey": ""}}',
                'is_group_admin' => 0,
                'group_id' => null,
                'roles' => null,
                'parameter' => null,
                'rating' => 3,
                'can_delete' => 0,
                'is_enable' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            3 => [
                'id' => 4,
                'usage_type' => 4,
                'plugin_unikey' => 'Video',
                'name' => 'Video',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'scene' => null,
                'editor_toolbar' => 0,
                'editor_number' => null,
                'data_sources' => '{"postByAll": {"pluginRating": [], "pluginUnikey": ""}, "postByFollow": {"pluginRating": [], "pluginUnikey": ""}, "postByNearby": {"pluginRating": [], "pluginUnikey": ""}}',
                'is_group_admin' => 0,
                'group_id' => null,
                'roles' => null,
                'parameter' => null,
                'rating' => 4,
                'can_delete' => 0,
                'is_enable' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            4 => [
                'id' => 5,
                'usage_type' => 4,
                'plugin_unikey' => 'Audio',
                'name' => 'Audio',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'scene' => null,
                'editor_toolbar' => 0,
                'editor_number' => null,
                'data_sources' => '{"postByAll": {"pluginRating": [], "pluginUnikey": ""}, "postByFollow": {"pluginRating": [], "pluginUnikey": ""}, "postByNearby": {"pluginRating": [], "pluginUnikey": ""}}',
                'is_group_admin' => 0,
                'group_id' => null,
                'roles' => null,
                'parameter' => null,
                'rating' => 5,
                'can_delete' => 0,
                'is_enable' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            5 => [
                'id' => 6,
                'usage_type' => 4,
                'plugin_unikey' => 'Document',
                'name' => 'Document',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'scene' => null,
                'editor_toolbar' => 0,
                'editor_number' => null,
                'data_sources' => '{"postByAll": {"pluginRating": [], "pluginUnikey": ""}, "postByFollow": {"pluginRating": [], "pluginUnikey": ""}, "postByNearby": {"pluginRating": [], "pluginUnikey": ""}}',
                'is_group_admin' => 0,
                'group_id' => null,
                'roles' => null,
                'parameter' => null,
                'rating' => 6,
                'can_delete' => 0,
                'is_enable' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ]);
    }
}
