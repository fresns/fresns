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

class HashtagController extends Controller
{
    // index
    public function index(Request $request)
    {
        $query = QueryHelper::convertOptionToRequestParam(QueryHelper::TYPE_HASHTAG, $request->all());

        $result = ApiHelper::make()->get('/api/v2/hashtag/list', [
            'query' => $query,
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        $hashtags = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('hashtags.index', compact('hashtags'));
    }

    // list
    public function list(Request $request)
    {
        $query = QueryHelper::convertOptionToRequestParam(QueryHelper::TYPE_HASHTAG_LIST, $request->all());

        $result = ApiHelper::make()->get('/api/v2/hashtag/list', [
            'query' => $query,
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        $hashtags = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('hashtags.list', compact('hashtags'));
    }

    // likes
    public function likes(Request $request)
    {
        $uid = fs_user('detail.uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/like/hashtags", [
            'query' => $request->all(),
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        $hashtags = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('hashtags.likes', compact('hashtags'));
    }

    // dislikes
    public function dislikes(Request $request)
    {
        $uid = fs_user('detail.uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/dislike/hashtags", [
            'query' => $request->all(),
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        $hashtags = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('hashtags.dislikes', compact('hashtags'));
    }

    // following
    public function following(Request $request)
    {
        $uid = fs_user('detail.uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/follow/hashtags", [
            'query' => $request->all(),
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        $hashtags = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('hashtags.following', compact('hashtags'));
    }

    // blocking
    public function blocking(Request $request)
    {
        $uid = fs_user('detail.uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/block/hashtags", [
            'query' => $request->all(),
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        $hashtags = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('hashtags.blocking', compact('hashtags'));
    }

    // detail
    public function detail(Request $request, string $hid)
    {
        $query = $request->all();
        $query['hid'] = $hid;

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'hashtag' => $client->getAsync("/api/v2/hashtag/{$hid}/detail"),
            'posts'   => $client->getAsync('/api/v2/post/list', [
                'query' => $query,
            ]),
        ]);

        if ($results['hashtag']['code'] != 0) {
            throw new ErrorException($results['hashtag']['message'], $results['hashtag']['code']);
        }

        $items = $results['hashtag']['data']['items'];
        $hashtag = $results['hashtag']['data']['detail'];

        $posts = QueryHelper::convertApiDataToPaginate(
            items: $results['posts']['data']['list'],
            paginate: $results['posts']['data']['paginate'],
        );

        return view('hashtags.detail', compact('items', 'hashtag', 'posts'));
    }
}
