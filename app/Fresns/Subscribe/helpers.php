<?php

use App\Fresns\Subscribe\SubscribeNotify;

if (! function_exists('qualifyTableName')) {
    function qualifyTableName(){
        return (new SubscribeNotify)->qualifyTableName(...func_get_args());
    }
}

if (! function_exists('notifyDataChange')) {
    function notifyDataChange(){
        (new SubscribeNotify)->notifyDataChange(...func_get_args());
    }
}

if (! function_exists('notifyUserActivate')) {
    function notifyUserActivate(){
        (new SubscribeNotify)->notifyUserActivate(...func_get_args());
    }
}