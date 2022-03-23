<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsNotifies;

use App\Fresns\Api\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'notifies';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [
        'type' => ['field' => 'source_type', 'op' => 'IN'],
        'user_id' => ['field' => 'user_id', 'op' => '='],
        'source_user_id' => ['field' => 'source_user_id', 'op' => '='],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
    ];
}
