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
use App\Helpers\PluginHelper;
use App\Models\ArchiveUsage;
use App\Models\Comment;
use App\Models\ExtendUsage;
use App\Models\Mention;
use App\Models\OperationUsage;
use App\Models\PluginUsage;
use App\Models\Post;
use App\Models\PostLog;
use App\Utilities\ContentUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractiveUtility;
use App\Utilities\LbsUtility;
use App\Utilities\PermissionUtility;
use Illuminate\Support\Str;

class PostService
{
    // $type = list or detail
    public function postData(?Post $post, string $type, string $langTag, string $timezone, ?int $authUserId = null, ?int $authUserMapId = null, ?string $authUserLng = null, ?string $authUserLat = null)
    {
        if (! $post) {
            return null;
        }

        $postInfo = $post->getPostInfo($langTag, $timezone);
        $postInfo['title'] = ContentUtility::replaceBlockWords('content', $postInfo['title']);
        $contentHandle = self::contentHandle($post, $type, $authUserId);

        if (! empty($post->map_id) && ! empty($authUserLng) && ! empty($authUserLat)) {
            $postLng = $post->map_longitude;
            $postLat = $post->map_latitude;
            $postInfo['location']['distance'] = LbsUtility::getDistanceWithUnit($langTag, $postLng, $postLat, $authUserLng, $authUserLat);
        }

        $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_POST, $post->id, $langTag);
        $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_POST, $post->id, $langTag);
        $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_POST, $post->id, $langTag);
        $item['files'] = FileHelper::fresnsAntiLinkFileInfoListByTableColumn('posts', 'id', $post->id);

        $fileCount['images'] = collect($item['files']['images'])->count();
        $fileCount['videos'] = collect($item['files']['videos'])->count();
        $fileCount['audios'] = collect($item['files']['audios'])->count();
        $fileCount['documents'] = collect($item['files']['documents'])->count();
        $item['fileCount'] = $fileCount;

        $item['group'] = null;
        if ($post->group) {
            $groupInteractiveConfig = InteractiveHelper::fresnsGroupInteractive($langTag);
            $groupInteractiveStatus = InteractiveUtility::getInteractiveStatus(InteractiveUtility::TYPE_GROUP, $post->group->id, $authUserId);

            $groupItem = $post->group->getGroupInfo($langTag);
            $groupItem['interactive'] = array_merge($groupInteractiveConfig, $groupInteractiveStatus);

            $item['group'] = $groupItem;
        }

        $item['hashtags'] = null;
        if ($post->hashtags->isNotEmpty()) {
            $hashtagService = new HashtagService;

            foreach ($post->hashtags as $hashtag) {
                $hashtagItem[] = $hashtagService->hashtagData($hashtag, $langTag, $timezone, $authUserId);
            }
            $item['hashtags'] = $hashtagItem;
        }

        $item['creator'] = InteractiveHelper::fresnsUserAnonymousProfile();
        if (! $post->is_anonymous) {
            $creatorProfile = $post->creator->getUserProfile($langTag, $timezone);
            $creatorMainRole = $post->creator->getUserMainRole($langTag, $timezone);
            $creatorOperations['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_USER, $post->creator->id, $langTag);

            $item['creator'] = array_merge($creatorProfile, $creatorMainRole, $creatorOperations);
        }

        $item['topComment'] = null;

        $topCommentRequire = ConfigHelper::fresnsConfigByItemKey('top_comment_require');
        if ($type == 'list' && $topCommentRequire != 0 && $topCommentRequire < $post->comment_like_count) {
            $item['topComment'] = self::getTopComment($post->id, $langTag, $timezone);
        }

        $item['manages'] = ExtendUtility::getPluginUsages(PluginUsage::TYPE_MANAGE, $post->group_id, PluginUsage::SCENE_POST, $authUserId, $langTag);

        $editStatus['isMe'] = false;
        $editStatus['canDelete'] = false;
        $editStatus['canEdit'] = false;
        $editStatus['isPluginEditor'] = false;
        $editStatus['editorUrl'] = null;

        $isMe = $post->user_id == $authUserId ? true : false;
        if ($isMe) {
            $editStatus['isMe'] = true;
            $editStatus['canDelete'] = (bool) $post->postAppend->can_delete;
            $editStatus['canEdit'] = PermissionUtility::checkContentIsCanEdit('post', $post->created_at, $post->sticky_state, $post->digest_state, $langTag, $timezone);
            $editStatus['isPluginEditor'] = (bool) $post->postAppend->is_plugin_editor;
            $editStatus['editorUrl'] = ! empty($post->postAppend->editor_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($post->postAppend->editor_unikey) : null;
        }
        $item['editStatus'] = $editStatus;

        $interactiveConfig = InteractiveHelper::fresnsPostInteractive($langTag);
        $interactiveStatus = InteractiveUtility::getInteractiveStatus(InteractiveUtility::TYPE_POST, $post->id, $authUserId);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus);

        $commentVisibilityRule = ConfigHelper::fresnsConfigByItemKey('comment_visibility_rule');
        $item['commentHidden'] = false;
        if ($commentVisibilityRule > 0) {
            $visibilityTime = $post->created_at->addDay($commentVisibilityRule);

            $item['commentHidden'] = $visibilityTime->lt(now());
        }

        $item['followType'] = null;

        $detail = array_merge($postInfo, $contentHandle, $item);

        return $detail;
    }

    public static function contentHandle(Post $post, string $type, ?int $authUserId = null)
    {
        $appendData = $post->postAppend;
        $contentLength = Str::length($post->content);

        $info['isAllow'] = (bool) $appendData->is_allow;

        $content = $post->content;
        if ($appendData->is_allow) {
            $allowProportion = intval($appendData->allow_proportion) / 100;
            $allowLength = intval($contentLength * $allowProportion);

            $checkPostAllow = PermissionUtility::checkPostAllow($post->id, $authUserId);

            if ($checkPostAllow) {
                $content = $post->content;
                $info['isAllow'] = false;
            } else {
                $content = Str::limit($post->content, $allowLength);
            }
        }

        $briefLength = ConfigHelper::fresnsConfigByItemKey('post_editor_brief_length');

        $info['content'] = $content;

        if ($type == 'list' && $contentLength > $briefLength) {
            $info['content'] = Str::limit($content, $briefLength);
            $info['isBrief'] = true;
        }

        $info['content'] = ContentUtility::replaceBlockWords('content', $info['content']);
        $info['content'] = ContentUtility::handleAndReplaceAll($info['content'], $post->is_markdown, Mention::TYPE_POST, $post->id);

        return $info;
    }

    // get top comment
    public static function getTopComment(int $postId, string $langTag, string $timezone)
    {
        $comment = Comment::with('creator')
            ->where('post_id', $postId)
            ->whereNull('top_parent_id')
            ->orderByDesc('like_count')
            ->first();

        $service = new CommentService();

        return $service->commentData($comment, 'list', $langTag, $timezone);
    }

    // post log data
    // $type = list or detail
    public function postLogData(PostLog $log, string $type, string $langTag, string $timezone)
    {
        $post = $log?->post;
        $group = $log?->group;

        $info['id'] = $log->id;
        $info['pid'] = $post?->pid;
        $info['isPluginEditor'] = (bool) $log->is_plugin_editor;
        $info['editorUnikey'] = $log->editor_unikey;
        $info['editorUrl'] = ! empty($log->editor_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($log->editor_unikey) : null;
        $info['group'] = null;
        $info['title'] = $log->title;
        $info['content'] = $log->content;
        $info['contentLength'] = Str::length($log->content);
        $info['isBrief'] = false;

        $briefLength = ConfigHelper::fresnsConfigByItemKey('post_editor_brief_length');
        if ($type == 'list' && $info['contentLength'] > $briefLength) {
            $info['content'] = Str::limit($log->content, $briefLength);
            $info['isBrief'] = true;
        }

        $info['isMarkdown'] = (bool) $log->is_markdown;
        $info['isAnonymous'] = (bool) $log->is_anonymous;
        $info['isComment'] = (bool) $log->is_comment;
        $info['isCommentPublic'] = (bool) $log->is_comment_public;
        $info['mapJson'] = $log->map_json;
        $info['allowJson'] = ContentUtility::handleAllowJson($log->allow_json, $langTag, $timezone);
        $info['userListJson'] = ContentUtility::handleUserListJson($log->user_list_json, $langTag);
        $info['commentBtnJson'] = ContentUtility::handleCommentBtnJson($log->comment_btn_json, $langTag);
        $info['state'] = $log->state;
        $info['reason'] = $log->reason;

        $info['creator'] = InteractiveHelper::fresnsUserAnonymousProfile();
        if (! $log->is_anonymous) {
            $creatorProfile = $log->creator->getUserProfile($langTag, $timezone);
            $creatorMainRole = $log->creator->getUserMainRole($langTag, $timezone);
            $creatorOperations['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_USER, $log->creator->id, $langTag);
            $item['creator'] = array_merge($creatorProfile, $creatorMainRole, $creatorOperations);
        }

        if ($group) {
            $groupItem[] = $group?->getGroupInfo($langTag);

            $info['group'] = $groupItem;
        }

        $info['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_POST_LOG, $log->id, $langTag);
        $info['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_POST_LOG, $log->id, $langTag);
        $info['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_POST_LOG, $log->id, $langTag);
        $info['files'] = FileHelper::fresnsAntiLinkFileInfoListByTableColumn('post_logs', 'id', $log->id);

        $fileCount['images'] = collect($info['files']['images'])->count();
        $fileCount['videos'] = collect($info['files']['videos'])->count();
        $fileCount['audios'] = collect($info['files']['audios'])->count();
        $fileCount['documents'] = collect($info['files']['documents'])->count();
        $info['fileCount'] = $fileCount;

        return $info;
    }
}
