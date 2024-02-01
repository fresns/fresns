<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateStickerGroupRequest;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Sticker;
use Illuminate\Http\Request;

class StickerGroupController extends Controller
{
    public function index()
    {
        $groups = Sticker::group()->orderBy('sort_order')
            ->with(['stickers' => function ($query) {
                $query->orderBy('sort_order');
            }])
            ->get();

        $groups = $this->makeStickerUrl($groups);

        foreach ($groups as $k => $v) {
            $groups[$k]['stickers'] = $this->makeStickerUrl($v->stickers);
        }

        return view('FsView::operations.stickers', compact('groups'));
    }

    public function makeStickerUrl($data)
    {
        foreach ($data as $k => $v) {
            $data[$k]['stickerUrl'] = FileHelper::fresnsFileUrlByTableColumn($v->image_file_id, $v->image_file_url);
        }

        return $data;
    }

    public function store(Sticker $sticker, UpdateStickerGroupRequest $request)
    {
        $sticker->type = Sticker::TYPE_GROUP;
        $sticker->code = $request->code;
        $sticker->name = $request->names;
        $sticker->sort_order = $request->sort_order;
        $sticker->is_enabled = $request->is_enabled;
        $sticker->image_file_url = $request->image_file_url ?: '';
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
            $fileId = PrimaryHelper::fresnsPrimaryId('file', $fresnsResp->getData('fid'));

            $sticker->image_file_id = $fileId;
            $sticker->image_file_url = null;
            $sticker->save();
        }

        return $this->createSuccess();
    }

    public function update(Sticker $sticker, UpdateStickerGroupRequest $request)
    {
        $sticker->code = $request->code;
        $sticker->name = $request->names;
        $sticker->sort_order = $request->sort_order;
        $sticker->is_enabled = $request->is_enabled;

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
            $fileId = PrimaryHelper::fresnsPrimaryId('file', $fresnsResp->getData('fid'));

            $sticker->image_file_id = $fileId;
            $sticker->image_file_url = null;
        } elseif ($sticker->image_file_url != $request->image_file_url) {
            $sticker->image_file_id = null;
            $sticker->image_file_url = $request->image_file_url;
        }

        $sticker->save();

        return $this->updateSuccess();
    }

    public function destroy(Sticker $sticker)
    {
        Sticker::where('parent_id', $sticker->id)->update(['is_enabled' => false]);

        $sticker->delete();

        return $this->deleteSuccess();
    }

    public function updateSortOrder(int $id, Request $request)
    {
        $stickerGroup = Sticker::findOrFail($id);
        $stickerGroup->sort_order = $request->order;
        $stickerGroup->save();

        return $this->updateSuccess();
    }
}
