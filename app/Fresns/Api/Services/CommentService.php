<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
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
use App\Utilities\ArrUtility;
use App\Utilities\ContentUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\GeneralUtility;
use App\Utilities\InteractionUtility;
use App\Utilities\PermissionUtility;
use App\Utilities\SubscribeUtility;
use Illuminate\Support\Str;

class CommentService
{
    // $type = list or detail
    public function commentData(?Comment $comment, string $type, string $langTag, ?string $timezone = null, ?int $authUserId = null, ?int $authUserMapId = null, ?string $authUserLong = null, ?string $authUserLat = null, ?bool $outputSubComments = false, ?bool $outputReplyToPost = false, ?bool $outputReplyToComment = false, ?bool $whetherToFilter = true)
    {
        if (! $comment) {
            return null;
        }

        $cacheKey = "fresns_api_comment_{$comment->cid}_{$langTag}";
        $cacheTag = 'fresnsComments';

        $commentData = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($commentData)) {
            $commentAppend = $comment->commentAppend;
            $post = $comment->post;
            $postAppend = $comment->postAppend;

            $commentInfo = $comment->getCommentInfo($langTag);

            $item['isCommentPrivate'] = (bool) $postAppend->is_comment_private;

            // extend list
            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_COMMENT, $comment->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_COMMENT, $comment->id, $langTag);
            $item['extends'] = ExtendUtility::getContentExtends(ExtendUsage::TYPE_COMMENT, $comment->id, $langTag);

            // file
            $item['files'] = FileHelper::fresnsFileInfoListByTableColumn('comments', 'id', $comment->id);

            // hashtags
            $item['hashtags'] = [];
            if ($comment->hashtags->isNotEmpty()) {
                $hashtagService = new HashtagService;

                foreach ($comment->hashtags as $hashtag) {
                    $hashtagItem[] = $hashtagService->hashtagData($hashtag, $langTag, $timezone);
                }

                $item['hashtags'] = $hashtagItem;
            }

            // author
            $userService = new UserService;
            $item['author'] = $userService->userData($comment->author, 'list', $langTag, $timezone);
            $item['author']['isPostAuthor'] = $comment->user_id == $post?->user_id ? true : false;

            $item['subComments'] = [];

            $item['extendButton'] = [
                'status' => (bool) $postAppend->is_comment_btn,
                'type' => $commentAppend->is_change_btn ? 'active' : 'default',
                'default' => [
                    'name' => $post?->id ? LanguageHelper::fresnsLanguageByTableId('post_appends', 'comment_btn_name', $post?->id, $langTag) : null,
                    'style' => $postAppend->comment_btn_style,
                    'url' => PluginHelper::fresnsPluginUrlByFskey($postAppend->comment_btn_plugin_fskey),
                ],
                'active' => [
                    'name' => $commentAppend->btn_name_key ? ConfigHelper::fresnsConfigByItemKey($commentAppend->btn_name_key, $langTag) : null,
                    'style' => $commentAppend->btn_style,
                    'url' => PluginHelper::fresnsPluginUrlByFskey($postAppend->comment_btn_plugin_fskey),
                ],
            ];

            $item['manages'] = [];
            $item['editControls'] = [
                'isMe' => true,
                'canDelete' => (bool) $commentAppend->can_delete,
                'canEdit' => false,
                'isPluginEditor' => (bool) $commentAppend->is_plugin_editor,
                'editorUrl' => PluginHelper::fresnsPluginUrlByFskey($commentAppend->editor_fskey),
            ];
            $item['interaction']['postAuthorLikeStatus'] = InteractionUtility::checkUserLike(InteractionUtility::TYPE_COMMENT, $comment->id, $post?->user_id);

            // reply to post
            $postData = self::getReplyToPost($post, $langTag);
            $item['replyToPost'] = $postData;

            // reply to comment
            $item['replyToComment'] = null;
            if ($comment->top_parent_id != $comment->parent_id) {
                $item['replyToComment'] = self::getReplyToComment($comment?->parentComment, $langTag);
            }

            $item['followType'] = null;

