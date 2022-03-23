<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsUsers;

use App\Fresns\Api\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'users';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [

    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'name' => 'name',
        'is_enable' => 'is_enable',
        'type' => 'type',
        'icon_file_id' => 'icon_file_id',
        'icon_file_url' => 'icon_file_url',
        'is_display_name' => 'is_display_name',
        'is_display_icon' => 'is_display_icon',
        'nickname_color' => 'nickname_color',
        'permission' => 'permission',

    ];

    // Role Type
    const TYPE_OPTION = [
        ['key' => 1, 'text' => 'Management'],
        ['key' => 2, 'text' => 'Configuration'],
        ['key' => 3, 'text' => 'General'],
    ];
}
