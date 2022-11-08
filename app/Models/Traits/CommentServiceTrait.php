<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;
use Illuminate\Support\Str;

trait CommentServiceTrait
{
    public function getCommentInfo(?string $langTag = null, ?string $timezone = null)
    {
        $commentData = $this;
        $appendData = $this->commentAppend;
        $postAppendData = $this->post->postAppend;

        $configKey = ConfigHelper::fresnsConfigByItemKeys([
            'website_comment_detail_path',
            'site_url',
            'comment_liker_count',
            'comment_disliker_count',
            'comment_follower_count',
            'comment_blocker_count',
        ]);

        $info['cid'] = $commentData->cid;
        $info['url'] = $configKey['site_url'].'/'.$configKey['website_comment_detail_path'].'/'.$commentData->cid;
        $info['content'] = $commentData->content;
        $info['contentLength'] = Str::length($commentData->content);
        $info['langTag'] = $commentData->lang_tag;
        $info['writingDirection'] = $commentData->writing_direction;
        $info['isBrief'] = false;
        $info['isMarkdown'] = (bool) $commentData->is_markdown;
        $info['isAnonymous'] = (bool) $commentData->is_anonymous;
        $info['isSticky'] = (bool) $commentData->is_sticky;
        $info['digestState'] = $commentData->digest_state;
        $info['likeCount'] = $configKey['comment_liker_count'] ? $commentData->like_count : null;
        $info['dislikeCount'] = $configKey['comment_disliker_count'] ? $commentData->dislike_count : null;
        $info['followCount'] = $configKey['comment_follower_count'] ? $commentData->follow_count : null;
        $info['blockCount'] = $configKey['comment_blocker_count'] ? $commentData->block_count : null;
        $info['commentCount'] = $commentData->comment_count;
        $info['commentDigestCount'] = $commentData->comment_digest_count;
        $info['commentLikeCount'] = $configKey['comment_liker_count'] ? $commentData->comment_like_count : null;
        $info['commentDislikeCount'] = $configKey['comment_disliker_count'] ? $commentData->comment_dislike_count : null;
        $info['commentFollowCount'] = $configKey['comment_follower_count'] ? $commentData->comment_follow_count : null;
        $info['commentBlockCount'] = $configKey['comment_blocker_count'] ? $commentData->comment_block_count : null;
        $info['createTime'] = DateHelper::fresnsFormatDateTime($commentData->created_at, $timezone, $langTag);
        $info['createTimeFormat'] = DateHelper::fresnsFormatTime($commentData->created_at, $langTag);
        $info['editTime'] = DateHelper::fresnsFormatDateTime($commentData->latest_edit_at, $timezone, $langTag);
        $info['editTimeFormat'] = DateHelper::fresnsFormatTime($commentData->latest_edit_at, $langTag);
        $info['editCount'] = $appendData->edit_count;
        $info['rankState'] = $commentData->rank_state;
        $info['status'] = (bool) $commentData->is_enable;

        $info['isCommentBtn'] = (bool) $postAppendData->is_comment_btn;
        $info['commentBtnName'] = LanguageHelper::fresnsLanguageByTableId('post_appends', 'comment_btn_name', $commentData->post_id, $langTag);
        $info['commentBtnStyle'] = $postAppendData->comment_btn_style;
        $info['commentBtnUrl'] = ! empty($postAppendData->comment_btn_plugin_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($postAppendData->comment_btn_plugin_unikey) : null;

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
