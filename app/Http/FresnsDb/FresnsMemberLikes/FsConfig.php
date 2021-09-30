<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsMemberLikes;

use App\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'member_likes';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [
        'member_id' => ['field' => 'member_id', 'op' => '='],
        'type' => ['field' => 'like_type', 'op' => '='],
        'like_id' => ['field' => 'like_id', 'op' => '='],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'member_id' => 'member_id',
        'like_type' => 'like_type',
        'like_id' => 'like_id',
    ];
}
