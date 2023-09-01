<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use Illuminate\Support\Str;

trait CommentServiceTrait
{
    public function getCommentInfo(?string $langTag = null): array
    {
        $commentData = $this;
        $appendData = $this->commentAppend;

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'website_comment_detail_path',
            'site_url',
            'comment_liker_count',
            'comment_disliker_count',
            'comment_follower_count',
            'comment_blocker_count',
        ]);

        $siteUrl = $configKeys['site_url'] ?? config('app.url');

        $info['cid'] = $commentData->cid;
        $info['url'] = $siteUrl.'/'.$configKeys['website_comment_detail_path'].'/'.$commentData->cid;
        $info['content'] = $commentData->content;
        $info['contentLength'] = Str::length($commentData->content);
        $info['langTag'] = $commentData->lang_tag;
        $info['writingDirection'] = $commentData->writing_direction;
        $info['isBrief'] = false;
        $info['isMarkdown'] = (bool) $commentData->is_markdown;
        $info['isAnonymous'] = (bool) $commentData->is_anonymous;
        $info['isSticky'] = (bool) $commentData->is_sticky;
        $info['digestState'] = $commentData->digest_state;
        $info['viewCount'] = $commentData->view_count;
        $info['likeCount'] = $configKeys['comment_liker_count'] ? $commentData->like_count : null;
        $info['dislikeCount'] = $configKeys['comment_disliker_count'] ? $commentData->dislike_count : null;
        $info['followCount'] = $configKeys['comment_follower_count'] ? $commentData->follow_count : null;
        $info['blockCount'] = $configKeys['comment_blocker_count'] ? $commentData->block_count : null;
        $info['commentCount'] = $commentData->comment_count;
        $info['commentDigestCount'] = $commentData->comment_digest_count;
        $info['commentLikeCount'] = $configKeys['comment_liker_count'] ? $commentData->comment_like_count : null;
        $info['commentDislikeCount'] = $configKeys['comment_disliker_count'] ? $commentData->comment_dislike_count : null;
        $info['commentFollowCount'] = $configKeys['comment_follower_count'] ? $commentData->comment_follow_count : null;
        $info['commentBlockCount'] = $configKeys['comment_blocker_count'] ? $commentData->comment_block_count : null;
        $info['createdDatetime'] = $commentData->created_at;
        $info['createdTimeAgo'] = $commentData->created_at;
        $info['editedDatetime'] = $commentData->latest_edit_at;
        $info['editedTimeAgo'] = $commentData->latest_edit_at;
        $info['editedCount'] = $appendData->edit_count;
        $info['latestCommentDatetime'] = $commentData->latest_comment_at;
        $info['latestCommentTimeAgo'] = $commentData->latest_comment_at;
        $info['rankState'] = $commentData->rank_state;
        $info['status'] = (bool) $commentData->is_enabled;

        $info['moreJson'] = $appendData->more_json;

        $mapJson = $appendData->map_json;
        $poi = $mapJson['poi'] ?? null;
        $mapJson['isLbs'] = (bool) ($commentData->map_latitude && $commentData->map_longitude && $poi);
        $mapJson['distance'] = null;
        $mapJson['unit'] = ConfigHelper::fresnsConfigLengthUnit($langTag);

        $info['location'] = $mapJson;
        $info['location']['encode'] = urlencode(base64_encode(json_encode($mapJson)));

        return $info;
    }
}
