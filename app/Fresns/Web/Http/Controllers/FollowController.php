<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use Illuminate\Http\Request;

class FollowController extends Controller
{
    // all posts
    public function allPosts()
    {
        return view('follows.all-posts');
    }

    // user posts
    public function userPosts()
    {
        return view('follows.user-posts');
    }

    // group posts
    public function groupPosts()
    {
        return view('follows.group-posts');
    }

    // hashtag posts
    public function hashtagPosts()
    {
        return view('follows.hashtag-posts');
    }

    // all comments
    public function allComments()
    {
        return view('follows.all-comments');
    }

    // user comments
    public function userComments()
    {
        return view('follows.user-comments');
    }

    // group comments
    public function groupComments()
    {
        return view('follows.group-comments');
    }

    // hashtag comments
    public function hashtagComments()
    {
        return view('follows.hashtag-comments');
    }
}
