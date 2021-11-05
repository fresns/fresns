<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MemberRolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        \DB::table('member_roles')->delete();
        
        \DB::table('member_roles')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Administrator',
                'type' => 1,
                'icon_file_id' => NULL,
                'icon_file_url' => NULL,
                'is_display_name' => 0,
                'is_display_icon' => 0,
                'nickname_color' => NULL,
                'permission' => '[{"permKey": "content_view", "permValue": true, "permStatus": ""}, {"permKey": "dialog", "permValue": true, "permStatus": ""}, {"permKey": "post_publish", "permValue": true, "permStatus": ""}, {"permKey": "post_review", "permValue": false, "permStatus": ""}, {"permKey": "post_email_verify", "permValue": false, "permStatus": ""}, {"permKey": "post_phone_verify", "permValue": false, "permStatus": ""}, {"permKey": "post_prove_verify", "permValue": false, "permStatus": ""}, {"permKey": "post_limit_status", "permValue": false, "permStatus": ""}, {"permKey": "post_limit_type", "permValue": 1, "permStatus": ""}, {"permKey": "post_limit_period_start", "permValue": "2021-10-08 22:30:00", "permStatus": ""}, {"permKey": "post_limit_period_end", "permValue": "2021-10-15 08:00:00", "permStatus": ""}, {"permKey": "post_limit_cycle_start", "permValue": "23:00:00", "permStatus": ""}, {"permKey": "post_limit_cycle_end", "permValue": "08:30:00", "permStatus": ""}, {"permKey": "post_limit_rule", "permValue": 1, "permStatus": ""}, {"permKey": "comment_publish", "permValue": true, "permStatus": ""}, {"permKey": "comment_review", "permValue": false, "permStatus": ""}, {"permKey": "comment_email_verify", "permValue": false, "permStatus": ""}, {"permKey": "comment_phone_verify", "permValue": false, "permStatus": ""}, {"permKey": "comment_prove_verify", "permValue": false, "permStatus": ""}, {"permKey": "comment_limit_status", "permValue": false, "permStatus": ""}, {"permKey": "comment_limit_type", "permValue": 1, "permStatus": ""}, {"permKey": "comment_limit_period_start", "permValue": "2021-10-08 22:30:00", "permStatus": ""}, {"permKey": "comment_limit_period_end", "permValue": "2021-10-15 08:00:00", "permStatus": ""}, {"permKey": "comment_limit_cycle_start", "permValue": "23:00:00", "permStatus": ""}, {"permKey": "comment_limit_cycle_end", "permValue": "08:30:00", "permStatus": ""}, {"permKey": "comment_limit_rule", "permValue": 1, "permStatus": ""}, {"permKey": "post_editor_image", "permValue": true, "permStatus": ""}, {"permKey": "post_editor_video", "permValue": true, "permStatus": ""}, {"permKey": "post_editor_audio", "permValue": true, "permStatus": ""}, {"permKey": "post_editor_doc", "permValue": true, "permStatus": ""}, {"permKey": "comment_editor_image", "permValue": true, "permStatus": ""}, {"permKey": "comment_editor_video", "permValue": false, "permStatus": ""}, {"permKey": "comment_editor_audio", "permValue": false, "permStatus": ""}, {"permKey": "comment_editor_doc", "permValue": false, "permStatus": ""}, {"permKey": "images_max_size", "permValue": 5, "permStatus": ""}, {"permKey": "videos_max_size", "permValue": 50, "permStatus": ""}, {"permKey": "videos_max_time", "permValue": 15, "permStatus": ""}, {"permKey": "audios_max_size", "permValue": 50, "permStatus": ""}, {"permKey": "audios_max_time", "permValue": 60, "permStatus": ""}, {"permKey": "docs_max_size", "permValue": 10, "permStatus": ""}, {"permKey": "download_file_count", "permValue": 10, "permStatus": ""}]',
                'rank_num' => 99,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Interdiction',
                'type' => 2,
                'icon_file_id' => NULL,
                'icon_file_url' => NULL,
                'is_display_name' => 0,
                'is_display_icon' => 0,
                'nickname_color' => NULL,
                'permission' => '[{"permKey": "content_view", "permValue": true, "permStatus": ""}, {"permKey": "dialog", "permValue": false, "permStatus": ""}, {"permKey": "post_publish", "permValue": false, "permStatus": ""}, {"permKey": "post_review", "permValue": false, "permStatus": ""}, {"permKey": "post_email_verify", "permValue": false, "permStatus": ""}, {"permKey": "post_phone_verify", "permValue": false, "permStatus": ""}, {"permKey": "post_prove_verify", "permValue": false, "permStatus": ""}, {"permKey": "post_limit_status", "permValue": false, "permStatus": ""}, {"permKey": "post_limit_type", "permValue": 1, "permStatus": ""}, {"permKey": "post_limit_period_start", "permValue": "2021-08-01 22:30:00", "permStatus": ""}, {"permKey": "post_limit_period_end", "permValue": "2021-08-02 08:00:00", "permStatus": ""}, {"permKey": "post_limit_cycle_start", "permValue": "23:00:00", "permStatus": ""}, {"permKey": "post_limit_cycle_end", "permValue": "08:30:00", "permStatus": ""}, {"permKey": "post_limit_rule", "permValue": 1, "permStatus": ""}, {"permKey": "comment_publish", "permValue": false, "permStatus": ""}, {"permKey": "comment_review", "permValue": false, "permStatus": ""}, {"permKey": "comment_email_verify", "permValue": false, "permStatus": ""}, {"permKey": "comment_phone_verify", "permValue": false, "permStatus": ""}, {"permKey": "comment_prove_verify", "permValue": false, "permStatus": ""}, {"permKey": "comment_limit_status", "permValue": false, "permStatus": ""}, {"permKey": "comment_limit_type", "permValue": 1, "permStatus": ""}, {"permKey": "comment_limit_period_start", "permValue": "2021-08-01 22:30:00", "permStatus": ""}, {"permKey": "comment_limit_period_end", "permValue": "2021-08-02 08:00:00", "permStatus": ""}, {"permKey": "comment_limit_cycle_start", "permValue": "23:00:00", "permStatus": ""}, {"permKey": "comment_limit_cycle_end", "permValue": "08:30:00", "permStatus": ""}, {"permKey": "comment_limit_rule", "permValue": 1, "permStatus": ""}, {"permKey": "post_editor_image", "permValue": false, "permStatus": ""}, {"permKey": "post_editor_video", "permValue": false, "permStatus": ""}, {"permKey": "post_editor_audio", "permValue": false, "permStatus": ""}, {"permKey": "post_editor_doc", "permValue": false, "permStatus": ""}, {"permKey": "comment_editor_image", "permValue": true, "permStatus": ""}, {"permKey": "comment_editor_video", "permValue": false, "permStatus": ""}, {"permKey": "comment_editor_audio", "permValue": false, "permStatus": ""}, {"permKey": "comment_editor_doc", "permValue": false, "permStatus": ""}, {"permKey": "images_max_size", "permValue": 5, "permStatus": ""}, {"permKey": "videos_max_size", "permValue": 50, "permStatus": ""}, {"permKey": "videos_max_time", "permValue": 15, "permStatus": ""}, {"permKey": "audios_max_size", "permValue": 50, "permStatus": ""}, {"permKey": "audios_max_time", "permValue": 60, "permStatus": ""}, {"permKey": "docs_max_size", "permValue": 10, "permStatus": ""}, {"permKey": "download_file_count", "permValue": 0, "permStatus": ""}]',
                'rank_num' => 99,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Pending Review',
                'type' => 2,
                'icon_file_id' => NULL,
                'icon_file_url' => NULL,
                'is_display_name' => 0,
                'is_display_icon' => 0,
                'nickname_color' => NULL,
                'permission' => '[{"permKey": "content_view", "permValue": true, "permStatus": ""}, {"permKey": "dialog", "permValue": false, "permStatus": ""}, {"permKey": "post_publish", "permValue": false, "permStatus": ""}, {"permKey": "post_review", "permValue": false, "permStatus": ""}, {"permKey": "post_email_verify", "permValue": false, "permStatus": ""}, {"permKey": "post_phone_verify", "permValue": false, "permStatus": ""}, {"permKey": "post_prove_verify", "permValue": false, "permStatus": ""}, {"permKey": "post_limit_status", "permValue": false, "permStatus": ""}, {"permKey": "post_limit_type", "permValue": 1, "permStatus": ""}, {"permKey": "post_limit_period_start", "permValue": "2021-08-01 22:30:00", "permStatus": ""}, {"permKey": "post_limit_period_end", "permValue": "2021-08-02 08:00:00", "permStatus": ""}, {"permKey": "post_limit_cycle_start", "permValue": "23:00:00", "permStatus": ""}, {"permKey": "post_limit_cycle_end", "permValue": "08:30:00", "permStatus": ""}, {"permKey": "post_limit_rule", "permValue": 1, "permStatus": ""}, {"permKey": "comment_publish", "permValue": false, "permStatus": ""}, {"permKey": "comment_review", "permValue": false, "permStatus": ""}, {"permKey": "comment_email_verify", "permValue": false, "permStatus": ""}, {"permKey": "comment_phone_verify", "permValue": false, "permStatus": ""}, {"permKey": "comment_prove_verify", "permValue": false, "permStatus": ""}, {"permKey": "comment_limit_status", "permValue": false, "permStatus": ""}, {"permKey": "comment_limit_type", "permValue": 1, "permStatus": ""}, {"permKey": "comment_limit_period_start", "permValue": "2021-08-01 22:30:00", "permStatus": ""}, {"permKey": "comment_limit_period_end", "permValue": "2021-08-02 08:00:00", "permStatus": ""}, {"permKey": "comment_limit_cycle_start", "permValue": "23:00:00", "permStatus": ""}, {"permKey": "comment_limit_cycle_end", "permValue": "08:30:00", "permStatus": ""}, {"permKey": "comment_limit_rule", "permValue": 1, "permStatus": ""}, {"permKey": "post_editor_image", "permValue": false, "permStatus": ""}, {"permKey": "post_editor_video", "permValue": false, "permStatus": ""}, {"permKey": "post_editor_audio", "permValue": false, "permStatus": ""}, {"permKey": "post_editor_doc", "permValue": false, "permStatus": ""}, {"permKey": "comment_editor_image", "permValue": true, "permStatus": ""}, {"permKey": "comment_editor_video", "permValue": false, "permStatus": ""}, {"permKey": "comment_editor_audio", "permValue": false, "permStatus": ""}, {"permKey": "comment_editor_doc", "permValue": false, "permStatus": ""}, {"permKey": "images_max_size", "permValue": 5, "permStatus": ""}, {"permKey": "videos_max_size", "permValue": 50, "permStatus": ""}, {"permKey": "videos_max_time", "permValue": 15, "permStatus": ""}, {"permKey": "audios_max_size", "permValue": 50, "permStatus": ""}, {"permKey": "audios_max_time", "permValue": 60, "permStatus": ""}, {"permKey": "docs_max_size", "permValue": 10, "permStatus": ""}, {"permKey": "download_file_count", "permValue": 0, "permStatus": ""}]',
                'rank_num' => 99,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'General User',
                'type' => 3,
                'icon_file_id' => NULL,
                'icon_file_url' => NULL,
                'is_display_name' => 0,
                'is_display_icon' => 0,
                'nickname_color' => NULL,
                'permission' => '[{"permKey": "content_view", "permValue": true, "permStatus": ""}, {"permKey": "dialog", "permValue": true, "permStatus": ""}, {"permKey": "post_publish", "permValue": true, "permStatus": ""}, {"permKey": "post_review", "permValue": false, "permStatus": ""}, {"permKey": "post_email_verify", "permValue": false, "permStatus": ""}, {"permKey": "post_phone_verify", "permValue": false, "permStatus": ""}, {"permKey": "post_prove_verify", "permValue": false, "permStatus": ""}, {"permKey": "post_limit_status", "permValue": false, "permStatus": ""}, {"permKey": "post_limit_type", "permValue": 1, "permStatus": ""}, {"permKey": "post_limit_period_start", "permValue": "2021-10-08 22:30:00", "permStatus": ""}, {"permKey": "post_limit_period_end", "permValue": "2021-10-15 08:00:00", "permStatus": ""}, {"permKey": "post_limit_cycle_start", "permValue": "23:00:00", "permStatus": ""}, {"permKey": "post_limit_cycle_end", "permValue": "08:30:00", "permStatus": ""}, {"permKey": "post_limit_rule", "permValue": 1, "permStatus": ""}, {"permKey": "comment_publish", "permValue": true, "permStatus": ""}, {"permKey": "comment_review", "permValue": false, "permStatus": ""}, {"permKey": "comment_email_verify", "permValue": false, "permStatus": ""}, {"permKey": "comment_phone_verify", "permValue": false, "permStatus": ""}, {"permKey": "comment_prove_verify", "permValue": false, "permStatus": ""}, {"permKey": "comment_limit_status", "permValue": false, "permStatus": ""}, {"permKey": "comment_limit_type", "permValue": 1, "permStatus": ""}, {"permKey": "comment_limit_period_start", "permValue": "2021-10-08 22:30:00", "permStatus": ""}, {"permKey": "comment_limit_period_end", "permValue": "2021-10-15 08:00:00", "permStatus": ""}, {"permKey": "comment_limit_cycle_start", "permValue": "23:00:00", "permStatus": ""}, {"permKey": "comment_limit_cycle_end", "permValue": "08:30:00", "permStatus": ""}, {"permKey": "comment_limit_rule", "permValue": 1, "permStatus": ""}, {"permKey": "post_editor_image", "permValue": true, "permStatus": ""}, {"permKey": "post_editor_video", "permValue": true, "permStatus": ""}, {"permKey": "post_editor_audio", "permValue": true, "permStatus": ""}, {"permKey": "post_editor_doc", "permValue": true, "permStatus": ""}, {"permKey": "comment_editor_image", "permValue": true, "permStatus": ""}, {"permKey": "comment_editor_video", "permValue": false, "permStatus": ""}, {"permKey": "comment_editor_audio", "permValue": false, "permStatus": ""}, {"permKey": "comment_editor_doc", "permValue": false, "permStatus": ""}, {"permKey": "images_max_size", "permValue": 5, "permStatus": ""}, {"permKey": "videos_max_size", "permValue": 50, "permStatus": ""}, {"permKey": "videos_max_time", "permValue": 15, "permStatus": ""}, {"permKey": "audios_max_size", "permValue": 50, "permStatus": ""}, {"permKey": "audios_max_time", "permValue": 60, "permStatus": ""}, {"permKey": "docs_max_size", "permValue": 10, "permStatus": ""}, {"permKey": "download_file_count", "permValue": 10, "permStatus": ""}]',
                'rank_num' => 99,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => NULL,
            ),
        ));

    }
}