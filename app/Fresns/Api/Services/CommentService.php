<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\InteractionHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;
use App\Helpers\PrimaryHelper;
use App\Models\ArchiveUsage;
use App\Models\Comment;
use App\Models\CommentLog;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\Mention;
use App\Models\OperationUsage;
use App\Models\Post;
use App\Utilities\ContentUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractionUtility;
use App\Utilities\LbsUtility;
use App\Utilities\PermissionUtility;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CommentService
{
    // $type = list or detail
    public function commentData(?Comment $comment, string $type, string $langTag, string $timezone, bool $isPreviewPost, ?int $authUserId = null, ?int $authUserMapId = null, ?string $authUserLng = null, ?string $authUserLat = null, ?bool $outputSubComments = false)
    {
        if (! $comment) {
            return null;
        }

        $cacheKey = "fresns_api_comment_{$comment->cid}_{$langTag}";

        $commentData = Cache::get($cacheKey);

        if (empty($commentData)) {
            $commentAppend = $comment->commentAppend;
            $post = $comment->post;
            $postAppend = $comment->postAppend;

            $commentInfo = $comment->getCommentInfo($langTag);

            $item['isCommentPublic'] = (bool) $postAppend->is_comment_public;

            // extend list
            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_COMMENT, $comment->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_COMMENT, $comment->id, $langTag);
            $item['extends'] = ExtendUtility::getContentExtends(ExtendUsage::TYPE_COMMENT, $comment->id, $langTag);

            // file
            $item['files'] = FileHelper::fresnsFileInfoListByTableColumn('comments', 'id', $comment->id);

            $fileCount['images'] = collect($item['files']['images'])->count();
            $fileCount['videos'] = collect($item['files']['videos'])->count();
            $fileCount['audios'] = collect($item['files']['audios'])->count();
            $fileCount['documents'] = collect($item['files']['documents'])->count();
            $item['fileCount'] = $fileCount;

            // hashtags
            $item['hashtags'] = [];
            if ($comment->hashtags->isNotEmpty()) {
                $hashtagService = new HashtagService;

                foreach ($comment->hashtags as $hashtag) {
                    $hashtagItem[] = $hashtagService->hashtagData($hashtag, $langTag, $timezone);
                }

                $item['hashtags'] = $hashtagItem;
            }

            // creator
            $userService = new UserService;
            $item['creator'] = $userService->userData($comment->creator, $langTag, $timezone);
            $item['creator']['isPostCreator'] = $comment->user_id == $post->user_id ? true : false;

            // reply to user
            $item['replyToUser'] = null;
            if ($comment->top_parent_id != $comment->parent_id) {
                $parentComment = $comment->parentComment;

                if ($parentComment->is_anonymous) {
                    $item['replyToUser'] = InteractionHelper::fresnsUserAnonymousProfile();
                } else {
                    $parentCommentUser = PrimaryHelper::fresnsModelById('user', $parentComment->user_id);
                    $userService = new UserService;
                    $item['replyToUser'] = $userService->userData($parentCommentUser, $langTag, $timezone);
                }
            }

            $item['subComments'] = [];

            $item['extendBtn'] = [
                'status' => (bool) $postAppend->is_comment_btn,
                'type' => $commentAppend->is_change_btn ? 'active' : 'default',
                'default' => [
                    'name' => LanguageHelper::fresnsLanguageByTableId('post_appends', 'comment_btn_name', $post->id, $langTag),
                    'style' => $postAppend->comment_btn_style,
                    'url' => PluginHelper::fresnsPluginUrlByUnikey($postAppend->comment_btn_plugin_unikey),
                ],
                'active' => [
                    'name' => $commentAppend->btn_name_key ? ConfigHelper::fresnsConfigByItemKey($commentAppend->btn_name_key, $langTag) : null,
                    'style' => $commentAppend->btn_style,
                    'url' => PluginHelper::fresnsPluginUrlByUnikey($postAppend->comment_btn_plugin_unikey),
                ],
            ];

            $item['manages'] = [];
            $item['editStatus'] = [
                'isMe' => true,
                'canDelete' => (bool) $commentAppend->can_delete,
                'canEdit' => false,
                'isPluginEditor' => (bool) $commentAppend->is_plugin_editor,
                'editorUrl' => PluginHelper::fresnsPluginUrlByUnikey($commentAppend->editor_unikey),
            ];
            $item['interaction']['postCreatorLikeStatus'] = InteractionUtility::checkUserLike(InteractionUtility::TYPE_COMMENT, $comment->id, $post->user_id);
            $item['followType'] = null;
            $postData = self::getPost($post, $langTag);
            $item['pid'] = $postData['pid'];
            $item['post'] = $postData;

            $commentData = array_merge($commentInfo, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);
            CacheHelper::put($commentData, $cacheKey, ['fresnsComments', 'fresnsCommentData'], null, $cacheTime);
        }

        $contentHandle = self::handleCommentContent($comment, $commentData, $type, $authUserId);
        $commentData['content'] = $contentHandle['content'];
        $commentData['isBrief'] = $contentHandle['isBrief'];

        // location
        if ($comment->map_id && $authUserLng && $authUserLat) {
            $postLng = $comment->map_longitude;
            $postLat = $comment->map_latitude;

            $commentData['location']['distance'] = LbsUtility::getDistanceWithUnit($langTag, $postLng, $postLat, $authUserLng, $authUserLat);
        }

        // creator
        if ($comment->is_anonymous) {
            $commentData['creator'] = InteractionHelper::fresnsUserAnonymousProfile();
            $commentData['creator']['isPostCreator'] = false;
        } else {
            $commentCreator = PrimaryHelper::fresnsModelByFsid('user', $commentData['creator']['uid']);

            $userService = new UserService;
            $commentData['creator'] = $userService->userData($commentCreator, $langTag, $timezone);
            $commentData['creator']['isPostCreator'] = $commentData['creator']['uid'] == $commentData['post']['creator']['uid'] ? true : false;
        }

        // whether to output sub-level comments
        $previewConfig = ConfigHelper::fresnsConfigByItemKey('preview_sub_comments');
        if ($outputSubComments && $previewConfig != 0) {
            $commentData['subComments'] = self::getSubComments($comment->id, $previewConfig, $langTag);
        }

        // auth user is creator
        $isMe = $comment->user_id == $authUserId ? true : false;
        if ($isMe) {
            $commentData['editStatus']['canEdit'] = PermissionUtility::checkContentIsCanEdit('comment', $comment->created_at, $comment->is_sticky, $comment->digest_state, $langTag, $timezone);
        } else {
            $commentData['extendBtn'] = [
                'status' => false,
                'type' => null,
                'default' => [
                    'name' => null,
                    'style' => null,
                    'url' => null,
                ],
                'active' => [
                    'name' => null,
                    'style' => null,
                    'url' => null,
                ],
            ];
            $commentData['editStatus'] = [
                'isMe' => false,
                'canDelete' => false,
                'canEdit' => false,
                'isPluginEditor' => false,
                'editorUrl' => null,
            ];
        }

        // manages
        $groupId = PrimaryHelper::fresnsGroupIdByGid($commentData['post']['group']['gid'] ?? null);
        $commentData['manages'] = ExtendUtility::getManageExtensions('comment', $langTag, $authUserId, $groupId);

        // interaction
        $interactionConfig = InteractionHelper::fresnsCommentInteraction($langTag);
        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_COMMENT, $comment->id, $authUserId);
        $interArr['interaction'] = array_merge($interactionConfig, $interactionStatus, $commentData['interaction']);

        if (! $isPreviewPost) {
            $commentData['post'] = null;
        }

        $data = array_merge($commentData, $interArr);

        $newCommentData = self::handleCommentCount($comment, $data);
        $result = self::handleCommentDate($newCommentData, $timezone, $langTag);

        return $result;
    }

    // handle comment content
    public static function handleCommentContent(Comment $comment, array $commentData, string $type, ?int $authUserId = null)
    {
        $cacheKey = "fresns_api_comment_{$commentData['cid']}_{$type}_content";

        $contentData = Cache::get($cacheKey);

        if (empty($contentData)) {
            $isBrief = false;
            $commentContent = ContentUtility::replaceBlockWords('content', $commentData['content']);

            $briefLength = ConfigHelper::fresnsConfigByItemKey('comment_editor_brief_length');

            if ($type == 'list' && $commentData['contentLength'] > $briefLength) {
                $commentContent = Str::limit($commentContent, $briefLength);
                $isBrief = true;
            }

            $commentContent = ContentUtility::handleAndReplaceAll($commentContent, $comment->is_markdown, $comment->user_id, Mention::TYPE_COMMENT, $comment->id);

            $contentData = [
                'content' => $commentContent,
                'isBrief' => $isBrief,
            ];

            CacheHelper::put($contentData, $cacheKey, ['fresnsComments', 'fresnsCommentData']);
        }

        $authUid = PrimaryHelper::fresnsModelById('user', $authUserId)?->uid;

        if (! $commentData['isCommentPublic'] && $commentData['post']['creator']['uid'] != $authUid) {
            return $contentData['content'] = null;
        }

        $contentFormat = \request()->header('contentFormat');
        if ($contentFormat == 'html') {
            $contentData['content'] = $comment->is_markdown ? Str::markdown($contentData['content']) : nl2br($contentData['content']);
        }

        return $contentData;
    }

    // handle comment data count
    public static function handleCommentCount(?Comment $comment, ?array $commentData)
    {
        if (empty($comment) || empty($commentData)) {
            return $commentData;
        }

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'comment_liker_count',
            'comment_disliker_count',
            'comment_follower_count',
            'comment_blocker_count',
        ]);

        $commentData['likeCount'] = $configKeys['comment_liker_count'] ? $comment->like_count : null;
        $commentData['dislikeCount'] = $configKeys['comment_disliker_count'] ? $comment->dislike_count : null;
        $commentData['followCount'] = $configKeys['comment_follower_count'] ? $comment->follow_count : null;
        $commentData['blockCount'] = $configKeys['comment_blocker_count'] ? $comment->block_count : null;
        $commentData['commentCount'] = $comment->comment_count;
        $commentData['commentDigestCount'] = $comment->comment_digest_count;
        $commentData['commentLikeCount'] = $configKeys['comment_liker_count'] ? $comment->comment_like_count : null;
        $commentData['commentDislikeCount'] = $configKeys['comment_disliker_count'] ? $comment->comment_dislike_count : null;
        $commentData['commentFollowCount'] = $configKeys['comment_follower_count'] ? $comment->comment_follow_count : null;
        $commentData['commentBlockCount'] = $configKeys['comment_blocker_count'] ? $comment->comment_block_count : null;

        return $commentData;
    }

    // handle comment data date
    public static function handleCommentDate(?array $commentData, string $timezone, string $langTag)
    {
        if (empty($commentData)) {
            return $commentData;
        }

        $commentData['createTime'] = DateHelper::fresnsFormatDateTime($commentData['createTime'], $timezone, $langTag);
        $commentData['createTimeFormat'] = DateHelper::fresnsFormatTime($commentData['createTimeFormat'], $langTag);
        $commentData['editTime'] = DateHelper::fresnsFormatDateTime($commentData['editTime'], $timezone, $langTag);
        $commentData['editTimeFormat'] = DateHelper::fresnsFormatTime($commentData['editTimeFormat'], $langTag);
        $commentData['latestCommentTime'] = DateHelper::fresnsFormatDateTime($commentData['latestCommentTime'], $timezone, $langTag);
        $commentData['latestCommentTimeFormat'] = DateHelper::fresnsFormatTime($commentData['latestCommentTimeFormat'], $langTag);

        $commentData['interaction']['followExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($commentData['interaction']['followExpiryDateTime'], $timezone, $langTag);

        return $commentData;
    }

    // get sub comments
    public static function getSubComments(int $commentId, int $limit, string $langTag)
    {
        $cacheKey = "fresns_api_comment_{$commentId}_sub_comments_{$langTag}";

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        // get cache
        $commentList = Cache::get($cacheKey);

        if (empty($commentList)) {
            $previewSortConfig = ConfigHelper::fresnsConfigByItemKey('preview_sub_comment_sort');

            $commentQuery = Comment::where('top_parent_id', $commentId);

            if ($previewSortConfig == 'like') {
                $commentQuery->orderByDesc('like_count');
            }

            if ($previewSortConfig == 'oldest') {
                $commentQuery->oldest();
            }

            if ($previewSortConfig == 'latest') {
                $commentQuery->latest();
            }

            $comments = $commentQuery->limit($limit)->get();

            $service = new CommentService();
            $timezone = ConfigHelper::fresnsConfigDefaultTimezone();

            $commentList = [];
            foreach ($comments as $comment) {
                $commentList[] = $service->commentData($comment, 'list', $langTag, $timezone, false);
            }

            CacheHelper::put($commentList, $cacheKey, ['fresnsComments', 'fresnsCommentData'], 10, now()->addMinutes(10));
        }

        return $commentList;
    }

    // get post
    public static function getPost(Post $post, string $langTag)
    {
        $timezone = ConfigHelper::fresnsConfigDefaultTimezone();
        $postService = new PostService;

        return $postService->postData($post, 'list', $langTag, $timezone, false);
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
        $info['editorUrl'] = PluginHelper::fresnsPluginUrlByUnikey($log->editor_unikey);
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

        $info['creator'] = InteractionHelper::fresnsUserAnonymousProfile();
        if (! $log->is_anonymous) {
            $userService = new UserService;

            $item['creator'] = $userService->userData($log->creator, $langTag, $timezone);
        }

        $info['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_POST_LOG, $log->id, $langTag);
        $info['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_POST_LOG, $log->id, $langTag);
        $info['extends'] = ExtendUtility::getContentExtends(ExtendUsage::TYPE_POST_LOG, $log->id, $langTag);
        $info['files'] = FileHelper::fresnsFileInfoListByTableColumn('post_logs', 'id', $log->id);

        $fileCount['images'] = collect($info['files']['images'])->count();
        $fileCount['videos'] = collect($info['files']['videos'])->count();
        $fileCount['audios'] = collect($info['files']['audios'])->count();
        $fileCount['documents'] = collect($info['files']['documents'])->count();
        $info['fileCount'] = $fileCount;

        return $info;
    }
}
