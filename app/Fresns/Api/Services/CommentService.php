<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\InteractiveHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;
use App\Models\ArchiveUsage;
use App\Models\Comment;
use App\Models\CommentLog;
use App\Models\ExtendUsage;
use App\Models\Mention;
use App\Models\OperationUsage;
use App\Models\PluginUsage;
use App\Models\Post;
use App\Utilities\ContentUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractiveUtility;
use App\Utilities\LbsUtility;
use App\Utilities\PermissionUtility;
use Illuminate\Support\Str;

class CommentService
{
    // $type = list or detail
    public function commentData(?Comment $comment, string $type, string $langTag, string $timezone, ?int $authUserId = null, ?int $authUserMapId = null, ?string $authUserLng = null, ?string $authUserLat = null)
    {
        if (! $comment) {
            return null;
        }

        $commentInfo = $comment->getCommentInfo($langTag, $timezone);
        $commentAppend = $comment->commentAppend;
        $post = $comment->post;
        $postAppend = $comment->postAppend;

        $contentHandle = self::contentHandle($comment, $type, $authUserId);

        if (! empty($comment->map_id) && ! empty($authUserLng) && ! empty($authUserLat)) {
            $postLng = $comment->map_longitude;
            $postLat = $comment->map_latitude;
            $commentInfo['location']['distance'] = LbsUtility::getDistanceWithUnit($langTag, $postLng, $postLat, $authUserLng, $authUserLat);
        }

        $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_COMMENT, $comment->id, $langTag);
        $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_COMMENT, $comment->id, $langTag);
        $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_COMMENT, $comment->id, $langTag);
        $item['files'] = FileHelper::fresnsAntiLinkFileInfoListByTableColumn('comments', 'id', $comment->id);

        $fileCount['images'] = collect($item['files']['images'])->count();
        $fileCount['videos'] = collect($item['files']['videos'])->count();
        $fileCount['audios'] = collect($item['files']['audios'])->count();
        $fileCount['documents'] = collect($item['files']['documents'])->count();
        $item['fileCount'] = $fileCount;

        $item['hashtags'] = null;
        if ($comment->hashtags->isNotEmpty()) {
            $hashtagService = new HashtagService;

            foreach ($comment->hashtags as $hashtag) {
                $hashtagItem[] = $hashtagService->hashtagData($hashtag, $langTag, $authUserId);
            }
            $item['hashtags'] = $hashtagItem;
        }

        $item['creator'] = InteractiveHelper::fresnsUserAnonymousProfile();
        $item['creator']['isPostCreator'] = false;
        if (! $comment->is_anonymous) {
            $creatorProfile = $comment->creator->getUserProfile($langTag, $timezone);
            $creatorMainRole = $comment->creator->getUserMainRole($langTag, $timezone);
            $creatorItem['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_USER, $comment->creator->id, $langTag);
            $creatorItem['isPostCreator'] = $comment->user_id == $post->user_id ? true : false;

            $item['creator'] = array_merge($creatorProfile, $creatorMainRole, $creatorItem);
        }

        $item['replyToUser'] = null;
        if ($comment->top_parent_id != $comment->parent_id) {
            $item['replyToUser'] = self::getReplyToUser($comment?->parentComment, $langTag, $timezone);
        }

        $item['commentPreviews'] = null;
        $previewConfig = ConfigHelper::fresnsConfigByItemKey('comment_preview');
        if ($type == 'list' && $previewConfig != 0) {
            $item['commentPreviews'] = self::getCommentPreviews($comment->id, $previewConfig, $langTag, $timezone);
        }

        $isMe = $comment->user_id == $authUserId ? true : false;

        $commentBtn['status'] = false;
        $commentBtn['name'] = null;
        $commentBtn['url'] = null;
        $commentBtn['style'] = null;

        if ($isMe && $commentAppend->is_close_btn) {
            $commentBtn['status'] = true;
            if ($commentAppend->is_change_btn) {
                $commentBtn['name'] = LanguageHelper::fresnsLanguageByTableId('posts', 'comment_btn_name', $postAppend->post_id, $langTag);
                $commentBtn['style'] = $postAppend->comment_btn_style;
            } else {
                $commentBtn['name'] = ConfigHelper::fresnsConfigByItemKey($commentAppend->btn_name_key, $langTag);
                $commentBtn['style'] = $commentAppend->btn_style;
            }
            $editStatus['url'] = ! empty($postAppend->comment_btn_plugin_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($postAppend->comment_btn_plugin_unikey) : null;
        }

        $item['commentBtn'] = $commentBtn;

        $item['manages'] = ExtendUtility::getPluginUsages(PluginUsage::TYPE_MANAGE, null, PluginUsage::SCENE_COMMENT, $authUserId, $langTag);

        $editStatus['isMe'] = false;
        $editStatus['canDelete'] = false;
        $editStatus['canEdit'] = false;
        $editStatus['isPluginEditor'] = false;
        $editStatus['editorUrl'] = null;

        if ($isMe) {
            $editStatus['isMe'] = true;
            $editStatus['canDelete'] = (bool) $commentAppend->can_delete;
            $editStatus['canEdit'] = PermissionUtility::checkContentIsCanEdit('comment', $comment->created_at, $comment->is_sticky, $comment->digest_state, $langTag, $timezone);
            $editStatus['isPluginEditor'] = (bool) $commentAppend->is_plugin_editor;
            $editStatus['editorUrl'] = ! empty($commentAppend->editor_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($commentAppend->editor_unikey) : null;
        }
        $item['editStatus'] = $editStatus;

        $interactiveConfig = InteractiveHelper::fresnsCommentInteractive($langTag);
        $interactiveStatus = InteractiveUtility::checkInteractiveStatus(InteractiveUtility::TYPE_COMMENT, $comment->id, $authUserId);
        $interactiveCreatorLike['postCreatorLikeStatus'] = InteractiveUtility::checkUserLike(InteractiveUtility::TYPE_COMMENT, $comment->id, $post->user_id);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus, $interactiveCreatorLike);

        $item['post'] = self::getPost($post, $langTag, $timezone);

        $detail = array_merge($commentInfo, $contentHandle, $item);

        return $detail;
    }

    public static function contentHandle(Comment $comment, string $type, ?int $authUserId = null)
    {
        $postAppend = $comment->postAppend;

        $contentLength = Str::length($comment->content);

        $briefLength = ConfigHelper::fresnsConfigByItemKey('comment_editor_brief_length');

        $item['contentPublic'] = (bool) $postAppend->is_comment_public;
        if (! $item['contentPublic']) {
            $commentInfo['content'] = null;
        } elseif ($type == 'list' && $contentLength > $briefLength) {
            $commentInfo['content'] = Str::limit($comment->content, $briefLength);
            $commentInfo['isBrief'] = true;
        } else {
            $commentInfo['content'] = $comment->content;
        }

        $commentInfo['content'] = ContentUtility::handleAndReplaceAll($commentInfo['content'], $comment->is_markdown, Mention::TYPE_COMMENT, $authUserId);

        return $commentInfo;
    }

    // get reply to user
    public static function getReplyToUser(?Comment $comment, string $langTag, string $timezone)
    {
        if (! $comment) {
            return null;
        }

        if (! $comment->is_anonymous) {
            return $comment->creator->getUserProfile($langTag, $timezone);
        }

        return InteractiveHelper::fresnsUserAnonymousProfile();
    }

    // get comment previews
    public static function getCommentPreviews(int $commentId, int $limit, string $langTag, string $timezone)
    {
        $comments = Comment::with('creator')
            ->where('parent_id', $commentId)
            ->orderByDesc('like_count')
            ->limit($limit)
            ->get();

        $commentList = null;
        $service = new CommentService();

        /** @var Comment $comment */
        foreach ($comments as $comment) {
            $commentList[] = $service->commentData($comment, 'list', $langTag, $timezone);
        }

        return $commentList;
    }

    // get post
    public static function getPost(Post $post, string $langTag, string $timezone)
    {
        $postInfo = $post->getPostInfo($langTag, $timezone);
        $contentHandle = PostService::contentHandle($post, 'list');

        $item['group'] = null;
        if ($post->group) {
            $item['group'] = $post->group->getGroupInfo($langTag);
        }

        $item['creator'] = InteractiveHelper::fresnsUserAnonymousProfile();
        if (! $post->is_anonymous) {
            $item['creator'] = $post->creator->getUserProfile($langTag, $timezone);
        }

        $data = array_merge($postInfo, $contentHandle, $item);

        return $data;
    }

    // comment Log
    public function commentLogList(CommentLog $log, string $langTag, string $timezone, ?int $authUserId = null)
    {
        return null;
    }

    // comment log detail
    public function commentLogDetail(CommentLog $log, string $langTag, string $timezone, ?int $authUserId = null)
    {
        return null;
    }
}
