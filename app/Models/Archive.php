<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Archive extends Model
{
    use Traits\ArchiveServiceTrait;

    const TYPE_USER = 1;
    const TYPE_GROUP = 2;
    const TYPE_HASHTAG = 3;
    const TYPE_GEOTAG = 4;
    const TYPE_POST = 5;
    const TYPE_COMMENT = 6;

    use Traits\IsEnabledTrait;

    protected $casts = [
        'name' => 'json',
        'description' => 'json',
        'element_options' => 'json',
    ];

    public function getElementOptionsAttribute($value)
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return $value ?? [];
    }

    public function scopeType($query, int $type)
    {
        return $query->where('usage_type', $type);
    }
}
