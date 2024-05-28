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
use App\Models\Comment;
use Illuminate\Support\Str;

trait CommentServiceTrait
{
    public function getCommentInfo(?string $langTag = null): array
    {
        $commentData = $this;
        $permissions = $commentData->permissions;

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'site_url',
            'website_comment_detail_path',
            'comment_like_public_count',
            'comment_dislike_public_count',
            'comment_follow_public_count',
            'comment_block_public_count',
        ]);

        // https://example.com/comment/{cid}
        $commentUrl = $configKeys['site_url'].'/'.$configKeys['website_comment_detail_path'].'/'.$commentData->cid;

        $info['cid'] = $commentData->cid;
        $info['url'] = $configKeys['site_url'] ? $commentUrl : null;
        $info['privacy'] = ($commentData->privacy_state == Comment::PRIVACY_PUBLIC) ? 'public' : 'private';
        $info['content'] = $commentData->content;
        $info['contentLength'] = Str::length($commentData->content);
        $info['langTag'] = $commentData->lang_tag;
        $info['writingDirection'] = $permissions['contentWritingDirection'] ?? 'ltr'; // ltr or rtl
        $info['isBrief'] = false;
        $info['isMarkdown'] = (bool) $commentData->is_markdown;
        $info['isAnonymous'] = (bool) $commentData->is_anonymous;
        $info['isSticky'] = (bool) $commentData->is_sticky;
        $info['digestState'] = $commentData->digest_state;
        $info['viewCount'] = $commentData->view_count;
        $info['likeCount'] = $configKeys['comment_like_public_count'] ? $commentData->like_count : null;
        $info['dislikeCount'] = $configKeys['comment_dislike_public_count'] ? $commentData->dislike_count : null;
        $info['followCount'] = $configKeys['comment_follow_public_count'] ? $commentData->follow_count : null;
        $info['blockCount'] = $configKeys['comment_block_public_count'] ? $commentData->block_count : null;
        $info['commentCount'] = $commentData->comment_count;
        $info['commentDigestCount'] = $commentData->comment_digest_count;
        $info['commentLikeCount'] = $configKeys['comment_like_public_count'] ? $commentData->comment_like_count : null;
        $info['commentDislikeCount'] = $configKeys['comment_dislike_public_count'] ? $commentData->comment_dislike_count : null;
        $info['commentFollowCount'] = $configKeys['comment_follow_public_count'] ? $commentData->comment_follow_count : null;
        $info['commentBlockCount'] = $configKeys['comment_block_public_count'] ? $commentData->comment_block_count : null;
        $info['createdDatetime'] = $commentData->created_at;
        $info['createdTimeAgo'] = null;
        $info['editedDatetime'] = $commentData->last_edit_at;
        $info['editedTimeAgo'] = null;
        $info['editedCount'] = $commentData->edit_count;
        $info['lastCommentDatetime'] = $commentData->last_comment_at;
        $info['lastCommentTimeAgo'] = null;
        $info['rankState'] = $commentData->rank_state;
        $info['status'] = (bool) $commentData->is_enabled;
        $info['moreInfo'] = $commentData->more_info;

        $info['activeButton'] = [
            'hasActiveButton' => $permissions['activeButton']['hasActiveButton'] ?? false,
            'buttonName' => StrHelper::languageContent($permissions['activeButton']['buttonName'] ?? null, $langTag),
            'buttonStyle' => $permissions['activeButton']['buttonStyle'] ?? null,
            'buttonUrl' => PluginHelper::fresnsPluginUrlByFskey($permissions['activeButton']['appFskey'] ?? null),
        ];

        return $info;
    }
}
