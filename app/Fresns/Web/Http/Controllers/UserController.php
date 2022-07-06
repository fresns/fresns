<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use App\Fresns\Web\Helpers\ApiHelper;
use App\Helpers\ConfigHelper;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // index
    public function index()
    {
        $queryConfig = ConfigHelper::fresnsConfigByItemKey('menu_user_config');

        $result = ApiHelper::make()->get('/api/v2/user/list');

        $users = $result['data']['list'];

        return view('users.index', compact('users'));
    }

    // list
    public function list()
    {
        $queryConfig = ConfigHelper::fresnsConfigByItemKey('menu_user_list_config');

        $result = ApiHelper::make()->get('/api/v2/user/list');

        $users = $result['data']['list'];

        return view('users.list', compact('users'));
    }

    // likes
    public function likes()
    {
        $uid = fs_user('uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/like/users");

        $users = $result['data']['list'];

        return view('users.likes', compact('users'));
    }

    // dislikes
    public function dislikes()
    {
        $uid = fs_user('uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/dislike/users");

        $users = $result['data']['list'];

        return view('users.dislikes', compact('users'));
    }

    // following
    public function following()
    {
        $uid = fs_user('uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/follow/users");

        $users = $result['data']['list'];

        return view('users.following', compact('users'));
    }

    // blocking
    public function blocking()
    {
        $uid = fs_user('uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/block/users");

        $users = $result['data']['list'];

        return view('users.blocking', compact('users'));
    }
}
