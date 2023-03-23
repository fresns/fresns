<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Fresns\Subscribe\SubscribeNotify;

if (! function_exists('qualifyTableName')) {
    function qualifyTableName()
    {
        return (new SubscribeNotify)->qualifyTableName(...func_get_args());
    }
}

if (! function_exists('notifyDataChange')) {
    function notifyDataChange()
    {
        (new SubscribeNotify)->notifyDataChange(...func_get_args());
    }
}

if (! function_exists('notifyUserActivate')) {
    function notifyUserActivate()
    {
        (new SubscribeNotify)->notifyUserActivate(...func_get_args());
    }
}
