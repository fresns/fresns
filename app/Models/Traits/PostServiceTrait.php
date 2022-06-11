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

trait PostServiceTrait
{
    public function getPostInfo(?string $langTag = null, ?string $timezone = null)
    {
        $postData = $this;
        $appendData = $this->postAppend;

        $info['pid'] = $postData->pid;
        $info['types'] = explode(',', $postData->types);
        $info['title'] = $postData->title;
        $info['content'] = $postData->content;
        $info['contentLength'] = Str::length($postData->content);
        $info['langTag'] = $postData->lang_tag;
        $info['writingDirection'] = $postData->writing_direction;
        $info['isBrief'] = false;
        $info['isMarkdown'] = (bool) $postData->is_markdown;
        $info['isAnonymous'] = (bool) $postData->is_anonymous;
        $info['stickyState'] = $postData->sticky_state;
        $info['digestState'] = $postData->digest_state;
        $info['likeCount'] = $postData->like_count;
        $info['dislikeCount'] = $postData->dislike_count;
        $info['followCount'] = $postData->follow_count;
        $info['blockCount'] = $postData->block_count;
        $info['commentCount'] = $postData->comment_count;
        $info['commentDigestCount'] = $postData->comment_digest_count;
        $info['commentLikeCount'] = $postData->comment_like_count;
        $info['commentDislikeCount'] = $postData->comment_dislike_count;
        $info['commentFollowCount'] = $postData->comment_follow_count;
        $info['commentBlockCount'] = $postData->comment_block_count;
        $info['createTime'] = DateHelper::fresnsFormatDateTime($postData->created_at, $timezone, $langTag);
        $info['createTimeFormat'] = DateHelper::fresnsFormatTime($postData->created_at, $langTag);
        $info['editTime'] = DateHelper::fresnsFormatDateTime($postData->latest_edit_at, $timezone, $langTag);
        $info['editTimeFormat'] = DateHelper::fresnsFormatTime($postData->latest_edit_at, $langTag);
        $info['editCount'] = $appendData->edit_count;

        $info['isAllow'] = (bool) $appendData->is_allow;
        $info['allowProportion'] = $appendData->allow_proportion;
        $info['allowBtnName'] = LanguageHelper::fresnsLanguageByTableId('post_appends', 'allow_btn_name', $appendData->post_id, $langTag);
        $info['allowBtnUrl'] = ! empty($appendData->allow_plugin_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($appendData->allow_plugin_unikey) : null;

        $info['isUserList'] = (bool) $appendData->is_user_list;
        $info['userListName'] = LanguageHelper::fresnsLanguageByTableId('post_appends', 'user_list_name', $appendData->post_id, $langTag);
        $info['userListUrl'] = ! empty($appendData->user_list_plugin_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($appendData->user_list_plugin_unikey) : null;

        $info['ipRegion'] = $appendData->ip_region;

        $location['isLbs'] = ! empty($postData->map_id) ? true : false;
        $location['mapId'] = $postData->map_id;
        $location['latitude'] = $postData->map_latitude;
        $location['longitude'] = $postData->map_longitude;
        $location['scale'] = $appendData->map_scale;
        $location['poi'] = $appendData->map_poi;
        $location['poiId'] = $appendData->map_poi_id;
        $location['distance'] = null;
        $location['unit'] = ConfigHelper::fresnsConfigLengthUnit($langTag);

        $info['location'] = $location;

        return $info;
    }
}
