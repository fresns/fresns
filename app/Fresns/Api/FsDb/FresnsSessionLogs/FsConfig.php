<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsSessionLogs;

use App\Fresns\Api\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'session_logs';

    // Recording behavior results
    // 0-Unknown or under implementation
    // 1-Failure
    // 2-Success
    const OBJECT_RESULT_DEFAULT = 0;
    const OBJECT_RESULT_ERROR = 1;
    const OBJECT_RESULT_SUCCESS = 2;

    // Log Type Relationships
    const OBJECT_TYPE_ACCOUNT_LOGIN = 3;
    const OBJECT_TYPE_USER_LOGIN = 7;
    const OBJECT_TYPE_PLUGIN = 15;

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [

    ];

    // Log Type
    const SESSION_OBJECT_TYPE_ARR = [
        'Unknown' => 1,
        'Account Register' => 2,
        'Account Login' => 3,
        'Delete Account' => 4,
        'Reset Account Password' => 5,
        'Modify Account Information' => 6,
        'User Login' => 7,
        'Modify User Information' => 8,
        'Wallet Trading Decrease' => 9,
        'Wallet Trading Increase' => 10,
        'Create Draft Post' => 11,
        'Create Draft Comment' => 12,
        'Publish Post Content' => 13,
        'Publish Comment Content' => 14,
        'Timed Task' => 15,
        'Console Login' => 16,
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
    ];
}
