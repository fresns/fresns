<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Extend extends Model
{
    const TYPE_TEXT = 1;
    const TYPE_INFO = 2;
    const TYPE_ACTION = 3;

    const INFO_TYPE_BASIC = 1;
    const INFO_TYPE_BIG = 2;
    const INFO_TYPE_PORTRAIT = 3;
    const INFO_TYPE_LANDSCAPE = 4;

    use Traits\IsEnabledTrait;
    use Traits\FsidTrait;
    use Traits\ExtendServiceTrait;

    protected $casts = [
        'content' => 'json',
        'action_items' => 'json',
    ];

    public function getFsidKey()
    {
        return 'eid';
    }
}
