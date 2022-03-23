<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsAccountWalletLogs;

use App\Fresns\Api\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'account_wallet_logs';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [
        'account_id' => ['field' => 'account_id', 'op' => '='],
        'type' => ['field' => 'object_type', 'op' => '='],
        'status' => ['field' => 'is_enable', 'op' => '='],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
    ];
}
