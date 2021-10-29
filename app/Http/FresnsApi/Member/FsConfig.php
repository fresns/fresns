<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Member;

class FsConfig
{
    // Modify member profile parameters
    const MEMBER_EDIT = [
        'mname' => 'name',
        'nickname' => 'nickname',
        'avatarUrl' => 'avatar_file_url',
        'gender' => 'gender',
        'birthday' => 'birthday',
        'bio' => 'bio',
        'dialogLimit' => 'dialog_limit',
        'timezone' => 'timezone',
        'language' => 'language',
        'iosDeviceToken' => 'device_token_ios',
        'androidDeviceToken' => 'device_token_android',
    ];
}
