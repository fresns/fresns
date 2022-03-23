<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsUserLikes;

use App\Fresns\Api\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'user_likes';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [
        'user_id' => ['field' => 'user_id', 'op' => '='],
        'type' => ['field' => 'like_type', 'op' => '='],
        'like_id' => ['field' => 'like_id', 'op' => '='],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'user_id' => 'user_id',
        'like_type' => 'like_type',
        'like_id' => 'like_id',
    ];
}
