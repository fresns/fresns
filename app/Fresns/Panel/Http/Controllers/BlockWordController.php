<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Exports\BlockWordsExport;
use App\Fresns\Panel\Http\Requests\UpdateBlockWordRequest;
use App\Fresns\Panel\Imports\BlockWordsImport;
use App\Models\BlockWord;
use Illuminate\Http\Request;

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

        $dialogModeLabels = [
            1 => __('FsLang::panel.block_word_dialog_mode_1'),
            2 => __('FsLang::panel.block_word_dialog_mode_2'),
            3 => __('FsLang::panel.block_word_dialog_mode_3'),
        ];

        return view('FsView::operations.block-words', compact(
            'words', 'contentModeLabels', 'userModeLabels',
            'dialogModeLabels', 'keyword'
        ));
    }

    public function store(BlockWord $blockWord, UpdateBlockWordRequest $request)
    {
        $blockWord->fill($request->all());
        $blockWord->save();

        return $this->createSuccess();
    }

    public function update(BlockWord $blockWord, UpdateBlockWordRequest $request)
    {
        $blockWord->update($request->all());

        return $this->updateSuccess();
    }

    public function destroy(BlockWord $blockWord)
    {
        $blockWord->delete();

        return $this->deleteSuccess();
    }

    public function export()
    {
        return \Excel::download(new BlockWordsExport, 'block_words.xlsx');
    }

    public function import(Request $request)
    {
        \Excel::import(new BlockWordsImport, $request->file('file'));

        return back();
    }
}
