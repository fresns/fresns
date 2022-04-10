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
    const OBJECT_TYPE_ACCOUNT_LOGIN = 5;
    const OBJECT_TYPE_USER_LOGIN = 8;

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [

    ];

    // Log Type
    const SESSION_OBJECT_TYPE_ARR = [
        'Unknown' => 1,
        'Panel Login' => 2,
        'Register Account' => 3,
        'Delete Account' => 4,
        'Account Login' => 5,
        'Reset Account Password' => 6,
        'Modify Account Information' => 7,
        'User Login' => 8,
        'Modify User Information' => 9,
        'Wallet Trading Decrease' => 10,
        'Wallet Trading Increase' => 11,
        'Create Draft Post' => 12,
        'Create Draft Comment' => 13,
        'Publish Post Content' => 14,
        'Publish Comment Content' => 15,
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
    ];
}
