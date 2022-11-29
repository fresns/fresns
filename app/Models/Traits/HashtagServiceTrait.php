<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;

trait HashtagServiceTrait
{
    public function getHashtagInfo(?string $langTag = null)
    {
        $hashtagData = $this;

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'website_hashtag_detail_path',
            'site_url',
            'hashtag_liker_count',
            'hashtag_disliker_count',
            'hashtag_follower_count',
            'hashtag_blocker_count',
        ]);

        $info['hid'] = $hashtagData->slug;
        $info['url'] = $configKeys['site_url'].'/'.$configKeys['website_hashtag_detail_path'].'/'.$hashtagData->slug;
        $info['hname'] = $hashtagData->name;
        $info['cover'] = FileHelper::fresnsFileUrlByTableColumn($hashtagData->cover_file_id, $hashtagData->cover_file_url);
        $info['description'] = LanguageHelper::fresnsLanguageByTableId('hashtags', 'description', $hashtagData->id, $langTag);
        $info['likeCount'] = $configKeys['hashtag_liker_count'] ? $hashtagData->like_count : null;
        $info['dislikeCount'] = $configKeys['hashtag_disliker_count'] ? $hashtagData->dislike_count : null;
        $info['followCount'] = $configKeys['hashtag_follower_count'] ? $hashtagData->follow_count : null;
        $info['blockCount'] = $configKeys['hashtag_blocker_count'] ? $hashtagData->block_count : null;
        $info['postCount'] = $hashtagData->post_count;
        $info['postDigestCount'] = $hashtagData->post_digest_count;
        $info['commentCount'] = $hashtagData->comment_count;
        $info['commentDigestCount'] = $hashtagData->comment_digest_count;
        $info['createDate'] = $hashtagData->created_at;

        return $info;
    }
}
