<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\PluginHelper;
use App\Helpers\InteractiveHelper;
use App\Models\ExtendLinked;
use App\Models\IconLinked;
use App\Models\PluginUsage;
use App\Models\Post;
use App\Models\TipLinked;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractiveUtility;
use App\Utilities\LbsUtility;
use App\Utilities\PermissionUtility;
use Illuminate\Support\Str;

class PostService
{
    public function postDetail(Post $post, string $type, string $langTag, string $timezone, ?int $authUserId = null, ?int $mapId = null, ?string $authUserLng = null, ?string $authUserLat = null)
    {
        $postInfo = $post->getPostInfo($langTag, $timezone);
        $postInfo[] = self::contentHandle($post, $type, $authUserId);

        if (! empty($post->map_id) && ! empty($authUserLng) && ! empty($authUserLat)) {
            $postLng = $post->map_longitude;
            $postLat = $post->map_latitude;
            $postInfo['location']['distance'] = LbsUtility::getDistanceWithUnit($langTag, $postLng, $postLat, $authUserLng, $authUserLat);
        }

        $item['icons'] = ExtendUtility::getIcons(IconLinked::TYPE_POST, $post->id, $langTag);
        $item['tips'] = ExtendUtility::getTips(TipLinked::TYPE_POST, $post->id, $langTag);
        $item['extends'] = ExtendUtility::getExtends(ExtendLinked::TYPE_POST, $post->id, $langTag);
        $item['files'] = FileHelper::fresnsAntiLinkFileInfoListByTableColumn('posts', 'id', $post->id);

        $attachCount['images'] = collect($item['files']['images'])->count();
        $attachCount['videos'] = collect($item['files']['videos'])->count();
        $attachCount['audios'] = collect($item['files']['audios'])->count();
        $attachCount['documents'] = collect($item['files']['documents'])->count();
        $attachCount['icons'] = collect($item['icons'])->count();
        $attachCount['tips'] = collect($item['tips'])->count();
        $attachCount['extends'] = collect($item['extends'])->count();
        $item['attachCount'] = $attachCount;

        $item['group'] = null;
        if ($post->group) {
            $groupInteractiveConfig = InteractiveHelper::fresnsGroupInteractive($langTag);
            $groupInteractiveStatus = InteractiveUtility::checkInteractiveStatus(InteractiveUtility::TYPE_GROUP, $post->group->id, $authUserId);

            $groupItem[] = $post->group?->getGroupInfo($langTag);
            $groupItem['interactive'] = array_merge($groupInteractiveConfig, $groupInteractiveStatus);

            $item['group'] = $groupItem;
        }

        $item['hashtags'] = null;
        if ($post->hashtags) {
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
            $item['creator'] = array_merge($creatorProfile, $creatorMainRole);
        }

        $item['manages'] = ExtendUtility::getPluginExtends(PluginUsage::TYPE_MANAGE, $post->group_id, PluginUsage::SCENE_POST, $authUserId, $langTag);

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

        $detail = array_merge($postInfo, $item);

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

        return $info;
    }

    public static function isCanEdit(string $createTime, int $stickyState, int $digestState): bool
    {
        $editConfig = ConfigHelper::fresnsConfigByItemKeys([
            'post_edit',
            'post_edit_timelimit',
            'post_edit_sticky',
            'post_edit_digest',
        ]);

        if (! $editConfig['post_edit']) {
            return false;
        }

        return false;
    }
}
