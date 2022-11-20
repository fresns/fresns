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
    public function commentData(?Comment $comment, string $type, string $langTag, string $timezone, ?int $authUserId = null, ?int $authUserMapId = null, ?string $authUserLng = null, ?string $authUserLat = null, ?bool $isCommentPreview = true)
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
        $item['files'] = FileHelper::fresnsFileInfoListByTableColumn('comments', 'id', $comment->id);

        $fileCount['images'] = collect($item['files']['images'])->count();
        $fileCount['videos'] = collect($item['files']['videos'])->count();
        $fileCount['audios'] = collect($item['files']['audios'])->count();
        $fileCount['documents'] = collect($item['files']['documents'])->count();
        $item['fileCount'] = $fileCount;

        $item['hashtags'] = [];
        if ($comment->hashtags->isNotEmpty()) {
            $hashtagService = new HashtagService;

            foreach ($comment->hashtags as $hashtag) {
                $hashtagItem[] = $hashtagService->hashtagData($hashtag, $langTag, $timezone, $authUserId);
            }
            $item['hashtags'] = $hashtagItem;
        }

        $userService = new UserService;

        $item['creator'] = InteractiveHelper::fresnsUserAnonymousProfile();
        $item['creator']['isPostCreator'] = false;
        if (! $comment->is_anonymous) {
            $item['creator'] = $userService->userData($comment->creator, $langTag, $timezone, $authUserId);

            $item['creator']['isPostCreator'] = $comment->user_id == $post->user_id ? true : false;
        }

        $item['replyToUser'] = null;
        if ($comment->top_parent_id != $comment->parent_id && ! $comment?->parentComment) {
            if ($comment->parentComment->is_anonymous) {
                $item['replyToUser'] = InteractiveHelper::fresnsUserAnonymousProfile();
            }

            $item['replyToUser'] = $userService->userData($comment->parentComment->creator, $langTag, $timezone, $authUserId);
        }

        $item['commentPreviews'] = null;
        $previewConfig = ConfigHelper::fresnsConfigByItemKey('comment_preview');
        if ($type == 'list' && $isCommentPreview && $previewConfig != 0) {
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
        $interactiveStatus = InteractiveUtility::getInteractiveStatus(InteractiveUtility::TYPE_COMMENT, $comment->id, $authUserId);
        $interactiveCreatorLike['postCreatorLikeStatus'] = InteractiveUtility::checkUserLike(InteractiveUtility::TYPE_COMMENT, $comment->id, $post->user_id);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus, $interactiveCreatorLike);

        $item['followType'] = null;
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

        $commentInfo['content'] = ContentUtility::replaceBlockWords('content', $commentInfo['content']);
        $commentInfo['content'] = ContentUtility::handleAndReplaceAll($commentInfo['content'], $comment->is_markdown, $comment->user_id, Mention::TYPE_COMMENT, $authUserId);

        return $commentInfo;
    }

    // get comment previews
    public static function getCommentPreviews(int $commentId, int $limit, string $langTag, string $timezone)
    {
        $comments = Comment::with(['commentAppend', 'post', 'creator', 'hashtags'])
            ->where('parent_id', $commentId)
            ->orderByDesc('like_count')
            ->limit($limit)
            ->get();

        $commentList = [];
        $service = new CommentService();

        /** @var Comment $comment */
        foreach ($comments as $comment) {
            $commentList[] = $service->commentData($comment, 'list', $langTag, $timezone, null, null, null, null, false);
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
            $userService = new UserService;

            $item['creator'] = $userService->userData($post->creator, $langTag, $timezone);
        }

        $data = array_merge($postInfo, $contentHandle, $item);

        return $data;
    }

    // comment log data
    // $type = list or detail
    public function commentLogData(CommentLog $log, string $type, string $langTag, string $timezone)
    {
        $comment = $log?->comment;

        $info['id'] = $log->id;
        $info['cid'] = $comment?->cid;
        $info['isPluginEditor'] = (bool) $log->is_plugin_editor;
        $info['editorUnikey'] = $log->editor_unikey;
        $info['editorUrl'] = ! empty($log->editor_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($log->editor_unikey) : null;
        $info['content'] = $log->content;
        $info['contentLength'] = Str::length($log->content);
        $info['isBrief'] = false;

        $briefLength = ConfigHelper::fresnsConfigByItemKey('comment_editor_brief_length');
        if ($type == 'list' && $info['contentLength'] > $briefLength) {
            $info['content'] = Str::limit($log->content, $briefLength);
            $info['isBrief'] = true;
        }

        $info['isMarkdown'] = (bool) $log->is_markdown;
        $info['isAnonymous'] = (bool) $log->is_anonymous;
        $info['mapJson'] = $log->map_json;
        $info['state'] = $log->state;
        $info['reason'] = $log->reason;

        $info['creator'] = InteractiveHelper::fresnsUserAnonymousProfile();
        if (! $log->is_anonymous) {
            $userService = new UserService;

            $item['creator'] = $userService->userData($log->creator, $langTag, $timezone);
        }

        $info['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_POST_LOG, $log->id, $langTag);
        $info['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_POST_LOG, $log->id, $langTag);
        $info['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_POST_LOG, $log->id, $langTag);
        $info['files'] = FileHelper::fresnsFileInfoListByTableColumn('post_logs', 'id', $log->id);

        $fileCount['images'] = collect($info['files']['images'])->count();
        $fileCount['videos'] = collect($info['files']['videos'])->count();
        $fileCount['audios'] = collect($info['files']['audios'])->count();
        $fileCount['documents'] = collect($info['files']['documents'])->count();
        $info['fileCount'] = $fileCount;

        return $info;
    }
}
