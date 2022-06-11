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
use App\Models\Comment;
use App\Models\ExtendLinked;
use App\Models\IconLinked;
use App\Models\PluginUsage;
use App\Models\TipLinked;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractiveUtility;
use App\Utilities\LbsUtility;
use App\Utilities\PermissionUtility;
use Illuminate\Support\Str;

class CommentService
{
    public function commentDetail(Comment $comment, string $type, string $langTag, string $timezone, ?int $authUserId = null, ?int $mapId = null, ?string $authUserLng = null, ?string $authUserLat = null)
    {
        $commentInfo = $comment->getCommentInfo($langTag, $timezone);
        $postAppend = $comment->postAppend();

        $briefLength = ConfigHelper::fresnsConfigByItemKey('comment_editor_brief_length');

        $item['contentPublic'] = (bool) $postAppend->is_comment_public;
        if (! $item['contentPublic']) {
            $commentInfo['content'] = null;
        } elseif ($type == 'list' && $commentInfo['contentLength'] > $briefLength) {
            $commentInfo['content'] = Str::limit($comment->content, $briefLength);
            $commentInfo['isBrief'] = true;
        }

        if (! empty($comment->map_id) && ! empty($authUserLng) && ! empty($authUserLat)) {
            $postLng = $comment->map_longitude;
            $postLat = $comment->map_latitude;
            $commentInfo['location']['distance'] = LbsUtility::getDistanceWithUnit($langTag, $postLng, $postLat, $authUserLng, $authUserLat);
        }

        $item['icons'] = ExtendUtility::getIcons(IconLinked::TYPE_COMMENT, $comment->id, $langTag);
        $item['tips'] = ExtendUtility::getTips(TipLinked::TYPE_COMMENT, $comment->id, $langTag);
        $item['extends'] = ExtendUtility::getExtends(ExtendLinked::TYPE_COMMENT, $comment->id, $langTag);
        $item['files'] = FileHelper::fresnsAntiLinkFileInfoListByTableColumn('comments', 'id', $comment->id);

        $attachCount['images'] = collect($item['files']['images'])->count();
        $attachCount['videos'] = collect($item['files']['videos'])->count();
        $attachCount['audios'] = collect($item['files']['audios'])->count();
        $attachCount['documents'] = collect($item['files']['documents'])->count();
        $attachCount['icons'] = collect($item['icons'])->count();
        $attachCount['tips'] = collect($item['tips'])->count();
        $attachCount['extends'] = collect($item['extends'])->count();
        $item['attachCount'] = $attachCount;

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

        if ($isMe && $comment->commentAppend->is_close_btn) {
            $commentBtn['status'] = true;
            if ($comment->commentAppend->is_change_btn) {
                $commentBtn['name'] = LanguageHelper::fresnsLanguageByTableId('posts', 'comment_btn_name', $postAppend->post_id, $langTag);
                $commentBtn['style'] = $postAppend->comment_btn_style;
            } else {
                $commentBtn['name'] = ConfigHelper::fresnsConfigByItemKey($comment->commentAppend->btn_name_key, $langTag);
                $commentBtn['style'] = $comment->commentAppend->btn_style;
            }
            $editStatus['url'] = ! empty($postAppend->comment_btn_plugin_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($postAppend->comment_btn_plugin_unikey) : null;
        }

        $item['commentBtn'] = $commentBtn;

        $item['manages'] = ExtendUtility::getPluginExtends(PluginUsage::TYPE_MANAGE, null, PluginUsage::SCENE_COMMENT, $authUserId, $langTag);

        $editStatus['isMe'] = false;
        $editStatus['canDelete'] = false;
        $editStatus['canEdit'] = false;
        $editStatus['isPluginEditor'] = false;
        $editStatus['editorUrl'] = null;

        if ($isMe) {
            $editStatus['isMe'] = true;
            $editStatus['canDelete'] = (bool) $comment->postAppend->can_delete;
            $editStatus['canEdit'] = self::isCanEdit($comment->created_at, $comment->is_sticky, $comment->digest_state);
            $editStatus['isPluginEditor'] = (bool) $comment->postAppend->is_plugin_editor;
            $editStatus['editorUrl'] = ! empty($comment->postAppend->editor_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($comment->postAppend->editor_unikey) : null;
        }
        $item['editStatus'] = $editStatus;

        $interactiveConfig = InteractiveHelper::fresnsCommentInteractive($langTag);
        $interactiveStatus = InteractiveUtility::checkInteractiveStatus(InteractiveUtility::TYPE_COMMENT, $comment->id, $authUserId);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus);

        $detail = array_merge($commentInfo, $item);

        return $detail;
    }

    public static function isCanEdit(string $createTime, int $isSticky, int $digestState): bool
    {
        $editConfig = ConfigHelper::fresnsConfigByItemKeys([
            'comment_edit',
            'comment_edit_timelimit',
            'comment_edit_sticky',
            'comment_edit_digest',
        ]);

        if (! $editConfig['comment_edit']) {
            return false;
        }

        return false;
    }
}
