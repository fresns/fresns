<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class TempCallbackContent extends Model
{
    const TYPE_UNKNOWN = 1;
    const TYPE_AUTHORIZATION = 2;
    const TYPE_TOKEN = 3;
    const TYPE_ACCOUNT = 4;
    const TYPE_WALLET = 5;
    const TYPE_USER = 6;
    const TYPE_GROUP = 7;
    const TYPE_HASHTAG = 8;
    const TYPE_GEOTAG = 9;
    const TYPE_POST = 10;
    const TYPE_COMMENT = 11;
    const TYPE_ARCHIVE = 12;
    const TYPE_EXTEND = 13;
    const TYPE_OPERATION = 14;
    const TYPE_FILE = 15;
    const TYPE_LOCATION_INFO = 16;

    use Traits\IsEnabledTrait;

    protected $casts = [
        'content' => 'json',
    ];
}
