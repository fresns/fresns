<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsHashtags;

use App\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'hashtags';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [
        'huri' => ['field' => 'slug', 'op' => '='],
        'viewCountGt' => ['field' => 'view_count', 'op' => '>='],
        'viewCountLt' => ['field' => 'view_count', 'op' => '<='],
        'likeCountGt' => ['field' => 'like_count', 'op' => '>='],
        'likeCountLt' => ['field' => 'like_count', 'op' => '<='],
        'followCountGt' => ['field' => 'follow_count', 'op' => '>='],
        'followCountLt' => ['field' => 'follow_count', 'op' => '<='],
        'shieldCountGt' => ['field' => 'shield_count', 'op' => '>='],
        'shieldCountLt' => ['field' => 'shield_count', 'op' => '<='],
        'postCountGt' => ['field' => 'post_count', 'op' => '>='],
        'postCountLt' => ['field' => 'post_count', 'op' => '<='],
        'essenceCountGt' => ['field' => 'essence_count', 'op' => '>='],
        'essenceCountLt' => ['field' => 'essence_count', 'op' => '<='],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'uuid' => 'uuid',
        'name' => 'name',
        'description' => 'description',
        'cover_file_id' => 'cover_file_id',
        'cover_file_url' => 'cover_file_url',
        'member_id' => 'member_id',
        'view_count' => 'view_count',
        'like_count' => 'like_count',
        'follow_count' => 'follow_count',
        'shield_count' => 'shield_count',
        'post_count' => 'post_count',
        'comment_count' => 'comment_count',
        'essence_count' => 'essence_count',
        'is_enable' => 'is_enable',
    ];
}