            $commentData = array_merge($commentInfo, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);
            CacheHelper::put($commentData, $cacheKey, $cacheTag, null, $cacheTime);
        }

        $contentHandle = self::handleCommentContent($comment, $commentData, $type, $authUserId);
        $commentData['content'] = $contentHandle['content'];
        $commentData['isBrief'] = $contentHandle['isBrief'];
        $commentData['isCommentPrivate'] = $contentHandle['isCommentPrivate'];
        $commentData['extends'] = $contentHandle['extends'];
        $commentData['files'] = $contentHandle['files'];

        // archives
        if ($comment->user_id != $authUserId && $commentData['archives']) {
            $archives = [];
            foreach ($commentData['archives'] as $archive) {
                $item = $archive;
                $item['value'] = $archive['isPrivate'] ? null : $archive['value'];

                $archives[] = $item;
            }

            $commentData['archives'] = $archives;
        }

        // location
        $isLbs = $commentData['location']['isLbs'];
        if ($isLbs && $authUserLong && $authUserLat) {
            $commentData['location']['distance'] = GeneralUtility::distanceOfLocation(
                $langTag,
                $commentData['location']['longitude'],
                $commentData['location']['latitude'],
                $authUserLong,
                $authUserLat,
                $commentData['location']['mapId'],
                $authUserMapId,
            );
        }

        // author
        if ($comment->is_anonymous) {
            $commentData['author'] = InteractionHelper::fresnsUserSubstitutionProfile();
            $commentData['author']['isPostAuthor'] = false;
        } elseif (! ($commentData['author']['uid'] ?? null)) {
            $commentData['author'] = InteractionHelper::fresnsUserSubstitutionProfile('deactivate');
            $commentData['author']['isPostAuthor'] = false;
        } else {
            $commentAuthor = PrimaryHelper::fresnsModelByFsid('user', $commentData['author']['uid']);

            $userService = new UserService;
            $commentData['author'] = $userService->userData($commentAuthor, 'list', $langTag, $timezone);
            $authorUid = $commentData['replyToPost']['author']['uid'] ?? null;
            $commentData['author']['isPostAuthor'] = $commentData['author']['uid'] == $authorUid ? true : false;
        }

        // whether to output sub-level comments
        $previewConfig = ConfigHelper::fresnsConfigByItemKey('preview_sub_comments');
        if ($outputSubComments && $previewConfig != 0 && ! $contentHandle['isCommentPrivate']) {
            $commentData['subComments'] = self::getSubComments($comment->id, $previewConfig, $langTag);
        }

        // auth user is author
        $isMe = $comment->user_id == $authUserId ? true : false;
        if ($isMe) {
            $commentData['editControls']['canDelete'] = $commentData['editControls']['canDelete'] ? PermissionUtility::checkContentIsCanDelete('comment', $comment->digest_state, $comment->is_sticky) : false;
            $commentData['editControls']['canEdit'] = PermissionUtility::checkContentIsCanEdit('comment', $comment->created_at, $comment->digest_state, $comment->is_sticky, $timezone, $langTag);
        } else {
            $commentData['extendButton'] = [
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
            $commentData['editControls'] = [
                'isMe' => false,
                'canDelete' => false,
                'canEdit' => false,
                'isPluginEditor' => false,
                'editorUrl' => null,
            ];
        }

        // manages
        $groupId = PrimaryHelper::fresnsGroupIdByGid($commentData['replyToPost']['group']['gid'] ?? null);
        $commentData['manages'] = ExtendUtility::getManageExtensions('comment', $langTag, $authUserId, $groupId);

        // interaction
        $interactionConfig = InteractionHelper::fresnsCommentInteraction($langTag);
        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_COMMENT, $comment->id, $authUserId);
        $interArr['interaction'] = array_merge($interactionConfig, $interactionStatus, $commentData['interaction']);

        SubscribeUtility::notifyViewContent('comment', $comment->cid, $type, $authUserId);

        $replyToPid = $commentData['replyToPost']['pid'] ?? null;
        if (! $outputReplyToPost) {
            $commentData['replyToPost'] = null;
            $commentData['replyToPost']['pid'] = $replyToPid;
        }
        if (! $outputReplyToComment) {
            $commentData['replyToComment'] = null;
        }

        $data = array_merge($commentData, $interArr);

        $newCommentData = self::handleCommentCount($comment, $data);
        $result = self::handleCommentDate($newCommentData, $timezone, $langTag);

        // filter
        $filterKeys = \request()->get('whitelistKeys') ?? \request()->get('blacklistKeys');
        $filter = [
            'type' => \request()->get('whitelistKeys') ? 'whitelist' : 'blacklist',
            'keys' => array_filter(explode(',', $filterKeys)),
        ];

        if (empty($filter['keys']) || ! $whetherToFilter) {
            return $result;
        }

        $currentRouteName = \request()->route()->getName();
        $filterRouteList = [
            'api.comment.list',
            'api.comment.detail',
            'api.comment.follow',
            'api.comment.nearby',
        ];

        if (! in_array($currentRouteName, $filterRouteList)) {
            return $result;
        }

        return ArrUtility::filter($result, $filter['type'], $filter['keys']);
    }

    // handle comment content
    public static function handleCommentContent(Comment $comment, array $commentData, string $type, ?int $authUserId = null)
    {
        $cacheKey = "fresns_api_comment_{$commentData['cid']}_{$type}_content";
        $cacheTag = 'fresnsComments';

        $contentData = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($contentData)) {
            $isBrief = false;
            $commentContent = ContentUtility::replaceBlockWords('content', $commentData['content']);

            $briefLength = ConfigHelper::fresnsConfigByItemKey('comment_editor_brief_length');

            if ($type == 'list' && $commentData['contentLength'] > $briefLength) {
                $commentContent = Str::limit($commentContent, $briefLength);

                $commentContent = strip_tags($commentContent);

                $isBrief = true;
            }

            $commentContent = ContentUtility::handleAndReplaceAll($commentContent, $comment->is_markdown, $comment->user_id, Mention::TYPE_COMMENT, $comment->id);

            $contentData = [
                'content' => $commentContent,
                'isBrief' => $isBrief,
                'isCommentPrivate' => $commentData['isCommentPrivate'],
            ];

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType();
            CacheHelper::put($contentData, $cacheKey, $cacheTag, null, $cacheTime);
        }

        // files
        $contentData['files'] = $commentData['files'];
        if ($type == 'detail') {
            $fidArr = ContentUtility::extractFile($contentData['content']);

            if ($fidArr) {
                $commentDetailContent = ContentUtility::replaceFile($contentData['content']);

                $files = [
                    'images' => ArrUtility::forget($commentData['files']['images'], 'fid', $fidArr),
                    'videos' => ArrUtility::forget($commentData['files']['videos'], 'fid', $fidArr),
                    'audios' => ArrUtility::forget($commentData['files']['audios'], 'fid', $fidArr),
                    'documents' => ArrUtility::forget($commentData['files']['documents'], 'fid', $fidArr),
                ];

                $contentData['content'] = $commentDetailContent;
                $contentData['files'] = $files;
            }
        }

        // extends
        $contentData['extends'] = $commentData['extends'];

        // isCommentPrivate
        if ($commentData['isCommentPrivate']) {
            $authUid = \request()->header('X-Fresns-Uid');

            if ($commentData['author']['uid'] != $authUid && $commentData['replyToPost']['author']['uid'] != $authUid) {
                $contentData['content'] = null;
                $contentData['extends'] = [
                    'textBox' => [],
                    'infoBox' => [],
                    'interactionBox' => [],
                ];
                $contentData['files'] = [
                    'images' => [],
                    'videos' => [],
                    'audios' => [],
                    'documents' => [],
                ];

                return $contentData;
            }
        }

        $contentFormat = \request()->header('X-Fresns-Client-Content-Format');
        if ($contentFormat == 'html') {
            $contentData['content'] = $comment->is_markdown ? Str::markdown($contentData['content']) : nl2br($contentData['content']);
        }

        $contentData['isCommentPrivate'] = false;

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

        $commentData['viewCount'] = $comment->view_count;
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
    public static function handleCommentDate(?array $commentData, ?string $timezone = null, ?string $langTag = null)
    {
        if (empty($commentData)) {
            return $commentData;
        }

        $commentData['createdDatetime'] = DateHelper::fresnsFormatDateTime($commentData['createdDatetime'], $timezone, $langTag);
        $commentData['createdTimeAgo'] = DateHelper::fresnsHumanReadableTime($commentData['createdTimeAgo'], $langTag);
        $commentData['editedDatetime'] = DateHelper::fresnsFormatDateTime($commentData['editedDatetime'], $timezone, $langTag);
        $commentData['editedTimeAgo'] = DateHelper::fresnsHumanReadableTime($commentData['editedTimeAgo'], $langTag);
        $commentData['latestCommentDatetime'] = DateHelper::fresnsFormatDateTime($commentData['latestCommentDatetime'], $timezone, $langTag);
        $commentData['latestCommentTimeAgo'] = DateHelper::fresnsHumanReadableTime($commentData['latestCommentTimeAgo'], $langTag);

        $commentData['interaction']['followExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($commentData['interaction']['followExpiryDateTime'], $timezone, $langTag);

        return $commentData;
    }

    // get sub comments
    public static function getSubComments(int $commentId, int $limit, string $langTag)
    {
        $cacheKey = "fresns_api_comment_{$commentId}_sub_comments_{$langTag}";
        $cacheTag = 'fresnsComments';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        // get cache
        $commentList = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($commentList)) {
            $previewSortConfig = ConfigHelper::fresnsConfigByItemKey('preview_sub_comment_sort');

            $commentQuery = Comment::with(['author'])->has('author')->where('top_parent_id', $commentId)->isEnabled();

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

            $commentService = new CommentService;

            $commentConfig = [
                'userId' => null,
                'mapId' => null,
                'longitude' => null,
                'latitude' => null,
                'outputSubComments' => false,
                'outputReplyToPost' => false,
                'outputReplyToComment' => false,
                'whetherToFilter' => false,
            ];

            $commentList = [];
            foreach ($comments as $comment) {
                $commentList[] = $commentService->commentData(
                    $comment,
                    'list',
                    $langTag,
                    null,
                    $commentConfig['userId'],
                    $commentConfig['mapId'],
                    $commentConfig['longitude'],
                    $commentConfig['latitude'],
                    $commentConfig['outputSubComments'],
                    $commentConfig['outputReplyToPost'],
                    $commentConfig['outputReplyToComment'],
                    $commentConfig['whetherToFilter'],
                );
            }

            CacheHelper::put($commentList, $cacheKey, $cacheTag, 10, now()->addMinutes(10));
        }

        return $commentList;
    }

    // get reply to post
    public static function getReplyToPost(?Post $post, string $langTag)
    {
        if (! $post) {
            return null;
        }

        $postService = new PostService;

        $postConfig = [
            'userId' => null,
            'mapId' => null,
            'longitude' => null,
            'latitude' => null,
            'isPreview' => false,
            'whetherToFilter' => false,
        ];

        $postData = $postService->postData(
            $post,
            'list',
            $langTag,
            null,
            $postConfig['userId'],
            $postConfig['mapId'],
            $postConfig['longitude'],
            $postConfig['latitude'],
            $postConfig['isPreview'],
            $postConfig['whetherToFilter'],
        );
        $postData['quotedPost'] = null;

        return $postData;
    }

    // get reply to comment
    public static function getReplyToComment(?Comment $comment, string $langTag)
    {
        if (! $comment) {
            return null;
        }

        $commentService = new CommentService;

        $commentConfig = [
            'userId' => null,
            'mapId' => null,
            'longitude' => null,
            'latitude' => null,
            'outputSubComments' => false,
            'outputReplyToPost' => false,
            'outputReplyToComment' => false,
            'whetherToFilter' => false,
        ];

        $commentData = $commentService->commentData(
            $comment,
            'list',
            $langTag,
            null,
            $commentConfig['userId'],
            $commentConfig['mapId'],
            $commentConfig['longitude'],
            $commentConfig['latitude'],
            $commentConfig['outputSubComments'],
            $commentConfig['outputReplyToPost'],
            $commentConfig['outputReplyToComment'],
            $commentConfig['whetherToFilter'],
        );

        return $commentData;
    }

    // comment log data
    // $type = list or detail
    public function commentLogData(CommentLog $log, string $type, string $langTag, ?string $timezone = null, ?int $authUserId = null)
    {
        $comment = $log?->comment;
        $parentComment = $log?->parentComment;
        $post = $log?->post;

        $info['id'] = $log->id;
        $info['cid'] = $comment?->cid;
        $info['pid'] = $post?->pid;
        $info['parentCid'] = $parentComment?->cid;
        $info['isPluginEditor'] = (bool) $log->is_plugin_editor;
        $info['editorFskey'] = $log->editor_fskey;
        $info['editorUrl'] = PluginHelper::fresnsPluginUrlByFskey($log->editor_fskey);
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
        $info['readJson'] = null;
        $info['userListJson'] = null;
        $info['commentBtnJson'] = null;
        $info['state'] = $log->state;
        $info['reason'] = $log->reason;

        $info['author'] = InteractionHelper::fresnsUserSubstitutionProfile();
        if (! $log->is_anonymous) {
            $userService = new UserService;

            $item['author'] = $userService->userData($log->author, 'list', $langTag, $timezone);
        }

        $info['files'] = FileHelper::fresnsFileInfoListByTableColumn('comment_logs', 'id', $log->id);
        $info['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_COMMENT_LOG, $log->id, $langTag);
        $info['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_COMMENT_LOG, $log->id, $langTag);
        $info['extends'] = ExtendUtility::getContentExtends(ExtendUsage::TYPE_COMMENT_LOG, $log->id, $langTag);

        // archives
        if ($log->user_id != $authUserId && $info['archives']) {
            $archives = [];
            foreach ($info['archives'] as $archive) {
                $item = $archive;
                $item['value'] = $archive['isPrivate'] ? null : $archive['value'];

                $archives[] = $item;
            }

            $info['archives'] = $archives;
        }

        return $info;
    }
}
