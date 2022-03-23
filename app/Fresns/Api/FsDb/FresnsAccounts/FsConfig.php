<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsAccounts;

use App\Fresns\Api\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'accounts';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [

    ];

    // Wallet Type
    const PLUGIN_USAGERS_TYPE_1 = 1;
    const PLUGIN_USAGERS_TYPE_2 = 2;

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
    ];
}
