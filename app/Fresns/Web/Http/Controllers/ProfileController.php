<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    // posts
    public function posts()
    {
        return view('profile.posts');
    }

    // comments
    public function comments()
    {
        return view('profile.comments');
    }

    // likers
    public function likers()
    {
        return view('profile.likers');
    }

    // dislikers
    public function dislikers()
    {
        return view('profile.dislikers');
    }

    // followers
    public function followers()
    {
        return view('profile.followers');
    }

    // blockers
    public function blockers()
    {
        return view('profile.blockers');
    }

    /**
     * like.
     */

    // likeUsers
    public function likeUsers()
    {
        return view('profile.likes.users');
    }

    // likeGroups
    public function likeGroups()
    {
        return view('profile.likes.groups');
    }

    // likeHashtags
    public function likeHashtags()
    {
        return view('profile.likes.hashtags');
    }

    // likePosts
    public function likePosts()
    {
        return view('profile.likes.posts');
    }

    // likeComments
    public function likeComments()
    {
        return view('profile.likes.comments');
    }

    /**
     * dislike.
     */

    // dislikeUsers
    public function dislikeUsers()
    {
        return view('profile.dislikes.users');
    }

    // dislikeGroups
    public function dislikeGroups()
    {
        return view('profile.dislikes.groups');
    }

    // dislikeHashtags
    public function dislikeHashtags()
    {
        return view('profile.dislikes.hashtags');
    }

    // dislikePosts
    public function dislikePosts()
    {
        return view('profile.dislikes.posts');
    }

    // dislikeComments
    public function dislikeComments()
    {
        return view('profile.dislikes.comments');
    }

    /**
     * following.
     */

    // followingUsers
    public function followingUsers()
    {
        return view('profile.following.users');
    }

    // followingGroups
    public function followingGroups()
    {
        return view('profile.following.groups');
    }

    // followingHashtags
    public function followingHashtags()
    {
        return view('profile.following.hashtags');
    }

    // followingPosts
    public function followingPosts()
    {
        return view('profile.following.posts');
    }

    // followingComments
    public function followingComments()
    {
        return view('profile.following.comments');
    }

    /**
     * blocking.
     */

    // blockingUsers
    public function blockingUsers()
    {
        return view('profile.blocking.users');
    }

    // blockingGroups
    public function blockingGroups()
    {
        return view('profile.blocking.groups');
    }

    // blockingHashtags
    public function blockingHashtags()
    {
        return view('profile.blocking.hashtags');
    }

    // blockingPosts
    public function blockingPosts()
    {
        return view('profile.blocking.posts');
    }

    // blockingComments
    public function blockingComments()
    {
        return view('profile.blocking.comments');
    }
}
