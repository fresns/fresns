<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;
use App\Models\PostUser;
use Illuminate\Support\Str;

trait PostServiceTrait
{
    public function getPostInfo(?string $langTag = null): array
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

        $siteUrl = $configKeys['site_url'] ?? config('app.url');

        $info['pid'] = $postData->pid;
        $info['url'] = $siteUrl.'/'.$configKeys['website_post_detail_path'].'/'.$postData->pid;
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
        $info['viewCount'] = $postData->view_count;
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
        $info['postCount'] = $postData->post_count;
        $info['createdDatetime'] = $postData->created_at;
        $info['createdTimeAgo'] = $postData->created_at;
        $info['editedDatetime'] = $postData->latest_edit_at;
        $info['editedTimeAgo'] = $postData->latest_edit_at;
        $info['editedCount'] = $appendData->edit_count;
        $info['latestCommentDatetime'] = $postData->latest_comment_at;
        $info['latestCommentTimeAgo'] = $postData->latest_comment_at;
        $info['rankState'] = $postData->rank_state;
        $info['status'] = (bool) $postData->is_enabled;

        $info['readConfig'] = [
            'isReadLocked' => (bool) $appendData->is_read_locked,
            'previewPercentage' => $appendData->read_pre_percentage,
            'buttonName' => LanguageHelper::fresnsLanguageByTableId('post_appends', 'read_btn_name', $appendData->post_id, $langTag) ?? $appendData->read_btn_name,
            'buttonUrl' => PluginHelper::fresnsPluginUrlByFskey($appendData->read_plugin_fskey),
        ];

        $info['affiliatedUserConfig'] = [
            'hasUserList' => (bool) $appendData->is_user_list,
            'userListName' => LanguageHelper::fresnsLanguageByTableId('post_appends', 'user_list_name', $appendData->post_id, $langTag) ?? $appendData->user_list_name,
            'userListUrl' => PluginHelper::fresnsPluginUrlByFskey($appendData->user_list_plugin_fskey),
            'userListCount' => $appendData->is_user_list ? PostUser::where('post_id', $postData->id)->count() : 0,
        ];

        $info['moreJson'] = $appendData->more_json;

        $mapJson = $appendData->map_json;
        $poi = $mapJson['poi'] ?? null;
        $mapJson['isLbs'] = (bool) ($postData->map_latitude && $postData->map_longitude && $poi);
        $mapJson['distance'] = null;
        $mapJson['unit'] = ConfigHelper::fresnsConfigLengthUnit($langTag);

        $info['location'] = $mapJson;
        $info['location']['encode'] = urlencode(base64_encode(json_encode($mapJson)));

        $info['isCommentHidden'] = false;
        $info['isCommentDisabled'] = (bool) $appendData->is_comment_disabled;
        $info['isCommentPrivate'] = (bool) $appendData->is_comment_private;

        return $info;
    }
}
