<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;
use App\Models\PostAllow;
use Illuminate\Support\Str;

trait PostServiceTrait
{
    public function getPostInfo(?string $langTag = null)
    {
        $postData = $this;
        $appendData = $this->postAppend;

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'website_post_detail_path',
            'site_url',
            'post_liker_count',
            'post_disliker_count',
            'post_follower_count',
            'post_blocker_count',
            'comment_liker_count',
            'comment_disliker_count',
            'comment_follower_count',
            'comment_blocker_count',
        ]);

        $info['pid'] = $postData->pid;
        $info['url'] = $configKeys['site_url'].'/'.$configKeys['website_post_detail_path'].'/'.$postData->pid;
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
        $info['likeCount'] = $configKeys['post_liker_count'] ? $postData->like_count : null;
        $info['dislikeCount'] = $configKeys['post_disliker_count'] ? $postData->dislike_count : null;
        $info['followCount'] = $configKeys['post_follower_count'] ? $postData->follow_count : null;
        $info['blockCount'] = $configKeys['post_blocker_count'] ? $postData->block_count : null;
        $info['commentCount'] = $postData->comment_count;
        $info['commentDigestCount'] = $postData->comment_digest_count;
        $info['commentLikeCount'] = $configKeys['comment_liker_count'] ? $postData->comment_like_count : null;
        $info['commentDislikeCount'] = $configKeys['comment_disliker_count'] ? $postData->comment_dislike_count : null;
        $info['commentFollowCount'] = $configKeys['comment_follower_count'] ? $postData->comment_follow_count : null;
        $info['commentBlockCount'] = $configKeys['comment_blocker_count'] ? $postData->comment_block_count : null;
        $info['createTime'] = $postData->created_at;
        $info['createTimeFormat'] = $postData->created_at;
        $info['editTime'] = $postData->latest_edit_at;
        $info['editTimeFormat'] = $postData->latest_edit_at;
        $info['editCount'] = $appendData->edit_count;
        $info['latestCommentTime'] = $postData->latest_comment_at;
        $info['latestCommentTimeFormat'] = $postData->latest_comment_at;
        $info['rankState'] = $postData->rank_state;
        $info['status'] = (bool) $postData->is_enable;

        $info['isAllow'] = (bool) $appendData->is_allow;
        $info['allowProportion'] = $appendData->allow_proportion;
        $info['allowBtnName'] = LanguageHelper::fresnsLanguageByTableId('post_appends', 'allow_btn_name', $appendData->post_id, $langTag) ?? $appendData->allow_btn_name;
        $info['allowBtnUrl'] = PluginHelper::fresnsPluginUrlByUnikey($appendData->allow_plugin_unikey);

        $info['isUserList'] = (bool) $appendData->is_user_list;
        $info['userListName'] = LanguageHelper::fresnsLanguageByTableId('post_appends', 'user_list_name', $appendData->post_id, $langTag) ?? $appendData->user_list_name;
        $info['userListUrl'] = PluginHelper::fresnsPluginUrlByUnikey($appendData->user_list_plugin_unikey);
        $info['userListCount'] = 0;
        if ($info['isUserList']) {
            $info['userListCount'] = PostAllow::where('post_id', $postData->id)->count();
        }

        $info['ipLocation'] = $appendData->ip_location;

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

        $info['isComment'] = (bool) $appendData->is_comment;
        $info['isCommentPublic'] = (bool) $appendData->is_comment_public;

        return $info;
    }
}
