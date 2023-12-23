<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class AppCallback extends Model
{
    const TYPE_CUSTOMIZE = 1;
    const TYPE_RELOAD = 2;
    const TYPE_TOKEN = 3;
    const TYPE_ACCOUNT = 4;
    const TYPE_USER = 5;
    const TYPE_GROUP = 6;
    const TYPE_HASHTAG = 7;
    const TYPE_GEOTAG = 8;
    const TYPE_POST = 9;
    const TYPE_COMMENT = 10;
    const TYPE_ARCHIVE = 11;
    const TYPE_EXTEND = 12;
    const TYPE_OPERATION = 13;
    const TYPE_FILE = 14;
    const TYPE_MAP = 15;

    protected $casts = [
        'content' => 'json',
    ];
}
