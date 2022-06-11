<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Models\Comment;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // users
    public function users(Request $request)
    {
        return $this->success();
    }

    // groups
    public function groups(Request $request)
    {
        return $this->success();
    }

    // hashtags
    public function hashtags(Request $request)
    {
        return $this->success();
    }

    // posts
    public function posts(Request $request)
    {
        return $this->success();
    }

    // comments
    public function comments(Request $request)
    {
        return $this->success();
    }
}
