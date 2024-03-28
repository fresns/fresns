<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Geotag extends Model
{
    use Traits\FsidTrait;
    use Traits\IsEnabledTrait;
    use Traits\GeotagServiceTrait;

    protected $casts = [
        'name' => 'json',
        'description' => 'json',
        'district' => 'json',
        'address' => 'json',
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

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }
}
