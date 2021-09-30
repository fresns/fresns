<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsUserWalletLogs;

use App\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'user_wallet_logs';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [
        'user_id' => ['field' => 'user_id', 'op' => '='],
        'type' => ['field' => 'object_type', 'op' => '='],
        'status' => ['field' => 'is_enable', 'op' => '='],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
    ];
}
