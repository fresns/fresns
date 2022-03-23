<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\User;

class FsConfig
{
    // Modify user profile parameters
    const USER_EDIT = [
        'username' => 'username',
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
