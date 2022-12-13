<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use Illuminate\Support\Str;

trait CommentServiceTrait
{
    public function getCommentInfo(?string $langTag = null, ?string $timezone = null)
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

        $info['cid'] = $commentData->cid;
        $info['url'] = $configKeys['site_url'].'/'.$configKeys['website_comment_detail_path'].'/'.$commentData->cid;
        $info['content'] = $commentData->content;
        $info['contentLength'] = Str::length($commentData->content);
        $info['langTag'] = $commentData->lang_tag;
        $info['writingDirection'] = $commentData->writing_direction;
        $info['isBrief'] = false;
        $info['isMarkdown'] = (bool) $commentData->is_markdown;
        $info['isAnonymous'] = (bool) $commentData->is_anonymous;
        $info['isSticky'] = (bool) $commentData->is_sticky;
        $info['digestState'] = $commentData->digest_state;
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
        $info['createTime'] = $commentData->created_at;
        $info['createTimeFormat'] = $commentData->created_at;
        $info['editTime'] = $commentData->latest_edit_at;
        $info['editTimeFormat'] = $commentData->latest_edit_at;
        $info['editCount'] = $appendData->edit_count;
        $info['latestCommentTime'] = $commentData->latest_comment_at;
        $info['latestCommentTimeFormat'] = $commentData->latest_comment_at;
        $info['rankState'] = $commentData->rank_state;
        $info['status'] = (bool) $commentData->is_enable;

        $info['ipLocation'] = $appendData->ip_location;

        $location['isLbs'] = ! empty($commentData->map_id) ? true : false;
        $location['mapId'] = $commentData->map_id;
        $location['latitude'] = $commentData->map_latitude;
        $location['longitude'] = $commentData->map_longitude;
        $location['scale'] = $appendData->map_scale;
        $location['poi'] = $appendData->map_poi;
        $location['poiId'] = $appendData->map_poi_id;
        $location['distance'] = null;
        $location['unit'] = ConfigHelper::fresnsConfigLengthUnit($langTag);

        $info['location'] = $location;

        return $info;
    }
}
