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
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        \DB::table('plugin_usages')->delete();
        
        \DB::table('plugin_usages')->insert(array (
            0 => 
            array (
                'id' => 1,
                'plugin_unikey' => 'All',
                'type' => 4,
                'name' => 'All',
                'icon_file_id' => NULL,
                'icon_file_url' => NULL,
                'scene' => NULL,
                'editor_number' => NULL,
                'data_sources' => '{"postLists": {"sortNumber": [], "pluginUnikey": ""}, "postFollows": {"sortNumber": [], "pluginUnikey": ""}, "postNearbys": {"sortNumber": [], "pluginUnikey": ""}}',
                'is_group_admin' => 0,
                'group_id' => NULL,
                'roles' => NULL,
                'parameter' => NULL,
                'rank_num' => 1,
                'can_delete' => 0,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'plugin_unikey' => 'Text',
                'type' => 4,
                'name' => 'Text',
                'icon_file_id' => NULL,
                'icon_file_url' => NULL,
                'scene' => NULL,
                'editor_number' => NULL,
                'data_sources' => '{"postLists": {"sortNumber": [], "pluginUnikey": ""}, "postFollows": {"sortNumber": [], "pluginUnikey": ""}, "postNearbys": {"sortNumber": [], "pluginUnikey": ""}}',
                'is_group_admin' => 0,
                'group_id' => NULL,
                'roles' => NULL,
                'parameter' => NULL,
                'rank_num' => 2,
                'can_delete' => 0,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'plugin_unikey' => 'Image',
                'type' => 4,
                'name' => 'Image',
                'icon_file_id' => NULL,
                'icon_file_url' => NULL,
                'scene' => NULL,
                'editor_number' => NULL,
                'data_sources' => '{"postLists": {"sortNumber": [], "pluginUnikey": ""}, "postFollows": {"sortNumber": [], "pluginUnikey": ""}, "postNearbys": {"sortNumber": [], "pluginUnikey": ""}}',
                'is_group_admin' => 0,
                'group_id' => NULL,
                'roles' => NULL,
                'parameter' => NULL,
                'rank_num' => 3,
                'can_delete' => 0,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'plugin_unikey' => 'Video',
                'type' => 4,
                'name' => 'Video',
                'icon_file_id' => NULL,
                'icon_file_url' => NULL,
                'scene' => NULL,
                'editor_number' => NULL,
                'data_sources' => '{"postLists": {"sortNumber": [], "pluginUnikey": ""}, "postFollows": {"sortNumber": [], "pluginUnikey": ""}, "postNearbys": {"sortNumber": [], "pluginUnikey": ""}}',
                'is_group_admin' => 0,
                'group_id' => NULL,
                'roles' => NULL,
                'parameter' => NULL,
                'rank_num' => 4,
                'can_delete' => 0,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => NULL,
            ),
            4 => 
            array (
                'id' => 5,
                'plugin_unikey' => 'Audio',
                'type' => 4,
                'name' => 'Audio',
                'icon_file_id' => NULL,
                'icon_file_url' => NULL,
                'scene' => NULL,
                'editor_number' => NULL,
                'data_sources' => '{"postLists": {"sortNumber": [], "pluginUnikey": ""}, "postFollows": {"sortNumber": [], "pluginUnikey": ""}, "postNearbys": {"sortNumber": [], "pluginUnikey": ""}}',
                'is_group_admin' => 0,
                'group_id' => NULL,
                'roles' => NULL,
                'parameter' => NULL,
                'rank_num' => 5,
                'can_delete' => 0,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => NULL,
            ),
            5 => 
            array (
                'id' => 6,
                'plugin_unikey' => 'Document',
                'type' => 4,
                'name' => 'Document',
                'icon_file_id' => NULL,
                'icon_file_url' => NULL,
                'scene' => NULL,
                'editor_number' => NULL,
                'data_sources' => '{"postLists": {"sortNumber": [], "pluginUnikey": ""}, "postFollows": {"sortNumber": [], "pluginUnikey": ""}, "postNearbys": {"sortNumber": [], "pluginUnikey": ""}}',
                'is_group_admin' => 0,
                'group_id' => NULL,
                'roles' => NULL,
                'parameter' => NULL,
                'rank_num' => 6,
                'can_delete' => 0,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}