<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    // index
    public function index()
    {
        return view('posts.index');
    }

    // list
    public function list()
    {
        return view('posts.list');
    }

    // nearby
    public function nearby()
    {
        return view('posts.nearby');
    }

    // location
    public function location()
    {
        return view('posts.location');
    }

    // likes
    public function likes()
    {
        return view('posts.likes');
    }

    // dislikes
    public function dislikes()
    {
        return view('posts.dislikes');
    }

    // following
    public function following()
    {
        return view('posts.following');
    }

    // blocking
    public function blocking()
    {
        return view('posts.blocking');
    }

    // detail
    public function detail()
    {
        return view('posts.detail');
    }
}
