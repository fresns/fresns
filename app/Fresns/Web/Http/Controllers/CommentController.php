<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use Illuminate\Http\Request;

class CommentController extends Controller
{
    // index
    public function index()
    {
        return view('comments.index');
    }

    // list
    public function list()
    {
        return view('comments.list');
    }

    // nearby
    public function nearby()
    {
        return view('comments.nearby');
    }

    // location
    public function location()
    {
        return view('comments.location');
    }

    // likes
    public function likes()
    {
        return view('comments.likes');
    }

    // dislikes
    public function dislikes()
    {
        return view('comments.dislikes');
    }

    // following
    public function following()
    {
        return view('comments.following');
    }

    // blocking
    public function blocking()
    {
        return view('comments.blocking');
    }

    // detail
    public function detail()
    {
        return view('comments.detail');
    }
}
