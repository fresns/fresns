<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateStickerGroupRequest;
use App\Helpers\PrimaryHelper;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Language;
use App\Models\Sticker;
use Illuminate\Http\Request;

class StickerGroupController extends Controller
{
    public function index()
    {
        $groups = Sticker::group()
            ->orderBy('rating')
            ->with('names')
            ->with(['stickers' => function ($query) {
                $query->orderBy('rating');
            }])
            ->get();

        return view('FsView::operations.stickers', compact('groups'));
    }

    public function store(Sticker $sticker, UpdateStickerGroupRequest $request)
    {
        $sticker->rating = $request->rating;
        $sticker->code = $request->code;
        $sticker->is_enable = $request->is_enable;
        $sticker->image_file_url = $request->image_file_url ?: '';
        $sticker->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $sticker->type = 2;
        $sticker->save();

        if ($request->file('image_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_STICKER,
                'platformId' => 4,
                'tableName' => 'stickers',
                'tableColumn' => 'image_file_id',
                'tableId' => $sticker->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('image_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $sticker->image_file_id = $fileId;
            $sticker->image_file_url = null;
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
        $sticker->rating = $request->rating;
        $sticker->code = $request->code;
        $sticker->is_enable = $request->is_enable;
        $sticker->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');

        if ($request->file('image_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_STICKER,
                'platformId' => 4,
                'tableName' => 'stickers',
                'tableColumn' => 'image_file_id',
                'tableId' => $sticker->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('image_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $sticker->image_file_id = $fileId;
            $sticker->image_file_url = null;
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

    public function updateRating(int $id, Request $request)
    {
        $stickerGroup = Sticker::findOrFail($id);
        $stickerGroup->rating = $request->rating;
        $stickerGroup->save();

        return $this->updateSuccess();
    }
}
