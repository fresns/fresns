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
use App\Helpers\PluginHelper;
use App\Helpers\PrimaryHelper;
use App\Models\ArchiveUsage;
use App\Models\Comment;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\Mention;
use App\Models\OperationUsage;
use App\Models\Post;
use App\Models\PostLog;
use App\Models\UserLike;
use App\Utilities\ContentUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractionUtility;
use App\Utilities\LbsUtility;
use App\Utilities\PermissionUtility;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PostService
{
    // $type = list or detail
    public function postData(?Post $post, string $type, string $langTag, string $timezone, bool $isPreview, ?int $authUserId = null, ?int $authUserMapId = null, ?string $authUserLng = null, ?string $authUserLat = null)
    {
        if (! $post) {
            return null;
        }

        $cacheKey = "fresns_api_post_{$post->pid}_{$langTag}";

        $postData = Cache::get($cacheKey);

        if (empty($postData)) {
            $postInfo = $post->getPostInfo($langTag);
            $postInfo['title'] = ContentUtility::replaceBlockWords('content', $postInfo['title']);

            // extend list
            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_POST, $post->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_POST, $post->id, $langTag);
            $item['extends'] = ExtendUtility::getContentExtends(ExtendUsage::TYPE_POST, $post->id, $langTag);

            // file
            $item['files'] = FileHelper::fresnsFileInfoListByTableColumn('posts', 'id', $post->id);

            $fileCount['images'] = collect($item['files']['images'])->count();
            $fileCount['videos'] = collect($item['files']['videos'])->count();
            $fileCount['audios'] = collect($item['files']['audios'])->count();
            $fileCount['documents'] = collect($item['files']['documents'])->count();
            $item['fileCount'] = $fileCount;

            $timezone = $timezone ?: ConfigHelper::fresnsConfigDefaultTimezone();

            // group
            $groupService = new GroupService;
            $item['group'] = $groupService->groupData($post->group, $langTag, $timezone);

            // hashtags
            $item['hashtags'] = [];
            if ($post->hashtags->isNotEmpty()) {
                $hashtagService = new HashtagService;

                foreach ($post->hashtags as $hashtag) {
                    $hashtagItem[] = $hashtagService->hashtagData($hashtag, $langTag, $timezone);
                }
                $item['hashtags'] = $hashtagItem;
            }

            // creator
            $userService = new UserService;
            $item['creator'] = $userService->userData($post->creator, $langTag, $timezone);

            $item['previewComments'] = [];
            $item['previewLikeUsers'] = [];
            $item['manages'] = [];

            $editStatus['isMe'] = true;
            $editStatus['canDelete'] = (bool) $post->postAppend->can_delete;
            $editStatus['canEdit'] = PermissionUtility::checkContentIsCanEdit('post', $post->created_at, $post->sticky_state, $post->digest_state, $langTag, $timezone);
            $editStatus['isPluginEditor'] = (bool) $post->postAppend->is_plugin_editor;
            $editStatus['editorUrl'] = ! empty($post->postAppend->editor_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($post->postAppend->editor_unikey) : null;
            $item['editStatus'] = $editStatus;

            $item['commentHidden'] = false;
            $item['followType'] = null;

            $postData = array_merge($postInfo, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);
            CacheHelper::put($postData, $cacheKey, ['fresnsPosts', 'fresnsPostData'], null, $cacheTime);
        }

        $contentHandle = self::handlePostContent($post, $postData, $type, $authUserId);
        $postData['content'] = $contentHandle['content'];
        $postData['isBrief'] = $contentHandle['isBrief'];
        $postData['isAllow'] = $contentHandle['isAllow'];

        // location
        if ($post->map_id && $authUserLng && $authUserLat) {
            $postLng = $post->map_longitude;
            $postLat = $post->map_latitude;

            $postData['location']['distance'] = LbsUtility::getDistanceWithUnit($langTag, $postLng, $postLat, $authUserLng, $authUserLat);
        }

        // group
        if ($post->group_id) {
            $groupDateLimit = GroupService::getGroupContentDateLimit($post->group_id, $authUserId);
            if ($groupDateLimit) {
                $postTime = strtotime($post->created_at);
                $dateLimit = strtotime($groupDateLimit);

                if ($postTime > $dateLimit) {
                    $postData['content'] = null;
                    $postData['isBrief'] = true;
                    $postData['files'] = [
                        'images' => [],
                        'videos' => [],
                        'audios' => [],
                        'documents' => [],
                    ];
                }
            }
        }

        // creator
        if ($post->is_anonymous) {
            $postData['creator'] = InteractionHelper::fresnsUserAnonymousProfile();
        }

        // get preview configs
        $previewConfig = ConfigHelper::fresnsConfigByItemKeys([
            'preview_post_like_users',
            'preview_post_comments',
            'preview_post_comment_require',
        ]);

        // get preview like users
        if ($type == 'list' && $isPreview && $previewConfig['preview_post_like_users'] != 0) {
            $postData['previewLikeUsers'] = self::getPreviewLikeUsers($post, $previewConfig['preview_post_like_users'], $langTag);
        }

        // get preview comments
        if ($type == 'list' && $isPreview && $previewConfig['preview_post_comments'] != 0) {
            $postData['previewComments'] = self::getPreviewComments($post, $previewConfig['preview_post_comments'], $langTag);
        }

        // auth user is creator
        if ($post->user_id == $authUserId) {
            $postData['editStatus']['canEdit'] = PermissionUtility::checkContentIsCanEdit('post', $post->created_at, $post->sticky_state, $post->digest_state, $langTag, $timezone);
        } else {
            $postData['editStatus'] = [
                'isMe' => false,
                'canDelete' => false,
                'canEdit' => false,
                'isPluginEditor' => false,
                'editorUrl' => null,
            ];
        }

        // manages
        $groupId = PrimaryHelper::fresnsGroupIdByGid($postData['group']['gid'] ?? null);
        $postData['manages'] = ExtendUtility::getManageExtensions('post', $langTag, $authUserId, $groupId);

        // interaction
        $interactionConfig = InteractionHelper::fresnsPostInteraction($langTag);
        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_POST, $post->id, $authUserId);
        $postData['interaction'] = array_merge($interactionConfig, $interactionStatus);

        $commentVisibilityRule = ConfigHelper::fresnsConfigByItemKey('comment_visibility_rule');
        if ($commentVisibilityRule > 0) {
            $visibilityTime = $post->created_at->addDay($commentVisibilityRule);

            $postData['commentHidden'] = $visibilityTime->lt(now());
        }

        $newPostData = self::handlePostCount($post, $postData);
        $result = self::handlePostDate($post, $newPostData, $timezone, $langTag);

        return $result;
    }

    // handle post content
    public static function handlePostContent(Post $post, array $postData, string $type, ?int $authUserId = null)
    {
        $cacheKey = "fresns_api_post_{$postData['pid']}_{$type}_content";

        $contentData = Cache::get($cacheKey);

        if (empty($contentData)) {
            $isBrief = false;
            $postContent = ContentUtility::replaceBlockWords('content', $postData['content']);

            $briefLength = ConfigHelper::fresnsConfigByItemKey('post_editor_brief_length');

            if ($type == 'list' && $postData['contentLength'] > $briefLength) {
                $postContent = Str::limit($postContent, $briefLength);
                $isBrief = true;
            }

            $postContent = ContentUtility::handleAndReplaceAll($postContent, $post->is_markdown, $post->user_id, Mention::TYPE_POST, $post->id);

            $contentData = [
                'content' => $postContent,
                'isBrief' => $isBrief,
                'isAllow' => $postData['isAllow'],
            ];

            CacheHelper::put($contentData, $cacheKey, ['fresnsPosts', 'fresnsPostData']);
        }

        if ($contentData['isAllow']) {
            return $contentData;
        }

        $contentData['isAllow'] = true;
        $checkPostAllow = PermissionUtility::checkPostAllow($post->id, $authUserId);

        if (empty($authUserId) || ! $checkPostAllow) {
            $allowProportion = $postData['allowProportion'] / 100;
            $allowLength = intval($postData['contentLength'] * $allowProportion);

            $contentData['isAllow'] = false;
            $contentData['content'] = Str::limit($contentData['content'], $allowLength);
        }

        return $contentData;
    }

    // handle post data count
    public static function handlePostCount(?Post $post, ?array $postData)
    {
        if (empty($post) || empty($postData)) {
            return $postData;
        }

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'post_liker_count',
            'post_disliker_count',
            'post_follower_count',
            'post_blocker_count',
            'comment_liker_count',
            'comment_disliker_count',
            'comment_follower_count',
            'comment_blocker_count',
        ]);

        $postData['likeCount'] = $configKeys['post_liker_count'] ? $post->like_count : null;
        $postData['dislikeCount'] = $configKeys['post_disliker_count'] ? $post->dislike_count : null;
        $postData['followCount'] = $configKeys['post_follower_count'] ? $post->follow_count : null;
        $postData['blockCount'] = $configKeys['post_blocker_count'] ? $post->block_count : null;
        $postData['commentCount'] = $post->comment_count;
        $postData['commentDigestCount'] = $post->comment_digest_count;
        $postData['commentLikeCount'] = $configKeys['comment_liker_count'] ? $post->comment_like_count : null;
        $postData['commentDislikeCount'] = $configKeys['comment_disliker_count'] ? $post->comment_dislike_count : null;
        $postData['commentFollowCount'] = $configKeys['comment_follower_count'] ? $post->comment_follow_count : null;
        $postData['commentBlockCount'] = $configKeys['comment_blocker_count'] ? $post->comment_block_count : null;

        return $postData;
    }

    // handle post data date
    public static function handlePostDate(?Post $post, ?array $postData, string $timezone, string $langTag)
    {
        if (empty($postData)) {
            return $postData;
        }

        $postData['createTime'] = DateHelper::fresnsFormatDateTime($postData['createTime'], $timezone, $langTag);
        $postData['createTimeFormat'] = DateHelper::fresnsFormatTime($postData['createTimeFormat'], $langTag);
        $postData['editTime'] = DateHelper::fresnsFormatDateTime($postData['editTime'], $timezone, $langTag);
        $postData['editTimeFormat'] = DateHelper::fresnsFormatTime($postData['editTimeFormat'], $langTag);
        $postData['latestCommentTime'] = DateHelper::fresnsFormatDateTime($post->latest_comment_at, $timezone, $langTag);
        $postData['latestCommentTimeFormat'] = DateHelper::fresnsFormatTime($post->latest_comment_at, $langTag);

        $postData['interaction']['followExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($postData['interaction']['followExpiryDateTime'], $timezone, $langTag);

        return $postData;
    }

    // get preview like users
    public static function getPreviewLikeUsers(Post $post, int $limit, string $langTag)
    {
        $cacheKey = "fresns_api_post_{$post->id}_preview_like_users_{$langTag}";

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        // get cache
        $userList = Cache::get($cacheKey);

        if (empty($userList)) {
            $userLikes = UserLike::with('creator')
                ->markType(UserLike::MARK_TYPE_LIKE)
                ->type(UserLike::TYPE_POST)
                ->where('like_id', $post->id)
                ->limit($limit)
                ->oldest()
                ->get();

            $service = new UserService();
            $timezone = ConfigHelper::fresnsConfigDefaultTimezone();

            $userList = [];
            foreach ($userLikes as $like) {
                $userList[] = $service->userData($like->creator, $langTag, $timezone);
            }

            CacheHelper::put($userList, $cacheKey, ['fresnsPosts', 'fresnsPostData', 'fresnsUsers', 'fresnsUserData'], 10, now()->addMinutes(10));
        }

        $userCount = count($userList);
        if ($userCount > 0 && $userCount < $post->like_count) {
            CacheHelper::forgetFresnsMultilingual("fresns_api_post_{$post->id}_preview_like_users");
        }

        return $userList;
    }

    // get preview comments
    public static function getPreviewComments(Post $post, int $limit, string $langTag)
    {
        $cacheKey = "fresns_api_post_{$post->id}_preview_comments_{$langTag}";

        $previewConfig = ConfigHelper::fresnsConfigByItemKeys([
            'preview_post_comment_sort',
            'preview_post_comment_require',
        ]);

        if ($previewConfig['preview_post_comment_sort'] == 'like' && $post->comment_like_count < $previewConfig['preview_post_comment_require']) {
            return [];
        }

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        // get cache
        $commentList = Cache::get($cacheKey);

        if (empty($commentList)) {
            $commentQuery = Comment::where('post_id', $post->id)->where('top_parent_id', 0)->limit($limit);

            if ($previewConfig['preview_post_comment_sort'] == 'like') {
                $commentQuery->orderByDesc('like_count');
            }

            if ($previewConfig['preview_post_comment_sort'] == 'comment') {
                $commentQuery->where('comment_count', '>', $previewConfig['preview_post_comment_require'])->orderByDesc('comment_count');
            }

            if ($previewConfig['preview_post_comment_sort'] == 'oldest') {
                $commentQuery->oldest();
            }

            if ($previewConfig['preview_post_comment_sort'] == 'latest') {
                $commentQuery->latest();
            }

            $comments = $commentQuery->get();

            $service = new CommentService();
            $timezone = ConfigHelper::fresnsConfigDefaultTimezone();

            $commentList = [];
            foreach ($comments as $comment) {
                $commentList[] = $service->commentData($comment, 'list', $langTag, $timezone, false);
            }

            CacheHelper::put($commentList, $cacheKey, ['fresnsPosts', 'fresnsPostData', 'fresnsComments', 'fresnsCommentData'], 10, now()->addMinutes(10));
        }

        return $commentList;
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

        $info['creator'] = InteractionHelper::fresnsUserAnonymousProfile();
        if (! $log->is_anonymous) {
            $userService = new UserService;

            $item['creator'] = $userService->userData($log->creator, $langTag, $timezone);
        }

        if ($group) {
            $groupItem[] = $group?->getGroupInfo($langTag);

            $info['group'] = $groupItem;
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
