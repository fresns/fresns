<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsMemberFollows;

use App\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'member_follows';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [

    ];

    // Target Type
    const FOLLOW_TYPE_1 = 1; //Member
    const FOLLOW_TYPE_2 = 2; //Group
    const FOLLOW_TYPE_3 = 3; //Hashtag
    const FOLLOW_TYPE_4 = 4; //Post
    const FOLLOW_TYPE_5 = 5; //Comment

    // Quantity per output
    const INPUTTIPS_COUNT = 20;

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
    ];
}
