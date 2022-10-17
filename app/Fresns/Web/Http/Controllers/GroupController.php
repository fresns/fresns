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
use App\Helpers\ConfigHelper;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    // index
    public function index(Request $request)
    {
        $indexType = ConfigHelper::fresnsConfigByItemKey('menu_group_type');

        $groupTree = [];
        $groups = [];

        if ($indexType == 'tree') {
            $result = ApiHelper::make()->get('/api/v2/group/tree');

            if (data_get($result, 'code') !== 0) {
                throw new ErrorException($result['message']);
            }

            $groupTree = $result['data'];
        } else {
            $query = QueryHelper::convertOptionToRequestParam(QueryHelper::TYPE_GROUP, $request->all());

            $result = ApiHelper::make()->get('/api/v2/group/list', [
                'query' => $query,
            ]);

            if (data_get($result, 'code') !== 0) {
                throw new ErrorException($result['message']);
            }

            $groups = QueryHelper::convertApiDataToPaginate(
                items: $result['data']['list'],
                paginate: $result['data']['paginate'],
            );
        }

        return view('groups.index', compact('groupTree', 'groups'));
    }

    // list
    public function list(Request $request)
    {
        $query = QueryHelper::convertOptionToRequestParam(QueryHelper::TYPE_GROUP_LIST, $request->all());

        $result = ApiHelper::make()->get('/api/v2/group/list', [
            'query' => $query,
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message']);
        }

        $groups = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('groups.list', compact('groups'));
    }

    // likes
    public function likes(Request $request)
    {
        $uid = fs_user('detail.uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/like/groups", [
            'query' => $request->all(),
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message']);
        }

        $groups = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('groups.likes', compact('groups'));
    }

    // dislikes
    public function dislikes(Request $request)
    {
        $uid = fs_user('detail.uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/dislike/groups", [
            'query' => $request->all(),
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message']);
        }

        $groups = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('groups.dislikes', compact('groups'));
    }

    // following
    public function following(Request $request)
    {
        $uid = fs_user('detail.uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/follow/groups", [
            'query' => $request->all(),
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message']);
        }

        $groups = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('groups.following', compact('groups'));
    }

    // blocking
    public function blocking(Request $request)
    {
        $uid = fs_user('detail.uid');

        $result = ApiHelper::make()->get("/api/v2/user/{$uid}/mark/block/groups", [
            'query' => $request->all(),
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message']);
        }

        $groups = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('groups.blocking', compact('groups'));
    }

    // detail
    public function detail(Request $request, string $gid)
    {
        $query = $request->all();
        $query['gid'] = $gid;

        $client = ApiHelper::make();

        $results = $client->handleUnwrap([
            'group' => $client->getAsync("/api/v2/group/{$gid}/detail"),
            'posts'   => $client->getAsync('/api/v2/post/list', [
                'query' => $query,
            ]),
        ]);

        if ($results['group']['code'] != 0) {
            throw new ErrorException($results['group']['message'], $results['group']['code']);
        }

        $items = $results['group']['data']['items'];
        $group = $results['group']['data']['detail'];

        $posts = QueryHelper::convertApiDataToPaginate(
            items: $results['posts']['data']['list'],
            paginate: $results['posts']['data']['paginate'],
        );

        return view('groups.detail', compact('items', 'group', 'posts'));
    }
}
