<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use Illuminate\Http\Request;

class HashtagController extends Controller
{
    // index
    public function index()
    {
        return view('hashtags.index');
    }

    // list
    public function list()
    {
        return view('hashtags.list');
    }

    // likes
    public function likes()
    {
        return view('hashtags.likes');
    }

    // dislikes
    public function dislikes()
    {
        return view('hashtags.dislikes');
    }

    // following
    public function following()
    {
        return view('hashtags.following');
    }

    // blocking
    public function blocking()
    {
        return view('hashtags.blocking');
    }

    // detail
    public function detail()
    {
        return view('hashtags.detail');
    }
}
