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
use App\Models\OperationUsage;
use App\Models\PluginUsage;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractiveUtility;
use App\Utilities\LbsUtility;
use Illuminate\Support\Str;

class CommentService
{
    public function commentList(?Comment $comment, string $langTag, string $timezone, ?int $authUserId = null)
    {
        if (! $comment) {
            return null;
        }

        $commentInfo = $comment->getCommentInfo($langTag, $timezone);
        $commentInfo[] = self::contentHandle($comment, 'list', $authUserId);

        $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_COMMENT, $comment->id, $langTag);

        $item['hashtags'] = null;
        if ($comment->hashtags) {
            $hashtagService = new HashtagService;

            foreach ($comment->hashtags as $hashtag) {
                $hashtagItem[] = $hashtagService->hashtagList($hashtag, $langTag, $authUserId);
            }
            $item['hashtags'] = $hashtagItem;
        }

        $item['creator'] = InteractiveHelper::fresnsUserAnonymousProfile();
        if (! $comment->is_anonymous) {
            $creatorProfile = $comment->creator->getUserProfile($langTag, $timezone);
            $creatorMainRole = $comment->creator->getUserMainRole($langTag, $timezone);
            $item['creator'] = array_merge($creatorProfile, $creatorMainRole);
        }

        $info = array_merge($commentInfo, $item);

        return $info;
    }

    public function commentDetail(Comment $comment, string $type, string $langTag, string $timezone, ?int $authUserId = null, ?int $mapId = null, ?string $authUserLng = null, ?string $authUserLat = null)
    {
        $commentInfo = $comment->getCommentInfo($langTag, $timezone);
        $commentInfo[] = self::contentHandle($comment, $type, $authUserId);
        $commentAppend = $comment->commentAppend;
        $postAppend = $comment->postAppend;

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
        if ($comment->hashtags) {
            $hashtagService = new HashtagService;

            foreach ($comment->hashtags as $hashtag) {
                $hashtagItem[] = $hashtagService->hashtagList($hashtag, $langTag, $authUserId);
            }
            $item['hashtags'] = $hashtagItem;
        }

        $item['creator'] = InteractiveHelper::fresnsUserAnonymousProfile();
        if (! $comment->is_anonymous) {
            $creatorProfile = $comment->creator->getUserProfile($langTag, $timezone);
            $creatorMainRole = $comment->creator->getUserMainRole($langTag, $timezone);
            $item['creator'] = array_merge($creatorProfile, $creatorMainRole);
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
            $editStatus['canEdit'] = self::isCanEdit($comment->created_at, $comment->is_sticky, $comment->digest_state);
            $editStatus['isPluginEditor'] = (bool) $commentAppend->is_plugin_editor;
            $editStatus['editorUrl'] = ! empty($commentAppend->editor_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($commentAppend->editor_unikey) : null;
        }
        $item['editStatus'] = $editStatus;

        $interactiveConfig = InteractiveHelper::fresnsCommentInteractive($langTag);
        $interactiveStatus = InteractiveUtility::checkInteractiveStatus(InteractiveUtility::TYPE_COMMENT, $comment->id, $authUserId);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus);

        $detail = array_merge($commentInfo, $item);

        return $detail;
    }

    public static function contentHandle(Comment $comment, string $type, ?int $authUserId = null)
    {
        $postAppend = $comment->postAppend();
        $contentLength = Str::length($comment->content);

        $briefLength = ConfigHelper::fresnsConfigByItemKey('comment_editor_brief_length');

        $item['contentPublic'] = (bool) $postAppend->is_comment_public;
        if (! $item['contentPublic']) {
            $commentInfo['content'] = null;
        } elseif ($type == 'list' && $contentLength > $briefLength) {
            $commentInfo['content'] = Str::limit($comment->content, $briefLength);
            $commentInfo['isBrief'] = true;
        }

        return $commentInfo;
    }

    public static function isCanEdit(string $createTime, int $isSticky, int $digestState): bool
    {
        $editConfig = ConfigHelper::fresnsConfigByItemKeys([
            'comment_edit',
            'comment_edit_time_limit',
            'comment_edit_sticky_limit',
            'comment_edit_digest_limit',
        ]);

        if (! $editConfig['comment_edit']) {
            return false;
        }

        return false;
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
