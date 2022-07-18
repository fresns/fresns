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
    public function postList(?Post $post, string $langTag, string $timezone, ?int $authUserId = null)
    {
        if (! $post) {
            return null;
        }

        $postInfo = $post->getPostInfo($langTag, $timezone);
        $contentHandle = self::contentHandle($post, 'list', $authUserId);

        $item['group'] = null;
        if ($post->group) {
            $groupInteractiveConfig = InteractiveHelper::fresnsGroupInteractive($langTag);
            $groupInteractiveStatus = InteractiveUtility::checkInteractiveStatus(InteractiveUtility::TYPE_GROUP, $post->group->id, $authUserId);

            $groupItem[] = $post->group?->getGroupInfo($langTag);
            $groupItem['interactive'] = array_merge($groupInteractiveConfig, $groupInteractiveStatus);

            $item['group'] = $groupItem;
        }

        $item['hashtags'] = null;
        if ($post->hashtags->isNotEmpty()) {
            $hashtagService = new HashtagService;

            foreach ($post->hashtags as $hashtag) {
                $hashtagItem[] = $hashtagService->hashtagList($hashtag, $langTag, $authUserId);
            }
            $item['hashtags'] = $hashtagItem;
        }

        $item['creator'] = InteractiveHelper::fresnsUserAnonymousProfile();
        if (! $post->is_anonymous) {
            $creatorProfile = $post->creator->getUserProfile($langTag, $timezone);
            $creatorMainRole = $post->creator->getUserMainRole($langTag, $timezone);
            $creatorOperations = ExtendUtility::getOperations(OperationUsage::TYPE_USER, $post->creator->id, $langTag);
            $item['creator'] = array_merge($creatorProfile, $creatorMainRole, $creatorOperations);
        }

        $info = array_merge($postInfo, $contentHandle, $item);

        return $info;
    }

    public function postDetail(Post $post, string $type, string $langTag, string $timezone, ?int $authUserId = null, ?int $mapId = null, ?string $authUserLng = null, ?string $authUserLat = null)
    {
        $postInfo = $post->getPostInfo($langTag, $timezone);
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
            $groupInteractiveStatus = InteractiveUtility::checkInteractiveStatus(InteractiveUtility::TYPE_GROUP, $post->group->id, $authUserId);

            $groupItem = $post->group?->getGroupInfo($langTag);
            $groupItem['interactive'] = array_merge($groupInteractiveConfig, $groupInteractiveStatus);

            $item['group'] = $groupItem;
        }

        $item['hashtags'] = null;
        if ($post->hashtags->isNotEmpty()) {
            $hashtagService = new HashtagService;

            foreach ($post->hashtags as $hashtag) {
                $hashtagItem[] = $hashtagService->hashtagList($hashtag, $langTag, $authUserId);
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
            $editStatus['canEdit'] = self::isCanEdit($post->created_at, $post->sticky_state, $post->digest_state);
            $editStatus['isPluginEditor'] = (bool) $post->postAppend->is_plugin_editor;
            $editStatus['editorUrl'] = ! empty($post->postAppend->editor_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($post->postAppend->editor_unikey) : null;
        }
        $item['editStatus'] = $editStatus;

        $interactiveConfig = InteractiveHelper::fresnsPostInteractive($langTag);
        $interactiveStatus = InteractiveUtility::checkInteractiveStatus(InteractiveUtility::TYPE_POST, $post->id, $authUserId);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus);

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

            if (empty($authUserId)) {
                $content = Str::limit($post->content, $allowLength);
            } else {
                $checkPostAllow = PermissionUtility::checkPostAllow($post->id, $authUserId);
                if (! $checkPostAllow) {
                    $content = Str::limit($post->content, $allowLength);
                } else {
                    $content = $post->content;
                    $info['isAllow'] = false;
                }
            }
        }

        $newContentLength = Str::length($content);
        $briefLength = ConfigHelper::fresnsConfigByItemKey('post_editor_brief_length');

        $info['content'] = $content;
        $info['isBrief'] = false;
        if ($type == 'list' && $newContentLength > $briefLength) {
            $info['content'] = Str::limit($content, $briefLength);
            $info['isBrief'] = true;
        }

        $info['content'] = ContentUtility::handleAndReplaceAll($info['content'], $post->is_markdown, Mention::TYPE_POST, $authUserId);

        return $info;
    }

    public static function isCanEdit(string $createTime, int $stickyState, int $digestState): bool
    {
        $editConfig = ConfigHelper::fresnsConfigByItemKeys([
            'post_edit',
            'post_edit_time_limit',
            'post_edit_sticky_limit',
            'post_edit_digest_limit',
        ]);

        if (! $editConfig['post_edit']) {
            return false;
        }

        return false;
    }

    // post Log
    public function postLogList(PostLog $log, string $langTag, string $timezone)
    {
        $post = $log?->post;
        $user = $log->user;
        $group = $log?->group;

        $info['id'] = $log->id;
        $info['uid'] = $user->uid;
        $info['pid'] = $post?->pid;
        $info['isPluginEditor'] = (bool) $log->is_plugin_editor;
        $info['editorUnikey'] = $log->editor_unikey;
        $info['group'] = null;
        $info['title'] = $log->title;
        $info['content'] = $log->content;
        $info['contentLength'] = Str::length($log->content);
        $info['isMarkdown'] = (bool) $log->is_markdown;
        $info['isAnonymous'] = (bool) $log->is_anonymous;
        $info['state'] = $log->state;
        $info['reason'] = $log->reason;

        $info['creator'] = InteractiveHelper::fresnsUserAnonymousProfile();
        if (! $log->is_anonymous) {
            $creatorProfile = $log->creator->getUserProfile($langTag, $timezone);
            $creatorMainRole = $log->creator->getUserMainRole($langTag, $timezone);
            $creatorOperations = ExtendUtility::getOperations(OperationUsage::TYPE_USER, $post->creator->id, $langTag);
            $item['creator'] = array_merge($creatorProfile, $creatorMainRole, $creatorOperations);
        }

        if ($group) {
            $groupItem[] = $group?->getGroupInfo($langTag);

            $info['group'] = $groupItem;
        }

        return $info;
    }

    // post log detail
    public function postLogDetail(PostLog $log, string $langTag, string $timezone)
    {
        $post = $log?->post;
        $user = $log->user;
        $group = $log?->group;

        $info['id'] = $log->id;
        $info['uid'] = $user->uid;
        $info['pid'] = $post?->pid;
        $info['isPluginEditor'] = (bool) $log->is_plugin_editor;
        $info['editorUnikey'] = $log->editor_unikey;
        $info['group'] = null;
        $info['title'] = $log->title;
        $info['content'] = $log->content;
        $info['contentLength'] = Str::length($log->content);
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
            $creatorOperations = ExtendUtility::getOperations(OperationUsage::TYPE_USER, $post->creator->id, $langTag);
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
