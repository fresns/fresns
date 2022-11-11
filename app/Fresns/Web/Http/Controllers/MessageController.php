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

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'conversations' => $client->getAsync('/api/v2/conversation/list', [
                'query' => [
                    'isPin' => false,
                ],
            ]),
            'pinConversations' => $client->getAsync('/api/v2/conversation/list', [
                'query' => [
                    'isPin' => true,
                ],
            ]),
        ]);

        if (data_get($results, 'conversations.code') !== 0) {
            throw new ErrorException($results['conversations']['message'], $results['conversations']['code']);
        }

        $conversations = QueryHelper::convertApiDataToPaginate(
            items: $results['conversations']['data']['list'],
            paginate: $results['conversations']['data']['paginate'],
        );

        $pinConversations = $results['pinConversations']['data']['list'];

        return view('messages.index', compact('conversations', 'pinConversations'));
    }

    // conversation
    public function conversation(Request $request, int $conversationId)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'conversation' => $client->getAsync("/api/v2/conversation/{$conversationId}/detail"),
            'messages' => $client->getAsync("/api/v2/conversation/{$conversationId}/messages", [
                'query' => $query,
            ]),
            'markAllAsRead' => $client->putAsync('/api/v2/conversation/mark-as-read', [
                'json' => [
                    'type' => 'conversation',
                    'conversationId' => $conversationId,
                ],
            ]),
        ]);

        if ($results['conversation']['code'] != 0) {
            throw new ErrorException($results['conversation']['message'], $results['conversation']['code']);
        }

        $conversation = $results['conversation']['data'];

        $messages = QueryHelper::convertApiDataToPaginate(
            items: $results['messages']['data']['list'],
            paginate: $results['messages']['data']['paginate'],
        );

        return view('messages.conversation', compact('conversation', 'messages'));
    }

    // notification
    public function notifications(Request $request, ?string $types = null)
    {
        $query = $request->all();
        $query['types'] = $types;

        $result = ApiHelper::make()->get('/api/v2/notification/list', [
            'query' => $query,
        ]);

        if (data_get($result, 'code') !== 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        $notifications = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('messages.notifications', compact('notifications', 'types'));
    }
}
