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
                'permissions' => '[{"permKey":"content_view","permValue":true,"isCustom":false},{"permKey":"dialog","permValue":true,"isCustom":false},{"permKey":"post_publish","permValue":true,"isCustom":false},{"permKey":"post_review","permValue":false,"isCustom":false},{"permKey":"post_email_verify","permValue":false,"isCustom":false},{"permKey":"post_phone_verify","permValue":false,"isCustom":false},{"permKey":"post_real_name_verify","permValue":false,"isCustom":false},{"permKey":"post_limit_status","permValue":false,"isCustom":false},{"permKey":"post_limit_type","permValue":1,"isCustom":false},{"permKey":"post_limit_period_start","permValue":"2022-06-01 22:30:00","isCustom":false},{"permKey":"post_limit_period_end","permValue":"2022-06-08 08:00:00","isCustom":false},{"permKey":"post_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"post_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"post_limit_rule","permValue":1,"isCustom":false},{"permKey":"post_minute_interval","permValue":1,"isCustom":false},{"permKey":"post_draft_count","permValue":10,"isCustom":false},{"permKey":"comment_publish","permValue":true,"isCustom":false},{"permKey":"comment_review","permValue":false,"isCustom":false},{"permKey":"comment_email_verify","permValue":false,"isCustom":false},{"permKey":"comment_phone_verify","permValue":false,"isCustom":false},{"permKey":"comment_real_name_verify","permValue":false,"isCustom":false},{"permKey":"comment_limit_status","permValue":false,"isCustom":false},{"permKey":"comment_limit_type","permValue":1,"isCustom":false},{"permKey":"comment_limit_period_start","permValue":"2021-06-01 22:30:00","isCustom":false},{"permKey":"comment_limit_period_end","permValue":"2021-06-08 08:00:00","isCustom":false},{"permKey":"comment_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"comment_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"comment_limit_rule","permValue":1,"isCustom":false},{"permKey":"comment_minute_interval","permValue":1,"isCustom":false},{"permKey":"comment_draft_count","permValue":10,"isCustom":false},{"permKey":"post_editor_image","permValue":true,"isCustom":false},{"permKey":"post_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"post_editor_video","permValue":true,"isCustom":false},{"permKey":"post_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"post_editor_audio","permValue":true,"isCustom":false},{"permKey":"post_editor_audio_upload_number","permValue":10,"isCustom":false},{"permKey":"post_editor_document","permValue":true,"isCustom":false},{"permKey":"post_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"comment_editor_image","permValue":true,"isCustom":false},{"permKey":"comment_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"comment_editor_video","permValue":false,"isCustom":false},{"permKey":"comment_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"comment_editor_audio","permValue":false,"isCustom":false},{"permKey":"comment_editor_audio_upload_number","permValue":10,"isCustom":false},{"permKey":"comment_editor_document","permValue":false,"isCustom":false},{"permKey":"comment_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"image_max_size","permValue":5,"isCustom":false},{"permKey":"video_max_size","permValue":50,"isCustom":false},{"permKey":"video_max_time","permValue":15,"isCustom":false},{"permKey":"audio_max_size","permValue":50,"isCustom":false},{"permKey":"audio_max_time","permValue":60,"isCustom":false},{"permKey":"document_max_size","permValue":10,"isCustom":false},{"permKey":"download_file_count","permValue":999,"isCustom":false}]',
                'rating' => 1,
                'is_enable' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
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
                'permissions' => '[{"permKey":"content_view","permValue":true,"isCustom":false},{"permKey":"dialog","permValue":true,"isCustom":false},{"permKey":"post_publish","permValue":false,"isCustom":false},{"permKey":"post_review","permValue":false,"isCustom":false},{"permKey":"post_email_verify","permValue":false,"isCustom":false},{"permKey":"post_phone_verify","permValue":false,"isCustom":false},{"permKey":"post_real_name_verify","permValue":false,"isCustom":false},{"permKey":"post_limit_status","permValue":false,"isCustom":false},{"permKey":"post_limit_type","permValue":1,"isCustom":false},{"permKey":"post_limit_period_start","permValue":"2022-06-01 22:30:00","isCustom":false},{"permKey":"post_limit_period_end","permValue":"2022-06-08 08:00:00","isCustom":false},{"permKey":"post_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"post_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"post_limit_rule","permValue":1,"isCustom":false},{"permKey":"post_minute_interval","permValue":1,"isCustom":false},{"permKey":"post_draft_count","permValue":10,"isCustom":false},{"permKey":"comment_publish","permValue":false,"isCustom":false},{"permKey":"comment_review","permValue":false,"isCustom":false},{"permKey":"comment_email_verify","permValue":false,"isCustom":false},{"permKey":"comment_phone_verify","permValue":false,"isCustom":false},{"permKey":"comment_real_name_verify","permValue":false,"isCustom":false},{"permKey":"comment_limit_status","permValue":false,"isCustom":false},{"permKey":"comment_limit_type","permValue":1,"isCustom":false},{"permKey":"comment_limit_period_start","permValue":"2021-06-01 22:30:00","isCustom":false},{"permKey":"comment_limit_period_end","permValue":"2021-06-08 08:00:00","isCustom":false},{"permKey":"comment_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"comment_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"comment_limit_rule","permValue":1,"isCustom":false},{"permKey":"comment_minute_interval","permValue":1,"isCustom":false},{"permKey":"comment_draft_count","permValue":10,"isCustom":false},{"permKey":"post_editor_image","permValue":true,"isCustom":false},{"permKey":"post_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"post_editor_video","permValue":true,"isCustom":false},{"permKey":"post_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"post_editor_audio","permValue":true,"isCustom":false},{"permKey":"post_editor_audio_upload_number","permValue":10,"isCustom":false},{"permKey":"post_editor_document","permValue":true,"isCustom":false},{"permKey":"post_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"comment_editor_image","permValue":true,"isCustom":false},{"permKey":"comment_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"comment_editor_video","permValue":false,"isCustom":false},{"permKey":"comment_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"comment_editor_audio","permValue":false,"isCustom":false},{"permKey":"comment_editor_audio_upload_number","permValue":10,"isCustom":false},{"permKey":"comment_editor_document","permValue":false,"isCustom":false},{"permKey":"comment_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"image_max_size","permValue":5,"isCustom":false},{"permKey":"video_max_size","permValue":50,"isCustom":false},{"permKey":"video_max_time","permValue":15,"isCustom":false},{"permKey":"audio_max_size","permValue":50,"isCustom":false},{"permKey":"audio_max_time","permValue":60,"isCustom":false},{"permKey":"document_max_size","permValue":10,"isCustom":false},{"permKey":"download_file_count","permValue":999,"isCustom":false}]',
                'rating' => 2,
                'is_enable' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
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
                'permissions' => '[{"permKey":"content_view","permValue":true,"isCustom":false},{"permKey":"dialog","permValue":true,"isCustom":false},{"permKey":"post_publish","permValue":false,"isCustom":false},{"permKey":"post_review","permValue":false,"isCustom":false},{"permKey":"post_email_verify","permValue":false,"isCustom":false},{"permKey":"post_phone_verify","permValue":false,"isCustom":false},{"permKey":"post_real_name_verify","permValue":false,"isCustom":false},{"permKey":"post_limit_status","permValue":false,"isCustom":false},{"permKey":"post_limit_type","permValue":1,"isCustom":false},{"permKey":"post_limit_period_start","permValue":"2022-06-01 22:30:00","isCustom":false},{"permKey":"post_limit_period_end","permValue":"2022-06-08 08:00:00","isCustom":false},{"permKey":"post_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"post_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"post_limit_rule","permValue":1,"isCustom":false},{"permKey":"post_minute_interval","permValue":1,"isCustom":false},{"permKey":"post_draft_count","permValue":10,"isCustom":false},{"permKey":"comment_publish","permValue":false,"isCustom":false},{"permKey":"comment_review","permValue":false,"isCustom":false},{"permKey":"comment_email_verify","permValue":false,"isCustom":false},{"permKey":"comment_phone_verify","permValue":false,"isCustom":false},{"permKey":"comment_real_name_verify","permValue":false,"isCustom":false},{"permKey":"comment_limit_status","permValue":false,"isCustom":false},{"permKey":"comment_limit_type","permValue":1,"isCustom":false},{"permKey":"comment_limit_period_start","permValue":"2021-06-01 22:30:00","isCustom":false},{"permKey":"comment_limit_period_end","permValue":"2021-06-08 08:00:00","isCustom":false},{"permKey":"comment_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"comment_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"comment_limit_rule","permValue":1,"isCustom":false},{"permKey":"comment_minute_interval","permValue":1,"isCustom":false},{"permKey":"comment_draft_count","permValue":10,"isCustom":false},{"permKey":"post_editor_image","permValue":true,"isCustom":false},{"permKey":"post_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"post_editor_video","permValue":true,"isCustom":false},{"permKey":"post_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"post_editor_audio","permValue":true,"isCustom":false},{"permKey":"post_editor_audio_upload_number","permValue":10,"isCustom":false},{"permKey":"post_editor_document","permValue":true,"isCustom":false},{"permKey":"post_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"comment_editor_image","permValue":true,"isCustom":false},{"permKey":"comment_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"comment_editor_video","permValue":false,"isCustom":false},{"permKey":"comment_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"comment_editor_audio","permValue":false,"isCustom":false},{"permKey":"comment_editor_audio_upload_number","permValue":10,"isCustom":false},{"permKey":"comment_editor_document","permValue":false,"isCustom":false},{"permKey":"comment_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"image_max_size","permValue":5,"isCustom":false},{"permKey":"video_max_size","permValue":50,"isCustom":false},{"permKey":"video_max_time","permValue":15,"isCustom":false},{"permKey":"audio_max_size","permValue":50,"isCustom":false},{"permKey":"audio_max_time","permValue":60,"isCustom":false},{"permKey":"document_max_size","permValue":10,"isCustom":false},{"permKey":"download_file_count","permValue":999,"isCustom":false}]',
                'rating' => 3,
                'is_enable' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
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
                'permissions' => '[{"permKey":"content_view","permValue":true,"isCustom":false},{"permKey":"dialog","permValue":true,"isCustom":false},{"permKey":"post_publish","permValue":true,"isCustom":false},{"permKey":"post_review","permValue":false,"isCustom":false},{"permKey":"post_email_verify","permValue":false,"isCustom":false},{"permKey":"post_phone_verify","permValue":false,"isCustom":false},{"permKey":"post_real_name_verify","permValue":false,"isCustom":false},{"permKey":"post_limit_status","permValue":false,"isCustom":false},{"permKey":"post_limit_type","permValue":1,"isCustom":false},{"permKey":"post_limit_period_start","permValue":"2022-06-01 22:30:00","isCustom":false},{"permKey":"post_limit_period_end","permValue":"2022-06-08 08:00:00","isCustom":false},{"permKey":"post_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"post_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"post_limit_rule","permValue":1,"isCustom":false},{"permKey":"post_minute_interval","permValue":1,"isCustom":false},{"permKey":"post_draft_count","permValue":10,"isCustom":false},{"permKey":"comment_publish","permValue":true,"isCustom":false},{"permKey":"comment_review","permValue":false,"isCustom":false},{"permKey":"comment_email_verify","permValue":false,"isCustom":false},{"permKey":"comment_phone_verify","permValue":false,"isCustom":false},{"permKey":"comment_real_name_verify","permValue":false,"isCustom":false},{"permKey":"comment_limit_status","permValue":false,"isCustom":false},{"permKey":"comment_limit_type","permValue":1,"isCustom":false},{"permKey":"comment_limit_period_start","permValue":"2021-06-01 22:30:00","isCustom":false},{"permKey":"comment_limit_period_end","permValue":"2021-06-08 08:00:00","isCustom":false},{"permKey":"comment_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"comment_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"comment_limit_rule","permValue":1,"isCustom":false},{"permKey":"comment_minute_interval","permValue":1,"isCustom":false},{"permKey":"comment_draft_count","permValue":10,"isCustom":false},{"permKey":"post_editor_image","permValue":true,"isCustom":false},{"permKey":"post_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"post_editor_video","permValue":true,"isCustom":false},{"permKey":"post_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"post_editor_audio","permValue":true,"isCustom":false},{"permKey":"post_editor_audio_upload_number","permValue":10,"isCustom":false},{"permKey":"post_editor_document","permValue":true,"isCustom":false},{"permKey":"post_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"comment_editor_image","permValue":true,"isCustom":false},{"permKey":"comment_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"comment_editor_video","permValue":false,"isCustom":false},{"permKey":"comment_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"comment_editor_audio","permValue":false,"isCustom":false},{"permKey":"comment_editor_audio_upload_number","permValue":10,"isCustom":false},{"permKey":"comment_editor_document","permValue":false,"isCustom":false},{"permKey":"comment_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"image_max_size","permValue":5,"isCustom":false},{"permKey":"video_max_size","permValue":50,"isCustom":false},{"permKey":"video_max_time","permValue":15,"isCustom":false},{"permKey":"audio_max_size","permValue":50,"isCustom":false},{"permKey":"audio_max_time","permValue":60,"isCustom":false},{"permKey":"document_max_size","permValue":10,"isCustom":false},{"permKey":"download_file_count","permValue":999,"isCustom":false}]',
                'rating' => 4,
                'is_enable' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ]);
    }
}
