<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsCommentLogs;

use App\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'comment_logs';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [
        'logId' => ['field' => 'id', 'op' => '='],
        'member_id' => ['field' => 'member_id', 'op' => '='],
        'status' => ['field' => 'state', 'op' => 'IN'],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'member_id' => 'member_id',
        'comment_id' => 'comment_id',
        'post_id' => 'post_id',
        'platform_id' => 'platform_id',
        'types' => 'types',
        'content' => 'content',
        'is_markdown' => 'is_markdown',
        'is_anonymous' => 'is_anonymous',
        'is_plugin_editor' => 'is_plugin_editor',
        'editor_unikey' => 'editor_unikey',
        'location_json' => 'location_json',
        'files_json' => 'files_json',
        'extends_json' => 'extends_json',
        'state' => 'state',
        'reason' => 'reason',
        'submit_at' => 'submit_at',
    ];
}
