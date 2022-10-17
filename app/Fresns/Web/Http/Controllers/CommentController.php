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

class CommentController extends Controller
{
    // index
    public function index(Request $request)
    {
        $query = QueryHelper::convertOptionToRequestParam(QueryHelper::TYPE_COMMENT, $request->all());

        $result = ApiHelper::make()->get('/api/v2/comment/list', [
            'query' => $query,
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message']);
        }

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('comments.index', compact('comments'));
    }

    // list
    public function list(Request $request)
    {
        $query = QueryHelper::convertOptionToRequestParam(QueryHelper::TYPE_COMMENT_LIST, $request->all());

        $result = ApiHelper::make()->get('/api/v2/comment/list', [
            'query' => $query,
        ]);

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('comments.list', compact('comments'));
    }

    // nearby
    public function nearby(Request $request)
    {
        if (empty($request->mapLng) || empty($request->mapLat)) {
            return back()->with([
                'failure' => fs_lang('location').': '.fs_lang('errorEmpty'),
            ]);
        }

        $query = $request->all();
        $query['mapId'] = $request->mapId;
        $query['mapLng'] = $request->mapLng;
        $query['mapLat'] = $request->mapLat;
        $query['unit'] = $request->unit ?? null;
        $query['length'] = $request->length ?? null;

        $result = ApiHelper::make()->get('/api/v2/comment/nearby', [
            'query' => $query,
        ]);

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('comments.nearby', compact('comments'));
    }

    // location
    public function location(Request $request)
    {
        if (empty($request->mapLng) || empty($request->mapLat)) {
            return back()->with([
                'failure' => fs_lang('location').': '.fs_lang('errorEmpty'),
            ]);
        }

        $query = $request->all();
        $query['mapId'] = $request->mapId;
        $query['mapLng'] = $request->mapLng;
        $query['mapLat'] = $request->mapLat;
        $query['unit'] = $request->unit ?? null;
        $query['length'] = $request->length ?? null;

        $result = ApiHelper::make()->get('/api/v2/comment/nearby', [
            'query' => $query,
        ]);

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('comments.location', compact('comments'));
    }

    // likes
    public function likes(Request $request)
    {
        $uid = fs_user('detail.uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/like/comments", [
            'query' => $request->all(),
        ]);

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('comments.likes', compact('comments'));
    }

    // dislikes
    public function dislikes(Request $request)
    {
        $uid = fs_user('detail.uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/dislike/comments", [
            'query' => $request->all(),
        ]);

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('comments.dislikes', compact('comments'));
    }

    // following
    public function following(Request $request)
    {
        $uid = fs_user('detail.uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/follow/comments", [
            'query' => $request->all(),
        ]);

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('comments.following', compact('comments'));
    }

    // blocking
    public function blocking(Request $request)
    {
        $uid = fs_user('detail.uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/block/comments", [
            'query' => $request->all(),
        ]);

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('comments.blocking', compact('comments'));
    }

    // detail
    public function detail(Request $request, string $cid)
    {
        $query = $request->all();
        $query['cid'] = $cid;

        $client = ApiHelper::make();

        $results = $client->handleUnwrap([
            'comment' => $client->getAsync("/api/v2/comment/{$cid}/detail"),
            'comments'   => $client->getAsync('/api/v2/comment/list', [
                'query' => $query,
            ]),
        ]);

        if ($results['comment']['code'] != 0) {
            throw new ErrorException($results['comment']['message'], $results['comment']['code']);
        }

        $items = $results['comment']['data']['items'];
        $comment = $results['comment']['data']['detail'];

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $results['comments']['data']['list'],
            paginate: $results['comments']['data']['paginate'],
        );

        return view('comments.detail', compact('items', 'comment', 'comments'));
    }
}
