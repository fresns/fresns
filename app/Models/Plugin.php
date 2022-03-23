<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Plugin extends Model
{
    protected $casts = [
        'scene' => 'array',
    ];

    public function scopeType($query, $value)
    {
        return $query->where('type', $value);
    }
}
