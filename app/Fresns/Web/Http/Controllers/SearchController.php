<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    // index
    public function index()
    {
        return view('search.index');
    }

    // users
    public function users()
    {
        return view('search.users');
    }

    // groups
    public function groups()
    {
        return view('search.groups');
    }

    // hashtags
    public function hashtags()
    {
        return view('search.hashtags');
    }

    // posts
    public function posts()
    {
        return view('search.posts');
    }

    // comments
    public function comments()
    {
        return view('search.comments');
    }
}
