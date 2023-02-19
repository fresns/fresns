<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateBlockWordRequest;
use App\Helpers\CacheHelper;
use App\Models\BlockWord;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;

class BlockWordController extends Controller
{
    public function index(Request $request)
    {
        $words = BlockWord::query();
        if ($keyword = $request->keyword) {
            $words->where('word', 'like', "%$keyword%");
        }

        $words = $words->latest()->paginate();

        $contentModeLabels = [
            1 => __('FsLang::panel.block_word_content_mode_1'),
            2 => __('FsLang::panel.block_word_content_mode_2'),
            3 => __('FsLang::panel.block_word_content_mode_3'),
            4 => __('FsLang::panel.block_word_content_mode_4'),
        ];

        $userModeLabels = [
            1 => __('FsLang::panel.block_word_user_mode_1'),
            2 => __('FsLang::panel.block_word_user_mode_2'),
            3 => __('FsLang::panel.block_word_user_mode_3'),
        ];

        $conversationModeLabels = [
            1 => __('FsLang::panel.block_word_conversation_mode_1'),
            2 => __('FsLang::panel.block_word_conversation_mode_2'),
            3 => __('FsLang::panel.block_word_conversation_mode_3'),
        ];

        return view('FsView::operations.block-words', compact(
            'words', 'contentModeLabels', 'userModeLabels',
            'conversationModeLabels', 'keyword'
        ));
    }

    public function store(BlockWord $blockWord, UpdateBlockWordRequest $request)
    {
        $blockWord->fill($request->all());
        $blockWord->save();

        CacheHelper::forgetFresnsKeys([
            'fresns_content_block_words',
            'fresns_user_block_words',
            'fresns_conversation_block_words',
            'fresns_content_ban_words',
            'fresns_content_review_words',
            'fresns_user_ban_words',
            'fresns_conversation_ban_words',
        ]);

        return $this->createSuccess();
    }

    public function update(BlockWord $blockWord, UpdateBlockWordRequest $request)
    {
        $blockWord->update($request->all());

        CacheHelper::forgetFresnsKeys([
            'fresns_content_block_words',
            'fresns_user_block_words',
            'fresns_conversation_block_words',
            'fresns_content_ban_words',
            'fresns_content_review_words',
            'fresns_user_ban_words',
            'fresns_conversation_ban_words',
        ]);

        return $this->updateSuccess();
    }

    public function destroy(BlockWord $blockWord)
    {
        $blockWord->delete();

        CacheHelper::forgetFresnsKeys([
            'fresns_content_block_words',
            'fresns_user_block_words',
            'fresns_conversation_block_words',
            'fresns_content_ban_words',
            'fresns_content_review_words',
            'fresns_user_ban_words',
            'fresns_conversation_ban_words',
        ]);

        return $this->deleteSuccess();
    }

    public function export()
    {
        // Load block words
        $blockWords = BlockWord::all();

        // Export all block words
        return (new FastExcel($blockWords))->download('Fresns-BlockWords.xlsx', function ($blockWords) {
            return [
                'word' => $blockWords->word,
                'content_mode' => $blockWords->content_mode,
                'user_mode' => $blockWords->user_mode,
                'conversation_mode' => $blockWords->conversation_mode,
                'replace_word' => $blockWords->replace_word,
            ];
        });
    }

    public function import(Request $request)
    {
        (new FastExcel)->import($request->file('file'), function ($blockWord) {
            return BlockWord::updateOrCreate([
                'word' => $blockWord['word'],
            ], [
                'content_mode' => $blockWord['content_mode'],
                'user_mode' => $blockWord['user_mode'],
                'conversation_mode' => $blockWord['conversation_mode'],
                'replace_word' => $blockWord['replace_word'],
            ]);
        });

        return back();
    }
}
