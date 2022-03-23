<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateStickerRequest;
use App\Models\Sticker;
use Illuminate\Http\Request;

class StickerController extends Controller
{
    public function store(Sticker $stickerImage, UpdateStickerRequest $request)
    {
        $stickerImage->parent_id = $request->parent_id;
        $stickerImage->rank_num = $request->rank_num;
        $stickerImage->code = $request->code;
        $stickerImage->name = $request->code;
        $stickerImage->is_enable = $request->is_enable;
        $stickerImage->image_file_url = $request->image_file_url ?: '';
        $stickerImage->type = 1;
        $stickerImage->save();

        return $this->createSuccess();
    }

    public function update(Sticker $stickerImage, Request $request)
    {
        $stickerImage->is_enable = $request->is_enable;
        $stickerImage->save();

        return $this->updateSuccess();
    }

    public function destroy(Sticker $stickerImage)
    {
        $stickerImage->delete();

        return $this->deleteSuccess();
    }

    public function updateRank(Sticker $stickerImage, Request $request)
    {
        $stickerImage->rank_num = $request->rank_num;
        $stickerImage->save();

        return $this->updateSuccess();
    }

    public function batchUpdate(Request $request)
    {
        $group = Sticker::group()->where('id', $request->parent_id)->firstOrFail();

        $stickerImages = $group->stickers;
        $deleteIds = $stickerImages->pluck('id')->diff(array_keys($request->rank_num));
        if ($deleteIds->count()) {
            $group->stickers()->whereIn('id', $deleteIds)->delete();
        }

        foreach ($request->rank_num ?? [] as $id => $rank) {
            $stickerImage = $stickerImages->where('id', $id)->first();
            if (! $stickerImage) {
                continue;
            }
            $stickerImage->rank_num = $rank;
            $stickerImage->is_enable = $request->enable[$id] ?? 0;
            $stickerImage->save();
        }

        return $this->updateSuccess();
    }
}
