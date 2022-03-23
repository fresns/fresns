<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsPluginCallbacks;

use App\Fresns\Api\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'plugin_callbacks';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [
        'uuid' => ['field' => 'uuid', 'op' => '='],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'plugin_unikey' => 'plugin_unikey',
        'user_id' => 'user_id',
        'uuid' => 'uuid',
        'types' => 'types',
        'content' => 'content',
        'status' => 'status',
        'use_plugin_unikey' => 'use_plugin_unikey',
    ];
}
