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

class EditorController extends Controller
{
    // drafts
    public function drafts(Request $request, string $type)
    {
        $query = $request->all();

        $result = ApiHelper::make()->get("/api/v2/editor/{$type}/drafts", [
            'query' => $query,
        ]);

        $drafts = QueryHelper::convertApiDataToPaginate(
            items: $result['data']['list'],
            paginate: $result['data']['paginate'],
        );

        return view('editor.drafts', compact('drafts'));
    }

    // post
    public function post(Request $request, ?int $draftId = null)
    {
        $type = 'post';
        $plid = $draftId;
        $clid = null;

        $draftInfo = self::getDraft('post', $draftId);

        $config = $draftInfo['config'];
        $stickers = $draftInfo['stickers'];
        $draft = $draftInfo['draft'];

        return view('editor.editor', compact('type', 'draftId', 'plid', 'clid', 'config', 'stickers'));
    }

    // comment
    public function comment(Request $request, ?int $draftId = null)
    {
        $type = 'comment';
        $plid = null;
        $clid = $draftId;

        $draftInfo = self::getDraft('comment', $draftId);

        $config = $draftInfo['config'];
        $stickers = $draftInfo['stickers'];
        $draft = $draftInfo['draft'];

        return view('editor.editor', compact('type', 'draftId', 'plid', 'clid', 'config', 'stickers'));
    }

    // get draft
    public static function getDraft(string $type, ?int $draftId = null)
    {
        $client = ApiHelper::make();

        if (empty($draftId)) {
            $results = $client->handleUnwrap([
                'config' => $client->getAsync("/api/v2/editor/{$type}/config"),
                'stickers' => $client->getAsync('/api/v2/global/stickers'),
            ]);

            $draftInfo['stickers'] = null;
            $draftInfo['draft'] = null;
        } else {
            $results = $client->handleUnwrap([
                'config' => $client->getAsync("/api/v2/editor/{$type}/config"),
                'stickers' => $client->getAsync('/api/v2/global/stickers'),
                'draft' => $client->getAsync("/api/v2/editor/post/{$draftId}"),
            ]);

            if ($results['draft']['code'] != 0) {
                throw new ErrorException($results['draft']['message'], $results['draft']['code']);
            }

            $draftInfo['stickers'] = $results['stickers']['data'];
            $draftInfo['draft'] = $results['draft']['data'];
        }

        $draftInfo['draft'] = $results['draft']['data'];

        return $draftInfo;
    }
}
