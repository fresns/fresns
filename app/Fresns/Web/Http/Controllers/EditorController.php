<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use App\Fresns\Web\Helpers\ApiHelper;
use Illuminate\Http\Request;

class EditorController extends Controller
{
    // drafts
    public function drafts(Request $request, string $type)
    {
        return view('editor.drafts');
    }

    // post
    public function post(Request $request, ?int $draftId = null)
    {
        $type = 'post';

        $client = ApiHelper::make();

        $results = $client->handleUnwrap([
            'config' => $client->getAsync('/api/v2/editor/post/config'),
            'stickers' => $client->getAsync('/api/v2/global/stickers'),
        ]);

        $config = $results['config']['data'];
        $stickers = $results['stickers']['data'];

        return view('editor.editor', compact('type', 'config', 'draftId', 'stickers'));
    }

    // comment
    public function comment(Request $request, ?int $draftId = null)
    {
        $type = 'comment';

        $client = ApiHelper::make();

        $results = $client->handleUnwrap([
            'config' => $client->getAsync('/api/v2/editor/comment/config'),
            'stickers' => $client->getAsync('/api/v2/global/stickers'),
        ]);

        $config = $results['config']['data'];
        $stickers = $results['stickers']['data'];

        return view('editor.editor', compact('type', 'config', 'draftId', 'stickers'));
    }
}
