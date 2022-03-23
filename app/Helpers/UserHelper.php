<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\User;

class UserHelper
{
    /**
     * Determine if the user belongs to the account.
     *
     * @param  int  $uid
     * @param  string  $aid
     * @return bool
     */
    public static function fresnsUserAttribution(int $uid, string $aid)
    {
        $accountId = User::where('uid', $uid)->value('account_id');

        return $aid == $accountId ? true : false;
    }

    /**
     * Whether the user is disabled or not.
     *
     * @param  int  $uid
     * @return int
     */
    public static function fresnsUserStatus(int $uid)
    {
        $stat = User::where('uid', $uid)->value('is_enable');

        return $stat;
    }
}
