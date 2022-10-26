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
        $info['likeCount'] = $commentData->like_count;
        $info['dislikeCount'] = $commentData->dislike_count;
        $info['followCount'] = $commentData->follow_count;
        $info['blockCount'] = $commentData->block_count;
        $info['commentCount'] = $commentData->comment_count;
        $info['commentDigestCount'] = $commentData->comment_digest_count;
        $info['commentLikeCount'] = $commentData->comment_like_count;
        $info['commentDislikeCount'] = $commentData->comment_dislike_count;
        $info['commentFollowCount'] = $commentData->comment_follow_count;
        $info['commentBlockCount'] = $commentData->comment_block_count;
        $info['createTime'] = DateHelper::fresnsFormatDateTime($commentData->created_at, $timezone, $langTag);
        $info['createTimeFormat'] = DateHelper::fresnsFormatTime($commentData->created_at, $langTag);
        $info['editTime'] = DateHelper::fresnsFormatDateTime($commentData->latest_edit_at, $timezone, $langTag);
        $info['editTimeFormat'] = DateHelper::fresnsFormatTime($commentData->latest_edit_at, $langTag);
        $info['editCount'] = $appendData->edit_count;

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
