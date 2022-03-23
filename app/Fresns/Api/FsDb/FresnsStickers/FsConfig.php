<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsStickers;

use App\Fresns\Api\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'stickers';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [
        'parent_id' => ['field' => 'parent_id', 'op' => '='],
    ];

    // Sticker Group Number
    const TYPE_GROUP = 2;

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'code' => 'code',
        'name' => 'name',
        'image_file_id' => 'image_file_id',
        'image_file_url' => 'image_file_url',
        'type' => 'type',
        'parent_id' => 'parent_id',
        'rank_num' => 'rank_num',
        'is_enable' => 'is_enable',
    ];
}
