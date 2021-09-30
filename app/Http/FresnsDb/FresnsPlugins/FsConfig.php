<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsPlugins;

use App\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'plugins';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [

    ];

    const ENABLE_FALSE = 0;
    const ENABLE_TRUE = 1;

    // Plugin download or not
    const NO_DOWNLOAD = 0;
    const DOWNLOAD = 1;

    // Is the new version
    const NO_NEWVISION = 0;
    const NEWVISION = 1;

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'unikey' => 'unikey',
        'name' => 'name',
        'type' => 'type',
        'image' => 'image',
        'description' => 'description',
        'version' => 'version',
        'version_int' => 'version_int',
        'author' => 'author',
        'author_link' => 'author_link',
        'scene' => 'scene',
        'plugin_domain' => 'plugin_domain',
        'access_path' => 'access_path',
        'setting_path' => 'setting_path',
        'is_enable' => 'is_enable',
        'more_json' => 'more_json',
        'is_upgrade' => 'is_upgrade',
        'upgrade_version' => 'upgrade_version',
        'upgrade_version_int' => 'upgrade_version_int',
    ];
}
