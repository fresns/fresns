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

class MessageController extends Controller
{
    // index
    public function index(Request $request)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get('/api/v2/dialog/list', [
            'query' => $query,
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        $dialogs = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('messages.index', compact('dialogs'));
    }

    // dialog
    public function dialog(Request $request, int $dialogId)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'dialog' => $client->getAsync("/api/v2/dialog/{$dialogId}/detail"),
            'messages'   => $client->getAsync("/api/v2/dialog/{$dialogId}/messages", [
                'query' => $query,
            ]),
        ]);

        if ($results['dialog']['code'] != 0) {
            throw new ErrorException($results['dialog']['message'], $results['dialog']['code']);
        }

        $dialog = $results['dialog']['data'];

        $messages = QueryHelper::convertApiDataToPaginate(
            items: $results['messages']['data']['list'],
            paginate: $results['messages']['data']['paginate'],
        );

        return view('messages.dialog', compact('dialog', 'messages'));
    }

    // notify
    public function notify(Request $request, string $types)
    {
        $query = $request->all();
        $query['types'] = $types;

        $result = ApiHelper::make()->get('/api/v2/notify/list', [
            'query' => $query,
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        $notifies = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('messages.notify', compact('notifies', 'types'));
    }
}
