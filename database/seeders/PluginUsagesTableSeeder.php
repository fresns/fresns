<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PluginUsagesTableSeeder extends Seeder
{
    /**
     * Fresns seed file.
     */
    public function run(): void
    {
        DB::table('plugin_usages')->delete();

        DB::table('plugin_usages')->insert([
            [
                'id' => 1,
                'usage_type' => 4,
                'plugin_fskey' => 'All',
                'name' => 'All',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'scene' => '1,2',
                'editor_toolbar' => 0,
                'editor_number' => null,
                'data_sources' => '{"postByAll":{"pluginRating":[],"pluginFskey":""},"postByFollow":{"pluginRating":[],"pluginFskey":""},"postByNearby":{"pluginRating":[],"pluginFskey":""},"commentByAll":{"pluginRating":[],"pluginFskey":""},"commentByFollow":{"pluginRating":[],"pluginFskey":""},"commentByNearby":{"pluginRating":[],"pluginFskey":""}}',
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
            [
                'id' => 2,
                'usage_type' => 4,
                'plugin_fskey' => 'Text',
                'name' => 'Text',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'scene' => '1,2',
                'editor_toolbar' => 0,
                'editor_number' => null,
                'data_sources' => '{"postByAll":{"pluginRating":[],"pluginFskey":""},"postByFollow":{"pluginRating":[],"pluginFskey":""},"postByNearby":{"pluginRating":[],"pluginFskey":""},"commentByAll":{"pluginRating":[],"pluginFskey":""},"commentByFollow":{"pluginRating":[],"pluginFskey":""},"commentByNearby":{"pluginRating":[],"pluginFskey":""}}',
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
            [
                'id' => 3,
                'usage_type' => 4,
                'plugin_fskey' => 'Image',
                'name' => 'Image',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'scene' => '1,2',
                'editor_toolbar' => 0,
                'editor_number' => null,
                'data_sources' => '{"postByAll":{"pluginRating":[],"pluginFskey":""},"postByFollow":{"pluginRating":[],"pluginFskey":""},"postByNearby":{"pluginRating":[],"pluginFskey":""},"commentByAll":{"pluginRating":[],"pluginFskey":""},"commentByFollow":{"pluginRating":[],"pluginFskey":""},"commentByNearby":{"pluginRating":[],"pluginFskey":""}}',
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
            [
                'id' => 4,
                'usage_type' => 4,
                'plugin_fskey' => 'Video',
                'name' => 'Video',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'scene' => '1,2',
                'editor_toolbar' => 0,
                'editor_number' => null,
                'data_sources' => '{"postByAll":{"pluginRating":[],"pluginFskey":""},"postByFollow":{"pluginRating":[],"pluginFskey":""},"postByNearby":{"pluginRating":[],"pluginFskey":""},"commentByAll":{"pluginRating":[],"pluginFskey":""},"commentByFollow":{"pluginRating":[],"pluginFskey":""},"commentByNearby":{"pluginRating":[],"pluginFskey":""}}',
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
            [
                'id' => 5,
                'usage_type' => 4,
                'plugin_fskey' => 'Audio',
                'name' => 'Audio',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'scene' => '1,2',
                'editor_toolbar' => 0,
                'editor_number' => null,
                'data_sources' => '{"postByAll":{"pluginRating":[],"pluginFskey":""},"postByFollow":{"pluginRating":[],"pluginFskey":""},"postByNearby":{"pluginRating":[],"pluginFskey":""},"commentByAll":{"pluginRating":[],"pluginFskey":""},"commentByFollow":{"pluginRating":[],"pluginFskey":""},"commentByNearby":{"pluginRating":[],"pluginFskey":""}}',
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
            [
                'id' => 6,
                'usage_type' => 4,
                'plugin_fskey' => 'Document',
                'name' => 'Document',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'scene' => '1,2',
                'editor_toolbar' => 0,
                'editor_number' => null,
                'data_sources' => '{"postByAll":{"pluginRating":[],"pluginFskey":""},"postByFollow":{"pluginRating":[],"pluginFskey":""},"postByNearby":{"pluginRating":[],"pluginFskey":""},"commentByAll":{"pluginRating":[],"pluginFskey":""},"commentByFollow":{"pluginRating":[],"pluginFskey":""},"commentByNearby":{"pluginRating":[],"pluginFskey":""}}',
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
