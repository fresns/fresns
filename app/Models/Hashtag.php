<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Hashtag extends Model
{
    use Traits\HashtagServiceTrait;
    use Traits\IsEnabledTrait;

    protected $casts = [
        'more_info' => 'json',
    ];

    protected $dates = [
        'last_post_at',
        'last_comment_at',
    ];
}
