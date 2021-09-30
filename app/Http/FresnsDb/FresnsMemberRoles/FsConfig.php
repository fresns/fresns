<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsMemberRoles;

use App\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'member_roles';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [

    ];

    // Role Type
    const TYPE_OPTION = [
        ['key' => 1, 'text' => 'Management'],
        ['key' => 2, 'text' => 'Configuration'],
        ['key' => 3, 'text' => 'General'],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'name' => 'name',
        'rank_num' => 'rank_num',
        'is_enable' => 'is_enable',
        'type' => 'type',
        'icon_file_id' => 'icon_file_id',
        'icon_file_url' => 'icon_file_url',
        'more_json' => 'more_json',
        'is_display_name' => 'is_display_name',
        'is_display_icon' => 'is_display_icon',
        'nickname_color' => 'nickname_color',
        'permission' => 'permission',
    ];
}
