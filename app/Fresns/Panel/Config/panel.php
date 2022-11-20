<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Fresns\Panel\Http\Middleware\ChangeLocale;

return [
    'middleware' => [
        'web',
        ChangeLocale::class,
    ],

    'route_blacklist' => [
        'dashboard/',
        'systems/',
        'operations/',
        'extends/',
        'plugins/',
        'clients/',
        'market/',
    ],

    'langs' => [
        'en' => 'English - English',
        'es' => 'Español - Spanish',
        'fr' => 'Français - French',
        'de' => 'Deutsch - German',
        'it' => 'Italiano - Italian',
        'ja' => '日本語 - Japanese',
        'ko' => '한국어 - Korean',
        'ru' => 'Русский - Russian',
        'pt' => 'Português - Portuguese',
        'id' => 'Bahasa Indonesia - Indonesian',
        'hi' => 'हिन्दी - Hindi',
        'zh-Hans' => '简体中文 - Chinese (Simplified)',
        'zh-Hant' => '繁體中文 - Chinese (Traditional)',
    ],

    'role_default_permissions' => '[{"permKey":"content_view","permValue":true,"isCustom":false},{"permKey":"conversation","permValue":true,"isCustom":false},{"permKey":"content_link_handle","permValue":2,"isCustom":false},{"permKey":"post_publish","permValue":true,"isCustom":false},{"permKey":"post_review","permValue":false,"isCustom":false},{"permKey":"post_email_verify","permValue":false,"isCustom":false},{"permKey":"post_phone_verify","permValue":false,"isCustom":false},{"permKey":"post_real_name_verify","permValue":false,"isCustom":false},{"permKey":"post_limit_status","permValue":false,"isCustom":false},{"permKey":"post_limit_type","permValue":1,"isCustom":false},{"permKey":"post_limit_period_start","permValue":"2022-06-01 22:30:00","isCustom":false},{"permKey":"post_limit_period_end","permValue":"2022-06-06 08:00:00","isCustom":false},{"permKey":"post_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"post_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"post_limit_rule","permValue":1,"isCustom":false},{"permKey":"post_minute_interval","permValue":1,"isCustom":false},{"permKey":"post_draft_count","permValue":10,"isCustom":false},{"permKey":"comment_publish","permValue":true,"isCustom":false},{"permKey":"comment_review","permValue":false,"isCustom":false},{"permKey":"comment_email_verify","permValue":false,"isCustom":false},{"permKey":"comment_phone_verify","permValue":false,"isCustom":false},{"permKey":"comment_real_name_verify","permValue":false,"isCustom":false},{"permKey":"comment_limit_status","permValue":false,"isCustom":false},{"permKey":"comment_limit_type","permValue":1,"isCustom":false},{"permKey":"comment_limit_period_start","permValue":"2022-06-01 22:30:00","isCustom":false},{"permKey":"comment_limit_period_end","permValue":"2022-06-06 08:00:00","isCustom":false},{"permKey":"comment_limit_cycle_start","permValue":"23:00:00","isCustom":false},{"permKey":"comment_limit_cycle_end","permValue":"08:30:00","isCustom":false},{"permKey":"comment_limit_rule","permValue":1,"isCustom":false},{"permKey":"comment_minute_interval","permValue":1,"isCustom":false},{"permKey":"comment_draft_count","permValue":10,"isCustom":false},{"permKey":"post_editor_image","permValue":true,"isCustom":false},{"permKey":"post_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"post_editor_video","permValue":true,"isCustom":false},{"permKey":"post_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"post_editor_audio","permValue":true,"isCustom":false},{"permKey":"post_editor_audio_upload_number","permValue":2,"isCustom":false},{"permKey":"post_editor_document","permValue":true,"isCustom":false},{"permKey":"post_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"comment_editor_image","permValue":true,"isCustom":false},{"permKey":"comment_editor_image_upload_number","permValue":9,"isCustom":false},{"permKey":"comment_editor_video","permValue":false,"isCustom":false},{"permKey":"comment_editor_video_upload_number","permValue":1,"isCustom":false},{"permKey":"comment_editor_audio","permValue":false,"isCustom":false},{"permKey":"comment_editor_audio_upload_number","permValue":2,"isCustom":false},{"permKey":"comment_editor_document","permValue":false,"isCustom":false},{"permKey":"comment_editor_document_upload_number","permValue":10,"isCustom":false},{"permKey":"image_max_size","permValue":5,"isCustom":false},{"permKey":"video_max_size","permValue":50,"isCustom":false},{"permKey":"video_max_time","permValue":60,"isCustom":false},{"permKey":"audio_max_size","permValue":50,"isCustom":false},{"permKey":"audio_max_time","permValue":60,"isCustom":false},{"permKey":"document_max_size","permValue":10,"isCustom":false},{"permKey":"follow_user_max_count","permValue":500,"isCustom":false},{"permKey":"block_user_max_count","permValue":500,"isCustom":false},{"permKey":"download_file_count","permValue":10,"isCustom":false}]',
];
