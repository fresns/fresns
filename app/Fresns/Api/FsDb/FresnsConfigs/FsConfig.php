<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsConfigs;

use App\Fresns\Api\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'configs';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [
        'ids' => ['field' => 'id', 'op' => 'IN'],
        'item_key' => ['field' => 'item_key', 'op' => '='],
        'item_key_no' => ['field' => 'item_key', 'op' => '<>'],
        'item_keys' => ['field' => 'item_key', 'op' => 'IN'],
        'item_tag' => ['field' => 'item_tag', 'op' => '='],
        'is_api' => ['field' => 'is_api', 'op' => '='],
    ];

    // Subscription History
    const SUB_PLUGINS = 'subscribe_plugins';

    // Dictionary Data
    const CONTINENTS = 'continents';
    const MAP = 'maps';
    const LANGUAGE_CODES = 'language_codes';
    const AREAS = 'areas_codes';

    // Language Tag
    const LANGUAGES = 'languages';

    // Languages
    const LANGUAGE_STATUS = 'language_status';
    const DEFAULT_LANGUAGE = 'default_language';
    const LANG_SETTINGS = 'language_menus';

    // Number of days between user name changes
    const USERNAME_EDIT = 'username_edit';
    const USERNAME_MIN = 'username_min';
    const USERNAME_MAX = 'username_max';

    // Number of days between user nickname changes
    const NICKNAME_EDIT = 'nickname_edit';

    // Storage Config
    const IMAGE_STORAGE = 'storageImages';
    const VIDEO_STORAGE = 'storageVideos';
    const AUDIO_STORAGE = 'storageAudios';
    const DOCUMENT_STORAGE = 'storageDocuments';

    // System Config
    const BACKEND_DOMAIN = 'backend_domain';
    const BACKEND_PATH = 'backend_path';
    const SITE_DOMAIN = 'site_domain';

    // Distance unit
    const LENGTHUNITS_OPTION = [
        ['key' => 'km', 'text' => 'Kilometer (km)'],
        ['key' => 'mi', 'text' => 'Mile (mi)'],
    ];

    // Date Format
    const DATE_OPTION = [
        ['key' => 1, 'text' => 'yyyy-mm-dd'],
        ['key' => 2, 'text' => 'yyyy/mm/dd'],
        ['key' => 3, 'text' => 'yyyy.mm.dd'],
        ['key' => 4, 'text' => 'mm-dd-yyyy'],
        ['key' => 5, 'text' => 'mm/dd/yyyy'],
        ['key' => 6, 'text' => 'mm.dd.yyyy'],
        ['key' => 7, 'text' => 'dd-mm-yyyy'],
        ['key' => 8, 'text' => 'dd/mm/yyyy'],
        ['key' => 9, 'text' => 'dd.mm.yyyy'],
    ];

    // Private mode display method
    const SITE_PRIVATE_END_OPTION = [
        ['key' => 1, 'text' => 'Content not visible'],
        ['key' => 2, 'text' => 'Pre-expiration content visible, new content not visible'],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'item_key' => 'item_key',
        'item_value' => 'item_value',
        'item_type' => 'item_type',
        'item_tag' => 'item_tag',
        'is_multilingual' => 'is_multilingual',
        'is_api' => 'is_api',
        'is_enable' => 'is_enable',
    ];
}
