<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Seo extends Model
{
    const TYPE_USER = 1;
    const TYPE_GROUP = 2;
    const TYPE_HASHTAG = 3;
    const TYPE_GEOTAG = 4;
    const TYPE_POST = 5;
    const TYPE_COMMENT = 6;

    protected $table = 'seo';

    protected $casts = [
        'title' => 'json',
        'keywords' => 'json',
        'description' => 'json',
    ];

    public function scopeType($query, int $type)
    {
        return $query->where('usage_type', $type);
    }
}
