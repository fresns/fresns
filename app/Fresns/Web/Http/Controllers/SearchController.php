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
    public function users(Request $request)
    {
        return view('search.users');
    }

    // groups
    public function groups(Request $request)
    {
        return view('search.groups');
    }

    // hashtags
    public function hashtags(Request $request)
    {
        return view('search.hashtags');
    }

    // posts
    public function posts(Request $request)
    {
        return view('search.posts');
    }

    // comments
    public function comments(Request $request)
    {
        return view('search.comments');
    }
}
