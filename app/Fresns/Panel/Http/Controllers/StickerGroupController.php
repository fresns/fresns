<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateStickerGroupRequest;
use App\Helpers\PrimaryHelper;
use App\Models\Language;
use App\Models\Sticker;

class StickerGroupController extends Controller
{
    public function index()
    {
        $groups = Sticker::group()
            ->orderBy('rank_num')
            ->with('names')
            ->with(['stickers' => function ($query) {
                $query->orderBy('rank_num');
            }])
            ->get();

        return view('FsView::operations.stickers', compact('groups'));
    }

    public function store(Sticker $sticker, UpdateStickerGroupRequest $request)
    {
        $sticker->rank_num = $request->rank_num;
        $sticker->code = $request->code;
        $sticker->is_enable = $request->is_enable;
        $sticker->image_file_url = $request->image_file_url ?: '';
        $sticker->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $sticker->type = 2;
        $sticker->save();

        if ($request->file('image_file')) {
            $wordBody = [
                'platform' => 4,
                'type' => 1,
                'tableType' => 3,
                'tableName' => 'stickers',
                'tableColumn' => 'image_file_id',
                'tableId' => $sticker->id,
                'file' => $request->file('image_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->errorResponse();
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $sticker->image_file_id = $fileId;
            $sticker->image_file_url = $fresnsResp->getData('imageConfigUrl');
            $sticker->save();
        }

        foreach ($request->names as $langTag => $content) {
            $language = Language::tableName('stickers')
                ->where('table_id', $sticker->id)
                ->where('lang_tag', $langTag)
                ->first();

            if (! $language) {
                // create but no content
                if (! $content) {
                    continue;
                }
                $language = new Language();
                $language->fill([
                    'table_name' => 'stickers',
                    'table_column' => 'name',
                    'table_id' => $sticker->id,
                    'lang_tag' => $langTag,
                ]);
            }

            $language->lang_content = $content;
            $language->save();
        }

        return $this->createSuccess();
    }

    public function update(Sticker $sticker, UpdateStickerGroupRequest $request)
    {
        $sticker->rank_num = $request->rank_num;
        $sticker->code = $request->code;
        $sticker->is_enable = $request->is_enable;
        $sticker->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');

        if ($request->file('image_file')) {
            $wordBody = [
                'platform' => 4,
                'type' => 1,
                'tableType' => 3,
                'tableName' => 'stickers',
                'tableColumn' => 'image_file_id',
                'tableId' => $sticker->id,
                'file' => $request->file('image_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->errorResponse();
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $sticker->image_file_id = $fileId;
            $sticker->image_file_url = $fresnsResp->getData('imageConfigUrl');
        } elseif ($sticker->image_file_url != $request->image_file_url) {
            $sticker->image_file_id = null;
            $sticker->image_file_url = $request->image_file_url;
        }

        $sticker->save();

        foreach ($request->names as $langTag => $content) {
            $language = Language::tableName('stickers')
                ->where('table_id', $sticker->id)
                ->where('lang_tag', $langTag)
                ->first();

            if (! $language) {
                // create but no content
                if (! $content) {
                    continue;
                }
                $language = new Language();
                $language->fill([
                    'table_name' => 'stickers',
                    'table_column' => 'name',
                    'table_id' => $sticker->id,
                    'lang_tag' => $langTag,
                ]);
            }

            $language->lang_content = $content;
            $language->save();
        }

        return $this->updateSuccess();
    }

    public function destroy(Sticker $sticker)
    {
        $sticker->delete();

        return $this->deleteSuccess();
    }
}
