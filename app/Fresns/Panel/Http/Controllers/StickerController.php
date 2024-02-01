<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateStickerRequest;
use App\Helpers\PrimaryHelper;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Sticker;
use Illuminate\Http\Request;

class StickerController extends Controller
{
    public function store(Sticker $stickerImage, UpdateStickerRequest $request)
    {
        $stickerImage->parent_id = $request->parent_id;
        $stickerImage->sort_order = $request->sort_order;
        $stickerImage->code = $request->code;
        $stickerImage->is_enabled = $request->is_enabled;
        $stickerImage->image_file_url = $request->image_file_url ?: '';
        $stickerImage->type = Sticker::TYPE_STICKER;
        $stickerImage->save();

        if ($request->file('image_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_STICKER,
                'platformId' => 4,
                'tableName' => 'stickers',
                'tableColumn' => 'image_file_id',
                'tableId' => $stickerImage->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('image_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsPrimaryId('file', $fresnsResp->getData('fid'));

            $stickerImage->image_file_id = $fileId;
            $stickerImage->image_file_url = null;
            $stickerImage->save();
        }

        return $this->createSuccess();
    }

    public function update(Sticker $stickerImage, Request $request)
    {
        $stickerImage->is_enabled = $request->is_enabled;
        $stickerImage->save();

        return $this->updateSuccess();
    }

    public function destroy(Sticker $stickerImage)
    {
        $stickerImage->delete();

        return $this->deleteSuccess();
    }

    public function batchUpdate(Request $request)
    {
        $group = Sticker::group()->where('id', $request->parent_id)->firstOrFail();

        $stickerImages = $group->stickers;

        if ($request->sort_order ?? []) {
            $deleteIds = $stickerImages->pluck('id')->diff(array_keys($request->sort_order));

            if ($deleteIds->count()) {
                $group->stickers()->whereIn('id', $deleteIds)->delete();
            }
        } else {
            foreach ($stickerImages as $stickerImage) {
                $stickerImage->delete();
            }
        }

        foreach ($request->sort_order ?? [] as $id => $sort_order) {
            $stickerImage = $stickerImages->where('id', $id)->first();
            if (! $stickerImage) {
                continue;
            }
            $stickerImage->sort_order = $sort_order;
            $stickerImage->is_enabled = $request->enable[$id] ?? 0;
            $stickerImage->save();
        }

        return $this->updateSuccess();
    }
}
