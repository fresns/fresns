<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsSessionKeys;

use App\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'session_keys';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [

    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'name' => 'name',
        'type' => 'type',
        'platform_id' => 'platform_id',
        'plugin_unikey' => 'plugin_unikey',
        'app_id' => 'app_id',
        'app_secret' => 'app_secret',
        'is_enable' => 'is_enable',

    ];
}
