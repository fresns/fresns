<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('roles')->delete();

        \DB::table('roles')->insert([
            0 => [
                'id' => 1,
                'name' => 'Administrator',
                'type' => 1,
                'icon_file_id' => null,
                'icon_file_url' => null,
                'is_display_name' => 0,
                'is_display_icon' => 0,
                'nickname_color' => null,
                'permission' => '[{"permKey":"content_view","permValue":true,"permStatus":"","isCustom":false},{"permKey":"dialog","permValue":true,"permStatus":"","isCustom":false},{"permKey":"post_publish","permValue":true,"permStatus":"","isCustom":false},{"permKey":"post_review","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_email_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_phone_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_prove_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_limit_status","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_limit_type","permValue":1,"permStatus":"","isCustom":false},{"permKey":"post_limit_period_start","permValue":"2021-08-01 22:30:00","permStatus":"","isCustom":false},{"permKey":"post_limit_period_end","permValue":"2021-08-02 08:00:00","permStatus":"","isCustom":false},{"permKey":"post_limit_cycle_start","permValue":"23:00:00","permStatus":"","isCustom":false},{"permKey":"post_limit_cycle_end","permValue":"08:30:00","permStatus":"","isCustom":false},{"permKey":"post_limit_rule","permValue":1,"permStatus":"","isCustom":false},{"permKey":"comment_publish","permValue":true,"permStatus":"","isCustom":false},{"permKey":"comment_review","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_email_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_phone_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_prove_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_limit_status","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_limit_type","permValue":1,"permStatus":"","isCustom":false},{"permKey":"comment_limit_period_start","permValue":"2021-08-01 22:30:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_period_end","permValue":"2021-08-02 08:00:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_cycle_start","permValue":"23:00:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_cycle_end","permValue":"08:30:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_rule","permValue":1,"permStatus":"","isCustom":false},{"permKey":"post_editor_image","permValue":true,"permStatus":"","isCustom":false},{"permKey":"post_editor_video","permValue":true,"permStatus":"","isCustom":false},{"permKey":"post_editor_audio","permValue":true,"permStatus":"","isCustom":false},{"permKey":"post_editor_document","permValue":true,"permStatus":"","isCustom":false},{"permKey":"comment_editor_image","permValue":true,"permStatus":"","isCustom":false},{"permKey":"comment_editor_video","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_editor_audio","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_editor_document","permValue":false,"permStatus":"","isCustom":false},{"permKey":"image_max_size","permValue":5,"permStatus":"","isCustom":false},{"permKey":"video_max_size","permValue":50,"permStatus":"","isCustom":false},{"permKey":"video_max_time","permValue":15,"permStatus":"","isCustom":false},{"permKey":"audio_max_size","permValue":50,"permStatus":"","isCustom":false},{"permKey":"audio_max_time","permValue":60,"permStatus":"","isCustom":false},{"permKey":"document_max_size","permValue":10,"permStatus":"","isCustom":false},{"permKey":"download_file_count","permValue":999,"permStatus":"","isCustom":false}]',
                'rank_num' => 99,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => null,
            ],
            1 => [
                'id' => 2,
                'name' => 'Interdiction',
                'type' => 2,
                'icon_file_id' => null,
                'icon_file_url' => null,
                'is_display_name' => 0,
                'is_display_icon' => 0,
                'nickname_color' => null,
                'permission' => '[{"permKey":"content_view","permValue":true,"permStatus":"","isCustom":false},{"permKey":"dialog","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_publish","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_review","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_email_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_phone_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_prove_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_limit_status","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_limit_type","permValue":1,"permStatus":"","isCustom":false},{"permKey":"post_limit_period_start","permValue":"2021-08-01 22:30:00","permStatus":"","isCustom":false},{"permKey":"post_limit_period_end","permValue":"2021-08-02 08:00:00","permStatus":"","isCustom":false},{"permKey":"post_limit_cycle_start","permValue":"23:00:00","permStatus":"","isCustom":false},{"permKey":"post_limit_cycle_end","permValue":"08:30:00","permStatus":"","isCustom":false},{"permKey":"post_limit_rule","permValue":1,"permStatus":"","isCustom":false},{"permKey":"comment_publish","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_review","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_email_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_phone_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_prove_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_limit_status","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_limit_type","permValue":1,"permStatus":"","isCustom":false},{"permKey":"comment_limit_period_start","permValue":"2021-08-01 22:30:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_period_end","permValue":"2021-08-02 08:00:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_cycle_start","permValue":"23:00:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_cycle_end","permValue":"08:30:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_rule","permValue":1,"permStatus":"","isCustom":false},{"permKey":"post_editor_image","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_editor_video","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_editor_audio","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_editor_document","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_editor_image","permValue":true,"permStatus":"","isCustom":false},{"permKey":"comment_editor_video","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_editor_audio","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_editor_document","permValue":false,"permStatus":"","isCustom":false},{"permKey":"image_max_size","permValue":5,"permStatus":"","isCustom":false},{"permKey":"video_max_size","permValue":50,"permStatus":"","isCustom":false},{"permKey":"video_max_time","permValue":15,"permStatus":"","isCustom":false},{"permKey":"audio_max_size","permValue":50,"permStatus":"","isCustom":false},{"permKey":"audio_max_time","permValue":60,"permStatus":"","isCustom":false},{"permKey":"document_max_size","permValue":10,"permStatus":"","isCustom":false},{"permKey":"download_file_count","permValue":0,"permStatus":"","isCustom":false}]',
                'rank_num' => 99,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => null,
            ],
            2 => [
                'id' => 3,
                'name' => 'Pending Review',
                'type' => 2,
                'icon_file_id' => null,
                'icon_file_url' => null,
                'is_display_name' => 0,
                'is_display_icon' => 0,
                'nickname_color' => null,
                'permission' => '[{"permKey":"content_view","permValue":true,"permStatus":"","isCustom":false},{"permKey":"dialog","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_publish","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_review","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_email_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_phone_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_prove_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_limit_status","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_limit_type","permValue":1,"permStatus":"","isCustom":false},{"permKey":"post_limit_period_start","permValue":"2021-08-01 22:30:00","permStatus":"","isCustom":false},{"permKey":"post_limit_period_end","permValue":"2021-08-02 08:00:00","permStatus":"","isCustom":false},{"permKey":"post_limit_cycle_start","permValue":"23:00:00","permStatus":"","isCustom":false},{"permKey":"post_limit_cycle_end","permValue":"08:30:00","permStatus":"","isCustom":false},{"permKey":"post_limit_rule","permValue":1,"permStatus":"","isCustom":false},{"permKey":"comment_publish","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_review","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_email_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_phone_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_prove_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_limit_status","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_limit_type","permValue":1,"permStatus":"","isCustom":false},{"permKey":"comment_limit_period_start","permValue":"2021-08-01 22:30:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_period_end","permValue":"2021-08-02 08:00:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_cycle_start","permValue":"23:00:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_cycle_end","permValue":"08:30:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_rule","permValue":1,"permStatus":"","isCustom":false},{"permKey":"post_editor_image","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_editor_video","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_editor_audio","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_editor_document","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_editor_image","permValue":true,"permStatus":"","isCustom":false},{"permKey":"comment_editor_video","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_editor_audio","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_editor_document","permValue":false,"permStatus":"","isCustom":false},{"permKey":"image_max_size","permValue":5,"permStatus":"","isCustom":false},{"permKey":"video_max_size","permValue":50,"permStatus":"","isCustom":false},{"permKey":"video_max_time","permValue":15,"permStatus":"","isCustom":false},{"permKey":"audio_max_size","permValue":50,"permStatus":"","isCustom":false},{"permKey":"audio_max_time","permValue":60,"permStatus":"","isCustom":false},{"permKey":"document_max_size","permValue":10,"permStatus":"","isCustom":false},{"permKey":"download_file_count","permValue":0,"permStatus":"","isCustom":false}]',
                'rank_num' => 99,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => null,
            ],
            3 => [
                'id' => 4,
                'name' => 'General User',
                'type' => 3,
                'icon_file_id' => null,
                'icon_file_url' => null,
                'is_display_name' => 0,
                'is_display_icon' => 0,
                'nickname_color' => null,
                'permission' => '[{"permKey":"content_view","permValue":true,"permStatus":"","isCustom":false},{"permKey":"dialog","permValue":true,"permStatus":"","isCustom":false},{"permKey":"post_publish","permValue":true,"permStatus":"","isCustom":false},{"permKey":"post_review","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_email_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_phone_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_prove_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_limit_status","permValue":false,"permStatus":"","isCustom":false},{"permKey":"post_limit_type","permValue":1,"permStatus":"","isCustom":false},{"permKey":"post_limit_period_start","permValue":"2021-08-01 22:30:00","permStatus":"","isCustom":false},{"permKey":"post_limit_period_end","permValue":"2021-08-02 08:00:00","permStatus":"","isCustom":false},{"permKey":"post_limit_cycle_start","permValue":"23:00:00","permStatus":"","isCustom":false},{"permKey":"post_limit_cycle_end","permValue":"08:30:00","permStatus":"","isCustom":false},{"permKey":"post_limit_rule","permValue":1,"permStatus":"","isCustom":false},{"permKey":"comment_publish","permValue":true,"permStatus":"","isCustom":false},{"permKey":"comment_review","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_email_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_phone_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_prove_verify","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_limit_status","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_limit_type","permValue":1,"permStatus":"","isCustom":false},{"permKey":"comment_limit_period_start","permValue":"2021-08-01 22:30:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_period_end","permValue":"2021-08-02 08:00:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_cycle_start","permValue":"23:00:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_cycle_end","permValue":"08:30:00","permStatus":"","isCustom":false},{"permKey":"comment_limit_rule","permValue":1,"permStatus":"","isCustom":false},{"permKey":"post_editor_image","permValue":true,"permStatus":"","isCustom":false},{"permKey":"post_editor_video","permValue":true,"permStatus":"","isCustom":false},{"permKey":"post_editor_audio","permValue":true,"permStatus":"","isCustom":false},{"permKey":"post_editor_document","permValue":true,"permStatus":"","isCustom":false},{"permKey":"comment_editor_image","permValue":true,"permStatus":"","isCustom":false},{"permKey":"comment_editor_video","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_editor_audio","permValue":false,"permStatus":"","isCustom":false},{"permKey":"comment_editor_document","permValue":false,"permStatus":"","isCustom":false},{"permKey":"image_max_size","permValue":5,"permStatus":"","isCustom":false},{"permKey":"video_max_size","permValue":50,"permStatus":"","isCustom":false},{"permKey":"video_max_time","permValue":15,"permStatus":"","isCustom":false},{"permKey":"audio_max_size","permValue":50,"permStatus":"","isCustom":false},{"permKey":"audio_max_time","permValue":60,"permStatus":"","isCustom":false},{"permKey":"document_max_size","permValue":10,"permStatus":"","isCustom":false},{"permKey":"download_file_count","permValue":999,"permStatus":"","isCustom":false}]',
                'rank_num' => 99,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => null,
            ],
        ]);
    }
}
