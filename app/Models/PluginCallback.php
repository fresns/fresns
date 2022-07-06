<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class PluginCallback extends Model
{
    const TYPE_CUSTOMIZE = 1;
    const TYPE_RELOAD = 2;
    const TYPE_ACCOUNT = 3;
    const TYPE_MAP_INFO = 4;
    const TYPE_FILE = 5;
    const TYPE_OPERATION = 6;
    const TYPE_ARCHIVE = 7;
    const TYPE_EXTEND = 8;
    const TYPE_READ_ALLOW_CONFIG = 9;
    const TYPE_USER_LIST_CONFIG = 10;
    const TYPE_COMMENT_BTN_CONFIG = 11;
    const TYPE_COMMENT_PUBLIC_CONFIG = 12;

    const IS_USE_FALSE = false;
    const IS_USE_TRUE = true;

    protected $casts = [
        'content' => 'json',
    ];
}
