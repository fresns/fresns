<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class PluginCallback extends Model
{
    const TYPE_CUSTOM = 1;
    const TYPE_ACCOUNT = 2;
    const TYPE_MAP_INFO = 3;
    const TYPE_FILE = 4;
    const TYPE_ICON = 5;
    const TYPE_TIP = 6;
    const TYPE_EXTEND = 7;
    const TYPE_READ_ALLOW_CONFIG = 8;
    const TYPE_USER_LIST_CONFIG = 9;
    const TYPE_COMMENT_BTN_CONFIG = 10;
    const TYPE_COMMENT_PUBLIC_CONFIG = 11;

    const IS_USE_FALSE = false;
    const IS_USE_TRUE = true;

    protected $guarded = [];

    protected $dates = [
        'deleted_at',
    ];

    protected $casts = [
        'content' => 'json',
    ];
}
