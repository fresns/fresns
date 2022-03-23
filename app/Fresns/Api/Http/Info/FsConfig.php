<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Info;

class FsConfig
{
    // Verify Code Template
    const TEAMPLATE_1 = 1;
    const TEAMPLATE_2 = 2;
    const TEAMPLATE_3 = 3;
    const TEAMPLATE_4 = 4;
    const TEAMPLATE_7 = 7;

    // Country Code
    const COUNTRYCODE = 86;

    // Message Table Type
    const SOURCE_TYPE_1 = 1;
    const SOURCE_TYPE_2 = 2;
    const SOURCE_TYPE_3 = 3;
    const SOURCE_TYPE_4 = 4;
    const SOURCE_TYPE_5 = 5;
    const SOURCE_TYPE_6 = 6;

    // Reading Status
    const NO_READ = 1;
    const READED = 2;

    // Callbacks Status
    const NOT_USE_CALLBACKS = 1;

    // Configs Dictionary
    const CONFIGS_ITEM_KEY = [
        'platforms',
        'language_codes',
        'language_pack',
        'connects',
        'disable_names',
        'utc',
        'continents',
        'area_codes',
        'currency_codes',
        'storages',
        'maps',
        'default_language',
        'language_status',
        'language_menus',
    ];
}
