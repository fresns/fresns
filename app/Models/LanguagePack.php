<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class LanguagePack extends Model
{
    protected $casts = [
        'lang_values' => 'json',
    ];
}
