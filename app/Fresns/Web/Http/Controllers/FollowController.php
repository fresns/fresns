<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use App\Fresns\Web\Helpers\ApiHelper;
use App\Fresns\Web\Helpers\QueryHelper;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    // all posts
    public function allPosts(Request $request)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get('/api/v2/post/follow/all', [
            'query' => $query,
        ]);

        $posts = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('follows.all-posts', compact('posts'));
    }

    // user posts
    public function userPosts(Request $request)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get('/api/v2/post/follow/user', [
            'query' => $query,
        ]);

        $posts = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('follows.user-posts', compact('posts'));
    }

    // group posts
    public function groupPosts(Request $request)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get('/api/v2/post/follow/group', [
            'query' => $query,
        ]);

        $posts = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('follows.group-posts', compact('posts'));
    }

    // hashtag posts
    public function hashtagPosts(Request $request)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get('/api/v2/post/follow/hashtag', [
            'query' => $query,
        ]);

        $posts = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('follows.hashtag-posts', compact('posts'));
    }

    // all comments
    public function allComments(Request $request)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get('/api/v2/comment/follow/all', [
            'query' => $query,
        ]);

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('follows.all-comments', compact('comments'));
    }

    // user comments
    public function userComments(Request $request)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get('/api/v2/comment/follow/user', [
            'query' => $query,
        ]);

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('follows.user-comments', compact('comments'));
    }

    // group comments
    public function groupComments(Request $request)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get('/api/v2/comment/follow/group', [
            'query' => $query,
        ]);

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('follows.group-comments', compact('comments'));
    }

    // hashtag comments
    public function hashtagComments(Request $request)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get('/api/v2/comment/follow/hashtag', [
            'query' => $query,
        ]);

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('follows.hashtag-comments', compact('comments'));
    }
}
