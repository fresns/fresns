<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\PluginHelper;
use App\Helpers\StrHelper;
use App\Models\PostUser;
use App\Models\User;
use Illuminate\Support\Str;

trait PostServiceTrait
{
    public function getPostInfo(?string $langTag = null): array
    {
        $postData = $this;
        $permissions = $postData->permissions;

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'website_post_detail_path',
            'post_like_public_count',
            'post_dislike_public_count',
            'post_follow_public_count',
            'post_block_public_count',
            'comment_like_public_count',
            'comment_dislike_public_count',
            'comment_follow_public_count',
            'comment_block_public_count',
        ]);

        $siteUrl = ConfigHelper::fresnsSiteUrl();

        $info['pid'] = $postData->pid;
        $info['url'] = $siteUrl.'/'.$configKeys['website_post_detail_path'].'/'.$postData->pid; // https://example.com/post/{pid}
        $info['title'] = $postData->title;
        $info['content'] = $postData->content;
        $info['contentLength'] = Str::length($postData->content);
        $info['langTag'] = $postData->lang_tag;
        $info['writingDirection'] = $permissions['contentWritingDirection'] ?? 'ltr'; // ltr or rtl
        $info['isBrief'] = false;
        $info['isMarkdown'] = (bool) $postData->is_markdown;
        $info['isAnonymous'] = (bool) $postData->is_anonymous;
        $info['stickyState'] = $postData->sticky_state;
        $info['digestState'] = $postData->digest_state;
        $info['viewCount'] = $postData->view_count;
        $info['likeCount'] = $configKeys['post_like_public_count'] ? $postData->like_count : null;
        $info['dislikeCount'] = $configKeys['post_dislike_public_count'] ? $postData->dislike_count : null;
        $info['followCount'] = $configKeys['post_follow_public_count'] ? $postData->follow_count : null;
        $info['blockCount'] = $configKeys['post_block_public_count'] ? $postData->block_count : null;
        $info['commentCount'] = $postData->comment_count;
        $info['commentDigestCount'] = $postData->comment_digest_count;
        $info['commentLikeCount'] = $configKeys['comment_like_public_count'] ? $postData->comment_like_count : null;
        $info['commentDislikeCount'] = $configKeys['comment_dislike_public_count'] ? $postData->comment_dislike_count : null;
        $info['commentFollowCount'] = $configKeys['comment_follow_public_count'] ? $postData->comment_follow_count : null;
        $info['commentBlockCount'] = $configKeys['comment_block_public_count'] ? $postData->comment_block_count : null;
        $info['quoteCount'] = $postData->quote_count;
        $info['createdDatetime'] = $postData->created_at;
        $info['createdTimeAgo'] = null;
        $info['editedDatetime'] = $postData->last_edit_at;
        $info['editedTimeAgo'] = null;
        $info['editedCount'] = $postData->edit_count;
        $info['lastCommentDatetime'] = $postData->last_comment_at;
        $info['lastCommentTimeAgo'] = null;
        $info['rankState'] = $postData->rank_state;
        $info['status'] = (bool) $postData->is_enabled;

        $readConfig = $permissions['readConfig'] ?? [];
        $associatedUserListConfig = $permissions['associatedUserListConfig'] ?? [];
        $commentConfig = $permissions['commentConfig'] ?? [];

        $info['readConfig'] = [
            'isReadLocked' => $readConfig['isReadLocked'] ?? false,
            'previewPercentage' => $readConfig['previewPercentage'] ?? 0,
            'buttonName' => StrHelper::languageContent($readConfig['buttonName'] ?? null, $langTag),
            'buttonUrl' => PluginHelper::fresnsPluginUrlByFskey($readConfig['appFskey'] ?? null),
        ];

        $info['associatedUserListConfig'] = [
            'hasUserList' => $associatedUserListConfig['hasUserList'] ?? false,
            'userListName' => StrHelper::languageContent($associatedUserListConfig['userListName'] ?? null, $langTag),
            'userListCount' => ($associatedUserListConfig['hasUserList'] ?? false) ? PostUser::where('post_id', $postData->id)->count() : 0,
            'userListUrl' => PluginHelper::fresnsPluginUrlByFskey($associatedUserListConfig['appFskey'] ?? null),
        ];

        $info['commentConfig'] = [
            'visible' => $commentConfig['visible'] ?? true,
            'policy' => $commentConfig['policy'] ?? User::POLICY_EVERYONE,
            'privacy' => $commentConfig['privacy'] ?? 'public',
            'action' => [
                'hasActionButton' => $commentConfig['action']['hasActionButton'] ?? false,
                'buttonName' => StrHelper::languageContent($commentConfig['action']['buttonName'] ?? null, $langTag),
                'buttonStyle' => $commentConfig['action']['buttonStyle'] ?? null,
                'buttonUrl' => PluginHelper::fresnsPluginUrlByFskey($commentConfig['action']['appFskey'] ?? null),
            ],
        ];

        $info['moreInfo'] = $postData->more_info;

        return $info;
    }
}
