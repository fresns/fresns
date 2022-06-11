<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;

trait HashtagServiceTrait
{
    public function getHashtagInfo(?string $langTag = null)
    {
        $hashtagData = $this;

        $info['hid'] = $hashtagData->slug;
        $info['hname'] = $hashtagData->name;
        $info['cover'] = FileHelper::fresnsFileUrlByTableColumn($hashtagData->cover_file_id, $hashtagData->cover_file_url);
        $info['description'] = LanguageHelper::fresnsLanguageByTableId('hashtags', 'description', $hashtagData->id, $langTag);
        $info['likeCount'] = $hashtagData->like_count;
        $info['dislikeCount'] = $hashtagData->dislike_count;
        $info['followCount'] = $hashtagData->follow_count;
        $info['blockCount'] = $hashtagData->block_count;
        $info['postCount'] = $hashtagData->post_count;
        $info['postDigestCount'] = $hashtagData->post_digest_count;

        return $info;
    }
}
