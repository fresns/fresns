<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use App\Fresns\Web\Exceptions\ErrorException;
use App\Fresns\Web\Helpers\ApiHelper;
use App\Fresns\Web\Helpers\QueryHelper;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // index
    public function index(Request $request)
    {
        $query = $request->all();
        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'users' => $client->getAsync('/api/v2/search/users', [
                'query' => $query,
            ]),
            'groups' => $client->getAsync('/api/v2/search/groups', [
                'query' => $query,
            ]),
            'hashtags' => $client->getAsync('/api/v2/search/hashtags', [
                'query' => $query,
            ]),
            'posts' => $client->getAsync('/api/v2/search/posts', [
                'query' => $query,
            ]),
            'comments' => $client->getAsync('/api/v2/search/comments', [
                'query' => $query,
            ]),
        ]);

        $data['users'] = $results['users']['data']['list'];
        $data['groups'] = $results['groups']['data']['list'];
        $data['hashtags'] = $results['hashtags']['data']['list'];
        $data['posts'] = $results['posts']['data']['list'];
        $data['comments'] = $results['comments']['data']['list'];

        return view('search.index', compact('data'));
    }

    // users
    public function users(Request $request)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get('/api/v2/search/users', [
            'query' => $query,
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        $users = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('search.users', compact('users'));
    }

    // groups
    public function groups(Request $request)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get('/api/v2/search/groups', [
            'query' => $query,
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        $groups = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('search.groups', compact('groups'));
    }

    // hashtags
    public function hashtags(Request $request)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get('/api/v2/search/hashtags', [
            'query' => $query,
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        $hashtags = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('search.hashtags', compact('hashtags'));
    }

    // posts
    public function posts(Request $request)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get('/api/v2/search/posts', [
            'query' => $query,
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        $posts = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('search.posts', compact('posts'));
    }

    // comments
    public function comments(Request $request)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get('/api/v2/search/comments', [
            'query' => $query,
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('search.comments', compact('comments'));
    }
}
