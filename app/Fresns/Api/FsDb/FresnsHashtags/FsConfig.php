<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsHashtags;

use App\Fresns\Api\Base\Config\BaseConfig;

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
        'blockCountGt' => ['field' => 'block_count', 'op' => '>='],
        'blockCountLt' => ['field' => 'block_count', 'op' => '<='],
        'postCountGt' => ['field' => 'post_count', 'op' => '>='],
        'postCountLt' => ['field' => 'post_count', 'op' => '<='],
        'digestCountGt' => ['field' => 'digest_count', 'op' => '>='],
        'digestCountLt' => ['field' => 'digest_count', 'op' => '<='],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'name' => 'name',
        'description' => 'description',
        'cover_file_id' => 'cover_file_id',
        'cover_file_url' => 'cover_file_url',
        'user_id' => 'user_id',
        'view_count' => 'view_count',
        'like_count' => 'like_count',
        'follow_count' => 'follow_count',
        'block_count' => 'block_count',
        'post_count' => 'post_count',
        'comment_count' => 'comment_count',
        'digest_count' => 'digest_count',
        'is_enable' => 'is_enable',
    ];
}
