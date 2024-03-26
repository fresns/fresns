<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Fresns seed file.
     */
    public function run(): void
    {
        DB::table('roles')->delete();

        DB::table('roles')->insert([
            [
                'id' => 1,
                'rid' => 'administrator',
                'name' => '{"en":"Administrator","zh-Hans":"管理员","zh-Hant":"管理員"}',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'is_display_name' => 0,
                'is_display_icon' => 0,
                'nickname_color' => null,
                'permissions' => '[{"permKey":"content_view","permValue":true,"isCustom":false},{"permKey":"conversation","permValue":true,"isCustom":false},{"permKey":"content_link_handle","permValue":3,"isCustom":false},{"permKey":"post_publish","permValue":true,"isCustom":false},{"permKey":"post_review","permValue":false,"isCustom":false},{"permKey":"post_required_email","permValue":false,"isCustom":false},{"permKey":"post_required_phone","permValue":false,"isCustom":false},{"permKey":"post_required_kyc","permValue":false,"isCustom":false},{"permKey":"post_limit_status","permValue":false,"isCustom":false},{"permKey":"post_limit_type","permValue":1,"isCustom":false},{"permKey":"post_limit_period_start","permValue":"2022-06-01 22:30:00","isCustom":false},{"permKey":"post_limit_period_end","permValue":"2022-06-06 08:00:00","isCustom":false},{"permKey":"post_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"post_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"post_limit_rule","permValue":1,"isCustom":false},{"permKey":"post_second_interval","permValue":60,"isCustom":false},{"permKey":"post_draft_count","permValue":10,"isCustom":false},{"permKey":"comment_publish","permValue":true,"isCustom":false},{"permKey":"comment_review","permValue":false,"isCustom":false},{"permKey":"comment_required_email","permValue":false,"isCustom":false},{"permKey":"comment_required_phone","permValue":false,"isCustom":false},{"permKey":"comment_required_kyc","permValue":false,"isCustom":false},{"permKey":"comment_limit_status","permValue":false,"isCustom":false},{"permKey":"comment_limit_type","permValue":1,"isCustom":false},{"permKey":"comment_limit_period_start","permValue":"2022-06-01 22:30:00","isCustom":false},{"permKey":"comment_limit_period_end","permValue":"2022-06-06 08:00:00","isCustom":false},{"permKey":"comment_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"comment_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"comment_limit_rule","permValue":1,"isCustom":false},{"permKey":"comment_second_interval","permValue":60,"isCustom":false},{"permKey":"comment_draft_count","permValue":10,"isCustom":false},{"permKey":"post_editor_image","permValue":true,"isCustom":false},{"permKey":"post_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"post_editor_video","permValue":true,"isCustom":false},{"permKey":"post_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"post_editor_audio","permValue":true,"isCustom":false},{"permKey":"post_editor_audio_upload_number","permValue":2,"isCustom":false},{"permKey":"post_editor_document","permValue":true,"isCustom":false},{"permKey":"post_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"comment_editor_image","permValue":true,"isCustom":false},{"permKey":"comment_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"comment_editor_video","permValue":false,"isCustom":false},{"permKey":"comment_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"comment_editor_audio","permValue":false,"isCustom":false},{"permKey":"comment_editor_audio_upload_number","permValue":2,"isCustom":false},{"permKey":"comment_editor_document","permValue":false,"isCustom":false},{"permKey":"comment_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"image_max_size","permValue":5,"isCustom":false},{"permKey":"video_max_size","permValue":50,"isCustom":false},{"permKey":"video_max_time","permValue":60,"isCustom":false},{"permKey":"audio_max_size","permValue":50,"isCustom":false},{"permKey":"audio_max_time","permValue":60,"isCustom":false},{"permKey":"document_max_size","permValue":10,"isCustom":false},{"permKey":"follow_user_max_count","permValue":500,"isCustom":false},{"permKey":"block_user_max_count","permValue":500,"isCustom":false},{"permKey":"download_file_count","permValue":10,"isCustom":false}]',
                'more_info' => null,
                'rank_state' => 1,
                'sort_order' => 1,
                'is_enabled' => 1,
                'created_at' => '2022-10-18 10:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'rid' => 'interdiction',
                'name' => '{"en":"Interdiction","zh-Hans":"禁言中","zh-Hant":"禁言中"}',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'is_display_name' => 0,
                'is_display_icon' => 0,
                'nickname_color' => null,
                'permissions' => '[{"permKey":"content_view","permValue":true,"isCustom":false},{"permKey":"conversation","permValue":false,"isCustom":false},{"permKey":"content_link_handle","permValue":1,"isCustom":false},{"permKey":"post_publish","permValue":false,"isCustom":false},{"permKey":"post_review","permValue":false,"isCustom":false},{"permKey":"post_required_email","permValue":false,"isCustom":false},{"permKey":"post_required_phone","permValue":false,"isCustom":false},{"permKey":"post_required_kyc","permValue":false,"isCustom":false},{"permKey":"post_limit_status","permValue":false,"isCustom":false},{"permKey":"post_limit_type","permValue":1,"isCustom":false},{"permKey":"post_limit_period_start","permValue":"2022-06-01 22:30:00","isCustom":false},{"permKey":"post_limit_period_end","permValue":"2022-06-06 08:00:00","isCustom":false},{"permKey":"post_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"post_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"post_limit_rule","permValue":1,"isCustom":false},{"permKey":"post_second_interval","permValue":60,"isCustom":false},{"permKey":"post_draft_count","permValue":10,"isCustom":false},{"permKey":"comment_publish","permValue":false,"isCustom":false},{"permKey":"comment_review","permValue":false,"isCustom":false},{"permKey":"comment_required_email","permValue":false,"isCustom":false},{"permKey":"comment_required_phone","permValue":false,"isCustom":false},{"permKey":"comment_required_kyc","permValue":false,"isCustom":false},{"permKey":"comment_limit_status","permValue":false,"isCustom":false},{"permKey":"comment_limit_type","permValue":1,"isCustom":false},{"permKey":"comment_limit_period_start","permValue":"2022-06-01 22:30:00","isCustom":false},{"permKey":"comment_limit_period_end","permValue":"2022-06-06 08:00:00","isCustom":false},{"permKey":"comment_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"comment_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"comment_limit_rule","permValue":1,"isCustom":false},{"permKey":"comment_second_interval","permValue":60,"isCustom":false},{"permKey":"comment_draft_count","permValue":10,"isCustom":false},{"permKey":"post_editor_image","permValue":true,"isCustom":false},{"permKey":"post_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"post_editor_video","permValue":true,"isCustom":false},{"permKey":"post_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"post_editor_audio","permValue":true,"isCustom":false},{"permKey":"post_editor_audio_upload_number","permValue":2,"isCustom":false},{"permKey":"post_editor_document","permValue":true,"isCustom":false},{"permKey":"post_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"comment_editor_image","permValue":true,"isCustom":false},{"permKey":"comment_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"comment_editor_video","permValue":false,"isCustom":false},{"permKey":"comment_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"comment_editor_audio","permValue":false,"isCustom":false},{"permKey":"comment_editor_audio_upload_number","permValue":2,"isCustom":false},{"permKey":"comment_editor_document","permValue":false,"isCustom":false},{"permKey":"comment_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"image_max_size","permValue":5,"isCustom":false},{"permKey":"video_max_size","permValue":50,"isCustom":false},{"permKey":"video_max_time","permValue":60,"isCustom":false},{"permKey":"audio_max_size","permValue":50,"isCustom":false},{"permKey":"audio_max_time","permValue":60,"isCustom":false},{"permKey":"document_max_size","permValue":10,"isCustom":false},{"permKey":"follow_user_max_count","permValue":500,"isCustom":false},{"permKey":"block_user_max_count","permValue":500,"isCustom":false},{"permKey":"download_file_count","permValue":0,"isCustom":false}]',
                'more_info' => null,
                'rank_state' => 1,
                'sort_order' => 2,
                'is_enabled' => 1,
                'created_at' => '2022-10-18 10:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'rid' => 'pendingreview',
                'name' => '{"en":"Pending Review","zh-Hans":"待审核","zh-Hant":"待審核"}',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'is_display_name' => 0,
                'is_display_icon' => 0,
                'nickname_color' => null,
                'permissions' => '[{"permKey":"content_view","permValue":true,"isCustom":false},{"permKey":"conversation","permValue":false,"isCustom":false},{"permKey":"content_link_handle","permValue":1,"isCustom":false},{"permKey":"post_publish","permValue":false,"isCustom":false},{"permKey":"post_review","permValue":false,"isCustom":false},{"permKey":"post_required_email","permValue":false,"isCustom":false},{"permKey":"post_required_phone","permValue":false,"isCustom":false},{"permKey":"post_required_kyc","permValue":false,"isCustom":false},{"permKey":"post_limit_status","permValue":false,"isCustom":false},{"permKey":"post_limit_type","permValue":1,"isCustom":false},{"permKey":"post_limit_period_start","permValue":"2022-06-01 22:30:00","isCustom":false},{"permKey":"post_limit_period_end","permValue":"2022-06-06 08:00:00","isCustom":false},{"permKey":"post_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"post_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"post_limit_rule","permValue":1,"isCustom":false},{"permKey":"post_second_interval","permValue":60,"isCustom":false},{"permKey":"post_draft_count","permValue":10,"isCustom":false},{"permKey":"comment_publish","permValue":false,"isCustom":false},{"permKey":"comment_review","permValue":false,"isCustom":false},{"permKey":"comment_required_email","permValue":false,"isCustom":false},{"permKey":"comment_required_phone","permValue":false,"isCustom":false},{"permKey":"comment_required_kyc","permValue":false,"isCustom":false},{"permKey":"comment_limit_status","permValue":false,"isCustom":false},{"permKey":"comment_limit_type","permValue":1,"isCustom":false},{"permKey":"comment_limit_period_start","permValue":"2022-06-01 22:30:00","isCustom":false},{"permKey":"comment_limit_period_end","permValue":"2022-06-06 08:00:00","isCustom":false},{"permKey":"comment_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"comment_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"comment_limit_rule","permValue":1,"isCustom":false},{"permKey":"comment_second_interval","permValue":60,"isCustom":false},{"permKey":"comment_draft_count","permValue":10,"isCustom":false},{"permKey":"post_editor_image","permValue":true,"isCustom":false},{"permKey":"post_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"post_editor_video","permValue":true,"isCustom":false},{"permKey":"post_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"post_editor_audio","permValue":true,"isCustom":false},{"permKey":"post_editor_audio_upload_number","permValue":2,"isCustom":false},{"permKey":"post_editor_document","permValue":true,"isCustom":false},{"permKey":"post_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"comment_editor_image","permValue":true,"isCustom":false},{"permKey":"comment_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"comment_editor_video","permValue":false,"isCustom":false},{"permKey":"comment_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"comment_editor_audio","permValue":false,"isCustom":false},{"permKey":"comment_editor_audio_upload_number","permValue":2,"isCustom":false},{"permKey":"comment_editor_document","permValue":false,"isCustom":false},{"permKey":"comment_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"image_max_size","permValue":5,"isCustom":false},{"permKey":"video_max_size","permValue":50,"isCustom":false},{"permKey":"video_max_time","permValue":60,"isCustom":false},{"permKey":"audio_max_size","permValue":50,"isCustom":false},{"permKey":"audio_max_time","permValue":60,"isCustom":false},{"permKey":"document_max_size","permValue":10,"isCustom":false},{"permKey":"follow_user_max_count","permValue":500,"isCustom":false},{"permKey":"block_user_max_count","permValue":500,"isCustom":false},{"permKey":"download_file_count","permValue":0,"isCustom":false}]',
                'more_info' => null,
                'rank_state' => 1,
                'sort_order' => 3,
                'is_enabled' => 1,
                'created_at' => '2022-10-18 10:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'rid' => 'generaluser',
                'name' => '{"en":"General User","zh-Hans":"普通会员","zh-Hant":"普通會員"}',
                'icon_file_id' => null,
                'icon_file_url' => null,
                'is_display_name' => 0,
                'is_display_icon' => 0,
                'nickname_color' => null,
                'permissions' => '[{"permKey":"content_view","permValue":true,"isCustom":false},{"permKey":"conversation","permValue":true,"isCustom":false},{"permKey":"content_link_handle","permValue":2,"isCustom":false},{"permKey":"post_publish","permValue":true,"isCustom":false},{"permKey":"post_review","permValue":false,"isCustom":false},{"permKey":"post_required_email","permValue":false,"isCustom":false},{"permKey":"post_required_phone","permValue":false,"isCustom":false},{"permKey":"post_required_kyc","permValue":false,"isCustom":false},{"permKey":"post_limit_status","permValue":false,"isCustom":false},{"permKey":"post_limit_type","permValue":1,"isCustom":false},{"permKey":"post_limit_period_start","permValue":"2022-06-01 22:30:00","isCustom":false},{"permKey":"post_limit_period_end","permValue":"2022-06-06 08:00:00","isCustom":false},{"permKey":"post_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"post_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"post_limit_rule","permValue":1,"isCustom":false},{"permKey":"post_second_interval","permValue":60,"isCustom":false},{"permKey":"post_draft_count","permValue":10,"isCustom":false},{"permKey":"comment_publish","permValue":true,"isCustom":false},{"permKey":"comment_review","permValue":false,"isCustom":false},{"permKey":"comment_required_email","permValue":false,"isCustom":false},{"permKey":"comment_required_phone","permValue":false,"isCustom":false},{"permKey":"comment_required_kyc","permValue":false,"isCustom":false},{"permKey":"comment_limit_status","permValue":false,"isCustom":false},{"permKey":"comment_limit_type","permValue":1,"isCustom":false},{"permKey":"comment_limit_period_start","permValue":"2022-06-01 22:30:00","isCustom":false},{"permKey":"comment_limit_period_end","permValue":"2022-06-06 08:00:00","isCustom":false},{"permKey":"comment_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"comment_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"comment_limit_rule","permValue":1,"isCustom":false},{"permKey":"comment_second_interval","permValue":60,"isCustom":false},{"permKey":"comment_draft_count","permValue":10,"isCustom":false},{"permKey":"post_editor_image","permValue":true,"isCustom":false},{"permKey":"post_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"post_editor_video","permValue":true,"isCustom":false},{"permKey":"post_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"post_editor_audio","permValue":true,"isCustom":false},{"permKey":"post_editor_audio_upload_number","permValue":2,"isCustom":false},{"permKey":"post_editor_document","permValue":true,"isCustom":false},{"permKey":"post_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"comment_editor_image","permValue":true,"isCustom":false},{"permKey":"comment_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"comment_editor_video","permValue":false,"isCustom":false},{"permKey":"comment_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"comment_editor_audio","permValue":false,"isCustom":false},{"permKey":"comment_editor_audio_upload_number","permValue":2,"isCustom":false},{"permKey":"comment_editor_document","permValue":false,"isCustom":false},{"permKey":"comment_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"image_max_size","permValue":5,"isCustom":false},{"permKey":"video_max_size","permValue":50,"isCustom":false},{"permKey":"video_max_time","permValue":60,"isCustom":false},{"permKey":"audio_max_size","permValue":50,"isCustom":false},{"permKey":"audio_max_time","permValue":60,"isCustom":false},{"permKey":"document_max_size","permValue":10,"isCustom":false},{"permKey":"follow_user_max_count","permValue":500,"isCustom":false},{"permKey":"block_user_max_count","permValue":500,"isCustom":false},{"permKey":"download_file_count","permValue":10,"isCustom":false}]',
                'more_info' => null,
                'rank_state' => 1,
                'sort_order' => 4,
                'is_enabled' => 1,
                'created_at' => '2022-10-18 10:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ]);
    }
}
