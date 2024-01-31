<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Geotag extends Model
{
    use Traits\GeotagServiceTrait;
    use Traits\IsEnabledTrait;

    protected $casts = [
        'name' => 'json',
        'description' => 'json',
        'location_info' => 'json',
        'more_info' => 'json',
    ];

    protected $dates = [
        'last_post_at',
        'last_comment_at',
    ];

    public function getFsidKey()
    {
        return 'gtid';
    }
}
