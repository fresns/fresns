<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Role extends Model
{
    use Traits\IsEnabledTrait;

    protected $casts = [
        'name' => 'json',
        'permissions' => 'json',
        'more_info' => 'json',
    ];
}
