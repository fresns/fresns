<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsNotifies;

use App\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'notifies';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [
        'type' => ['field' => 'source_type', 'op' => 'IN'],
        'member_id' => ['field' => 'member_id', 'op' => '='],
        'source_member_id' => ['field' => 'source_member_id', 'op' => '='],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
    ];
}
