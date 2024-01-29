<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\CacheHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Comment;
use App\Models\Domain;
use App\Models\DomainLink;
use App\Models\DomainLinkUsage;
use App\Models\Geotag;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\Mention;
use App\Models\Notification;
use App\Models\Post;
use App\Models\UserBlock;
use App\Models\UserFollow;
use App\Models\UserLike;
use App\Models\UserStat;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class InteractionUtility
{
    const TYPE_USER = 1;
    const TYPE_GROUP = 2;
    const TYPE_HASHTAG = 3;
    const TYPE_GEOTAG = 4;
    const TYPE_POST = 5;
    const TYPE_COMMENT = 6;

    // get interaction status
    public static function getInteractionStatus(int $type, int $markId, ?int $userId = null, ?bool $checkPostAuthorLikeStatus = false): array
    {
        $status = [
            'likeStatus' => false,
            'dislikeStatus' => false,
            'followStatus' => false,
            'blockStatus' => false,
            'note' => null,
        ];

        $postAuthorLikeStatus = false;
        if ($type == InteractionUtility::TYPE_COMMENT && $checkPostAuthorLikeStatus) {
            $comment = PrimaryHelper::fresnsModelById('comment', $markId);
            $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_COMMENT, $markId, $comment?->post?->user_id);

            $postAuthorLikeStatus = $interactionStatus['likeStatus'];
        }

        if (empty($userId)) {
            if ($type == InteractionUtility::TYPE_USER) {
                $status['followMeStatus'] = false;
                $status['blockMeStatus'] = false;
            }

            if ($type == InteractionUtility::TYPE_USER || $type == InteractionUtility::TYPE_GROUP) {
                $status['followExpired'] = false;
                $status['followExpiryDateTime'] = null;
            }

            if ($type == InteractionUtility::TYPE_COMMENT) {
                $status['postAuthorLikeStatus'] = $postAuthorLikeStatus;
            }

            return $status;
        }

        $cacheKey = "fresns_interaction_status_{$type}_{$markId}_{$userId}";
        $cacheTag = 'fresnsUsers';

        $status = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($status)) {
            $userLike = UserLike::where('user_id', $userId)->type($type)->where('like_id', $markId)->first();
            $userFollow = UserFollow::where('user_id', $userId)->type($type)->where('follow_id', $markId)->first();

            $status['likeStatus'] = $userLike?->mark_type === UserLike::MARK_TYPE_LIKE;
            $status['dislikeStatus'] = $userLike?->mark_type === UserLike::MARK_TYPE_DISLIKE;
            $status['followStatus'] = $userFollow?->mark_type === UserFollow::MARK_TYPE_FOLLOW;
            $status['blockStatus'] = $userFollow?->mark_type === UserFollow::MARK_TYPE_BLOCK;
            $status['note'] = $userFollow?->user_note;

            if ($type == InteractionUtility::TYPE_USER) {
                $userFollowMe = UserFollow::where('user_id', $markId)->type($type)->where('follow_id', $userId)->first();

                $status['followMeStatus'] = $userFollowMe?->mark_type === UserFollow::MARK_TYPE_FOLLOW;
                $status['blockMeStatus'] = $userFollowMe?->mark_type === UserFollow::MARK_TYPE_BLOCK;
            }

            if ($type == InteractionUtility::TYPE_USER || $type == InteractionUtility::TYPE_GROUP) {
                $status['followExpired'] = $userFollow?->expired_at?->isPast();
                $status['followExpiryDateTime'] = $userFollow?->expired_at;
            }

            CacheHelper::put($status, $cacheKey, $cacheTag);
        }

        if ($type == InteractionUtility::TYPE_COMMENT) {
            $status['postAuthorLikeStatus'] = $postAuthorLikeStatus;
        }

        return $status;
    }

    // mark interaction
    public static function markUserLike(int $userId, int $likeType, int $likeId): void
    {
        $userLike = UserLike::withTrashed()
            ->where('user_id', $userId)
            ->type($likeType)
            ->where('like_id', $likeId)
            ->first();

        if ($userLike?->trashed() || empty($userLike)) {
            if ($userLike?->trashed() && $userLike->mark_type == UserLike::MARK_TYPE_LIKE) {
                // trashed data, mark type=like
                $userLike->restore();

                InteractionUtility::markStats($userId, 'like', $likeType, $likeId, 'increment');
            } elseif ($userLike?->trashed() && $userLike->mark_type == UserLike::MARK_TYPE_DISLIKE) {
                // trashed data, mark type=dislike
                $userLike->restore();

                $userLike->update([
                    'mark_type' => UserLike::MARK_TYPE_LIKE,
                ]);

                InteractionUtility::markStats($userId, 'like', $likeType, $likeId, 'increment');
                InteractionUtility::markStats($userId, 'dislike', $likeType, $likeId, 'decrement');
            } else {
                // like null
                UserLike::updateOrCreate([
                    'user_id' => $userId,
                    'like_type' => $likeType,
                    'like_id' => $likeId,
                ], [
                    'mark_type' => UserLike::MARK_TYPE_LIKE,
                ]);

                InteractionUtility::markStats($userId, 'like', $likeType, $likeId, 'increment');
            }

            // send notification
            InteractionUtility::sendMarkNotification(Notification::TYPE_LIKE, $userId, $likeType, $likeId);
        } else {
            if ($userLike->mark_type == UserLike::MARK_TYPE_LIKE) {
                // documented, mark type=like
                $userLike->delete();

                InteractionUtility::markStats($userId, 'like', $likeType, $likeId, 'decrement');
            } else {
                // documented, mark type=dislike
                $userLike->update([
                    'mark_type' => UserLike::MARK_TYPE_LIKE,
                ]);

                InteractionUtility::markStats($userId, 'like', $likeType, $likeId, 'increment');
                InteractionUtility::markStats($userId, 'dislike', $likeType, $likeId, 'decrement');
            }
        }
    }

    public static function markUserDislike(int $userId, int $dislikeType, int $dislikeId): void
    {
        $userDislike = UserLike::withTrashed()
            ->where('user_id', $userId)
            ->type($dislikeType)
            ->where('like_id', $dislikeId)
            ->first();

        if ($userDislike?->trashed() || empty($userDislike)) {
            if ($userDislike?->trashed() && $userDislike->mark_type == UserLike::MARK_TYPE_DISLIKE) {
                // trashed data, mark type=dislike
                $userDislike->restore();

                InteractionUtility::markStats($userId, 'dislike', $dislikeType, $dislikeId, 'increment');
            } elseif ($userDislike?->trashed() && $userDislike->mark_type == UserLike::MARK_TYPE_LIKE) {
                // trashed data, mark type=like
                $userDislike->restore();

                $userDislike->update([
                    'mark_type' => UserLike::MARK_TYPE_DISLIKE,
                ]);

                InteractionUtility::markStats($userId, 'dislike', $dislikeType, $dislikeId, 'increment');
                InteractionUtility::markStats($userId, 'like', $dislikeType, $dislikeId, 'decrement');
            } else {
                // dislike null
                UserLike::updateOrCreate([
                    'user_id' => $userId,
                    'like_type' => $dislikeType,
                    'like_id' => $dislikeId,
                ], [
                    'mark_type' => UserLike::MARK_TYPE_DISLIKE,
                ]);

                InteractionUtility::markStats($userId, 'dislike', $dislikeType, $dislikeId, 'increment');
            }

            // send notification
            InteractionUtility::sendMarkNotification(Notification::TYPE_DISLIKE, $userId, $dislikeType, $dislikeId);
        } else {
            if ($userDislike->mark_type == UserLike::MARK_TYPE_DISLIKE) {
                // documented, mark type=dislike
                $userDislike->delete();

                InteractionUtility::markStats($userId, 'dislike', $dislikeType, $dislikeId, 'decrement');
            } else {
                // documented, mark type=like
                $userDislike->update([
                    'mark_type' => UserLike::MARK_TYPE_DISLIKE,
                ]);

                InteractionUtility::markStats($userId, 'dislike', $dislikeType, $dislikeId, 'increment');
                InteractionUtility::markStats($userId, 'like', $dislikeType, $dislikeId, 'decrement');
            }
        }
    }

    public static function markUserFollow(int $userId, int $followType, int $followId): void
    {
        $userFollow = UserFollow::withTrashed()
            ->where('user_id', $userId)
            ->type($followType)
            ->where('follow_id', $followId)
            ->first();

        if ($userFollow?->trashed() || empty($userFollow)) {
            if ($userFollow?->trashed() && $userFollow->mark_type == UserFollow::MARK_TYPE_FOLLOW) {
                // trashed data, mark type=follow
                $userFollow->restore();

                InteractionUtility::markStats($userId, 'follow', $followType, $followId, 'increment');
            } elseif ($userFollow?->trashed() && $userFollow->mark_type == UserFollow::MARK_TYPE_BLOCK) {
                // trashed data, mark type=block
                $userFollow->restore();

                $userFollow->update([
                    'mark_type' => UserFollow::MARK_TYPE_FOLLOW,
                ]);

                InteractionUtility::markStats($userId, 'follow', $followType, $followId, 'increment');
                InteractionUtility::markStats($userId, 'block', $followType, $followId, 'decrement');
            } else {
                // follow null
                UserFollow::updateOrCreate([
                    'user_id' => $userId,
                    'follow_type' => $followType,
                    'follow_id' => $followId,
                ], [
                    'mark_type' => UserFollow::MARK_TYPE_FOLLOW,
                ]);

                InteractionUtility::markStats($userId, 'follow', $followType, $followId, 'increment');
            }

            // send notification
            InteractionUtility::sendMarkNotification(Notification::TYPE_FOLLOW, $userId, $followType, $followId);
        } else {
            if ($userFollow->mark_type == UserFollow::MARK_TYPE_FOLLOW) {
                // documented, mark type=follow
                $userFollow->delete();

                InteractionUtility::markStats($userId, 'follow', $followType, $followId, 'decrement');
            } else {
                // documented, mark type=block
                $userFollow->update([
                    'mark_type' => UserFollow::MARK_TYPE_BLOCK,
                ]);

                InteractionUtility::markStats($userId, 'follow', $followType, $followId, 'increment');
                InteractionUtility::markStats($userId, 'block', $followType, $followId, 'decrement');
            }
        }
    }

    public static function markUserBlock(int $userId, int $blockType, int $blockId): void
    {
        $userBlock = UserFollow::withTrashed()
            ->where('user_id', $userId)
            ->type($blockType)
            ->where('follow_id', $blockId)
            ->first();

        if ($userBlock?->trashed() || empty($userBlock)) {
            if ($userBlock?->trashed() && $userBlock->mark_type == UserFollow::MARK_TYPE_BLOCK) {
                // trashed data, mark type=block
                $userBlock->restore();

                InteractionUtility::markStats($userId, 'block', $blockType, $blockId, 'increment');
            } elseif ($userBlock?->trashed() && $userBlock->mark_type == UserFollow::MARK_TYPE_FOLLOW) {
                // trashed data, mark type=follow
                $userBlock->restore();

                $userBlock->update([
                    'mark_type' => UserFollow::MARK_TYPE_BLOCK,
                ]);

                InteractionUtility::markStats($userId, 'block', $blockType, $blockId, 'increment');
                InteractionUtility::markStats($userId, 'follow', $blockType, $blockId, 'decrement');
            } else {
                // dislike null
                UserFollow::updateOrCreate([
                    'user_id' => $userId,
                    'like_type' => $blockType,
                    'like_id' => $blockId,
                ], [
                    'mark_type' => UserFollow::MARK_TYPE_BLOCK,
                ]);

                InteractionUtility::markStats($userId, 'block', $blockType, $blockId, 'increment');
            }

            // send notification
            InteractionUtility::sendMarkNotification(Notification::TYPE_BLOCK, $userId, $blockType, $blockId);
        } else {
            if ($userBlock->mark_type == UserFollow::MARK_TYPE_BLOCK) {
                // documented, mark type=block
                $userBlock->delete();

                InteractionUtility::markStats($userId, 'block', $blockType, $blockId, 'decrement');
            } else {
                // documented, mark type=follow
                $userBlock->update([
                    'mark_type' => UserFollow::MARK_TYPE_BLOCK,
                ]);

                InteractionUtility::markStats($userId, 'block', $blockType, $blockId, 'increment');
                InteractionUtility::markStats($userId, 'follow', $blockType, $blockId, 'decrement');
            }
        }
    }

    // mark content sticky
    public static function markContentSticky(string $type, int $id, int $stickyState): void
    {
        switch ($type) {
            case 'post':
                $post = Post::where('id', $id)->first();
                $post->update([
                    'sticky_state' => $stickyState,
                ]);

                CacheHelper::forgetFresnsMultilingual('fresns_web_sticky_posts', 'fresnsWeb');

                if ($stickyState == Post::STICKY_GROUP && $post->group_id) {
                    $group = PrimaryHelper::fresnsModelById('group', $post->group_id);

                    CacheHelper::forgetFresnsMultilingual("fresns_web_group_{$group?->gid}_sticky_posts", 'fresnsWeb');
                }
                break;

            case 'comment':
                $comment = Comment::where('id', $id)->first();

                if ($stickyState) {
                    $comment->update([
                        'is_sticky' => true,
                    ]);
                } else {
                    $comment->update([
                        'is_sticky' => false,
                    ]);
                }

                $post = PrimaryHelper::fresnsModelById('post', $comment->post_id);
                CacheHelper::forgetFresnsMultilingual("fresns_web_post_{$post?->pid}_sticky_comments", 'fresnsWeb');
                break;
        }
    }

    // mark content digest
    public static function markContentDigest(string $type, int $id, int $digestState): void
    {
        $digestStatus = match ($digestState) {
            default => null,
            1 => 'no',
            2 => 'yes',
            3 => 'yes',
        };

        switch ($type) {
            case 'post':
                $post = Post::where('id', $id)->first();

                if ($post->digest_state == Post::DIGEST_NO && $digestStatus == 'yes') {
                    $post->update([
                        'digest_state' => $digestState,
                    ]);

                    InteractionUtility::digestStats('post', $post->id, 'increment');
                } elseif ($post->digest_state != Post::DIGEST_NO && $digestStatus == 'no') {
                    $post->update([
                        'digest_state' => $digestState,
                    ]);

                    InteractionUtility::digestStats('post', $post->id, 'decrement');
                } else {
                    $post->update([
                        'digest_state' => $digestState,
                    ]);
                }
                break;

            case 'comment':
                $comment = Comment::where('id', $id)->first();

                if ($comment->digest_state == Comment::DIGEST_NO && $digestStatus == 'yes') {
                    $comment->update([
                        'digest_state' => $digestState,
                    ]);

                    InteractionUtility::digestStats('comment', $comment->id, 'increment');
                } elseif ($comment->digest_state != Comment::DIGEST_NO && $digestStatus == 'no') {
                    $comment->update([
                        'digest_state' => $digestState,
                    ]);

                    InteractionUtility::digestStats('comment', $comment->id, 'decrement');
                } else {
                    $comment->update([
                        'digest_state' => $digestState,
                    ]);
                }
                break;
        }
    }

    /**
     * It increments or decrements the stats of the user, group, hashtag, post, or comment that was
     * interacted with.
     *
     * @param int userId The user who is performing the action.
     * @param string markType The type of interaction action(like, dislike, follow, block).
     * @param int contentType 1 = user, 2 = group, 3 = hashtag, 4 = geotag, 5 = post, 6 = comment
     * @param int contentId The id of the user, group, hashtag, geotag, post, or comment that is being marked.
     * @param string actionType increment or decrement
     */
    public static function markStats(int $userId, string $markType, int $contentType, int $contentId, string $actionType): void
    {
        if (! in_array($actionType, ['increment', 'decrement'])) {
            return;
        }

        if (! in_array($markType, ['like', 'dislike', 'follow', 'block'])) {
            return;
        }

        $userState = UserStat::where('user_id', $userId)->first();

        switch ($contentType) {
            case InteractionUtility::TYPE_USER:
                $userMeState = UserStat::where('user_id', $contentId)->first();

                if ($actionType == 'increment') {
                    $userState?->increment("{$markType}_user_count");
                    $userMeState?->increment("{$markType}_me_count");

                    return;
                }

                $userStateCount = $userState?->{"{$markType}_user_count"} ?? 0;
                if ($userStateCount > 0) {
                    $userState->decrement("{$markType}_user_count");
                }

                $userMeStateCount = $userMeState?->{"{$markType}_me_count"} ?? 0;
                if ($userMeStateCount > 0) {
                    $userMeState->decrement("{$markType}_me_count");
                }
                break;

            case InteractionUtility::TYPE_GROUP:
                $groupState = Group::where('id', $contentId)->first();

                if ($actionType == 'increment') {
                    $userState?->increment("{$markType}_group_count");
                    $groupState?->increment("{$markType}_count");

                    return;
                }

                $userStateCount = $userState?->{"{$markType}_group_count"} ?? 0;
                if ($userStateCount > 0) {
                    $userState->decrement("{$markType}_group_count");
                }

                $groupStateCount = $groupState?->{"{$markType}_count"} ?? 0;
                if ($groupStateCount > 0) {
                    $groupState->decrement("{$markType}_count");
                }
                break;

            case InteractionUtility::TYPE_HASHTAG:
                $hashtagState = Hashtag::where('id', $contentId)->first();

                if ($actionType == 'increment') {
                    $userState?->increment("{$markType}_hashtag_count");
                    $hashtagState?->increment("{$markType}_count");

                    return;
                }

                $userStateCount = $userState?->{"{$markType}_hashtag_count"} ?? 0;
                if ($userStateCount > 0) {
                    $userState->decrement("{$markType}_hashtag_count");
                }

                $hashtagStateCount = $hashtagState?->{"{$markType}_count"} ?? 0;
                if ($hashtagStateCount > 0) {
                    $hashtagState->decrement("{$markType}_count");
                }
                break;

            case InteractionUtility::TYPE_GEOTAG:
                $geotagState = Geotag::where('id', $contentId)->first();

                if ($actionType == 'increment') {
                    $userState?->increment("{$markType}_geotag_count");
                    $geotagState?->increment("{$markType}_count");

                    return;
                }

                $userStateCount = $userState?->{"{$markType}_geotag_count"} ?? 0;
                if ($userStateCount > 0) {
                    $userState->decrement("{$markType}_geotag_count");
                }

                $geotagStateCount = $geotagState?->{"{$markType}_count"} ?? 0;
                if ($geotagStateCount > 0) {
                    $geotagState->decrement("{$markType}_count");
                }
                break;

            case InteractionUtility::TYPE_POST:
                $post = Post::where('id', $contentId)->first();
                $postAuthorState = UserStat::where('user_id', $post?->user_id)->first();

                if ($actionType == 'increment') {
                    $userState?->increment("{$markType}_post_count");
                    $post?->increment("{$markType}_count");
                    $postAuthorState?->increment("post_{$markType}_count");

                    return;
                }

                $userStateCount = $userState?->{"{$markType}_post_count"} ?? 0;
                if ($userStateCount > 0) {
                    $userState?->decrement("{$markType}_post_count");
                }

                $postStateCount = $post?->{"{$markType}_count"} ?? 0;
                if ($postStateCount > 0) {
                    $post?->decrement("{$markType}_count");
                }

                $postAuthorStateCount = $postAuthorState?->{"post_{$markType}_count"} ?? 0;
                if ($postAuthorStateCount > 0) {
                    $postAuthorState?->decrement("post_{$markType}_count");
                }
                break;

            case InteractionUtility::TYPE_COMMENT:
                $comment = Comment::where('id', $contentId)->first();
                $commentAuthorState = UserStat::where('user_id', $comment?->user_id)->first();
                $commentPost = Post::where('id', $comment?->post_id)->first();

                if ($actionType == 'increment') {
                    $userState->increment("{$markType}_comment_count");
                    $comment?->increment("{$markType}_count");
                    $commentAuthorState?->increment("comment_{$markType}_count");
                    $commentPost?->increment("comment_{$markType}_count");

                    // parent comment
                    if ($comment?->parent_id) {
                        InteractionUtility::parentCommentStats($comment->parent_id, 'increment', "comment_{$markType}_count");
                    }

                    return;
                }

                $userStateCount = $userState?->{"{$markType}_comment_count"} ?? 0;
                if ($userStateCount > 0) {
                    $userState?->decrement("{$markType}_comment_count");
                }

                $commentStateCount = $comment?->{"{$markType}_count"} ?? 0;
                if ($commentStateCount > 0) {
                    $comment?->decrement("{$markType}_count");
                }

                $commentAuthorStateCount = $commentAuthorState?->{"comment_{$markType}_count"} ?? 0;
                if ($commentAuthorStateCount > 0) {
                    $commentAuthorState?->decrement("comment_{$markType}_count");
                }

                $commentPostCount = $commentPost?->{"comment_{$markType}_count"} ?? 0;
                if ($commentPostCount > 0) {
                    $commentPost?->decrement("comment_{$markType}_count");
                }

                // parent comment
                if ($comment?->parent_id) {
                    InteractionUtility::parentCommentStats($comment->parent_id, 'decrement', "comment_{$markType}_count");
                }
                break;
        }
    }

    /**
     * It increments or decrements the stats of a post or comment.
     *
     * @param string type post or comment
     * @param int id The id of the post or comment
     * @param string actionType increment or decrement
     */
    public static function publishStats(string $type, int $id, string $actionType): void
    {
        if (! in_array($actionType, ['increment', 'decrement'])) {
            return;
        }

        if (! in_array($type, ['post', 'comment'])) {
            return;
        }

        switch ($type) {
            case 'post':
                $post = Post::with('hashtags')->where('id', $id)->first();
                $userState = UserStat::where('user_id', $post?->user_id)->first();
                $group = Group::where('id', $post?->group_id)->first();

                $linkIds = DomainLinkUsage::type(DomainLinkUsage::TYPE_POST)->where('usage_id', $post?->id)->pluck('link_id')->toArray();
                $domainIds = DomainLink::whereIn('id', $linkIds)->pluck('domain_id')->toArray();
                $hashtagIds = $post?->hashtags?->pluck('id');

                if ($actionType == 'increment') {
                    if ($post?->parent_id) {
                        Post::where('id', $post->parent_id)->increment('post_count');
                    }

                    $userState?->increment('post_publish_count');

                    $group?->increment('post_count');

                    DomainLink::whereIn('id', $linkIds)->increment('post_count');
                    Domain::whereIn('id', $domainIds)->increment('post_count');
                    Hashtag::whereIn('id', $hashtagIds)->increment('post_count');
                } else {
                    if ($post?->parent_id) {
                        Post::where('id', $post->parent_id)->decrement('post_count');
                    }

                    $userStateCount = $userState?->{'post_publish_count'} ?? 0;
                    if ($userStateCount > 0) {
                        $userState?->decrement('post_publish_count');
                    }

                    $groupPostCount = $group?->post_count ?? 0;
                    if ($groupPostCount > 0) {
                        $group?->decrement('post_count');
                    }

                    DomainLink::whereIn('id', $linkIds)->where('post_count', '>', 0)->decrement('post_count');
                    Domain::whereIn('id', $domainIds)->where('post_count', '>', 0)->decrement('post_count');
                    Hashtag::whereIn('id', $hashtagIds)->where('post_count', '>', 0)->decrement('post_count');
                }
                break;

            case 'comment':
                $comment = Comment::with('hashtags')->where('id', $id)->first();
                $userState = UserStat::where('user_id', $comment?->user_id)->first();
                $post = Post::where('id', $comment?->post_id)->first();
                $group = Group::where('id', $post?->group_id)->first();

                $linkIds = DomainLinkUsage::type(DomainLinkUsage::TYPE_COMMENT)->where('usage_id', $comment?->id)->pluck('link_id')->toArray();
                $domainIds = DomainLink::whereIn('id', $linkIds)->pluck('domain_id')->toArray();
                $hashtagIds = $comment?->hashtags?->pluck('id');

                if ($actionType == 'increment') {
                    $userState?->increment('comment_publish_count');

                    $post?->increment('comment_count');
                    $group?->increment('comment_count');

                    DomainLink::whereIn('id', $linkIds)->increment('comment_count');
                    Domain::whereIn('id', $domainIds)->increment('comment_count');
                    Hashtag::whereIn('id', $hashtagIds)->increment('comment_count');
                } else {
                    $userStateCount = $userState?->{'comment_publish_count'} ?? 0;
                    if ($userStateCount > 0) {
                        $userState?->decrement('comment_publish_count');
                    }

                    $postCommentCount = $post?->comment_count ?? 0;
                    if ($postCommentCount > 0) {
                        $post?->decrement('comment_count');
                    }

                    $groupCommentCount = $group?->comment_count ?? 0;
                    if ($groupCommentCount > 0) {
                        $group?->decrement('comment_count');
                    }

                    DomainLink::whereIn('id', $linkIds)->where('comment_count', '>', 0)->decrement('comment_count');
                    Domain::whereIn('id', $domainIds)->where('comment_count', '>', 0)->decrement('comment_count');
                    Hashtag::whereIn('id', $hashtagIds)->where('comment_count', '>', 0)->decrement('comment_count');
                }

                if ($comment?->parent_id) {
                    InteractionUtility::parentCommentStats($comment->parent_id, $actionType, 'comment_count');
                }
                break;
        }
    }

    public static function editStats(string $type, int $id, string $actionType): void
    {
        if (! in_array($actionType, ['increment', 'decrement'])) {
            return;
        }

        if (! in_array($type, ['post', 'comment'])) {
            return;
        }

        switch ($type) {
            case 'post':
                $content = Post::with('hashtags')->where('id', $id)->first();
                $typeNumber = DomainLinkUsage::TYPE_POST;
                break;

            case 'comment':
                $content = Comment::with('hashtags')->where('id', $id)->first();
                $typeNumber = DomainLinkUsage::TYPE_COMMENT;
                break;
        }

        $group = Group::where('id', $content?->group_id)->first();
        $linkIds = DomainLinkUsage::type($typeNumber)->where('usage_id', $content?->id)->pluck('link_id')->toArray();
        $domainIds = DomainLink::whereIn('id', $linkIds)->pluck('domain_id')->toArray();
        $hashtagIds = $content->hashtags->pluck('id');

        if ($actionType == 'increment') {
            $group?->increment("{$type}_count");

            DomainLink::whereIn('id', $linkIds)->increment("{$type}_count");
            Domain::whereIn('id', $domainIds)->increment("{$type}_count");
            Hashtag::whereIn('id', $hashtagIds)->increment("{$type}_count");
        } else {
            $groupTypeCount = $group?->{"{$type}_count"} ?? 0;
            if ($groupTypeCount > 0) {
                $group?->decrement("{$type}_count");
            }

            DomainLink::whereIn('id', $linkIds)->where("{$type}_count", '>', 0)->decrement("{$type}_count");
            Domain::whereIn('id', $domainIds)->where("{$type}_count", '>', 0)->decrement("{$type}_count");
            Hashtag::whereIn('id', $hashtagIds)->where("{$type}_count", '>', 0)->decrement("{$type}_count");
        }
    }

    /**
     * It increments or decrements the digest count of a post or comment.
     *
     * @param string type post or comment
     * @param int id the id of the post or comment
     * @param string actionType increment or decrement
     */
    public static function digestStats(string $type, int $id, string $actionType): void
    {
        if (! in_array($actionType, ['increment', 'decrement'])) {
            return;
        }

        if (! in_array($type, ['post', 'comment'])) {
            return;
        }

        switch ($type) {
            case 'post':
                $post = Post::with('hashtags')->where('id', $id)->first();
                $userState = UserStat::where('user_id', $post?->user_id)->first();
                $group = Group::where('id', $post?->group_id)->first();
                $hashtagIds = $post?->hashtags->pluck('id')->toArray() ?? [];

                if ($actionType == 'increment') {
                    $userState?->increment('post_digest_count');
                    $group?->increment('post_digest_count');

                    Hashtag::whereIn('id', $hashtagIds)->increment('post_digest_count');
                } else {
                    $userStateCount = $userState?->{'post_digest_count'} ?? 0;
                    if ($userStateCount > 0) {
                        $userState?->decrement('post_digest_count');
                    }

                    $groupPostDigestCount = $group?->{'post_digest_count'} ?? 0;
                    if ($groupPostDigestCount > 0) {
                        $group?->decrement('post_digest_count');
                    }

                    Hashtag::whereIn('id', $hashtagIds)->where('post_digest_count', '>', 0)->decrement('post_digest_count');
                }
                break;

            case 'comment':
                $comment = Comment::with('hashtags')->where('id', $id)->first();
                $userState = UserStat::where('user_id', $comment?->user_id)->first();
                $post = Post::where('id', $comment?->post_id)->first();
                $group = Group::where('id', $comment?->group_id)->first();
                $hashtagIds = $comment?->hashtags->pluck('id')->toArray() ?? [];

                if ($actionType == 'increment') {
                    $userState?->increment('comment_digest_count');
                    $post?->increment('comment_digest_count');
                    $group?->increment('comment_digest_count');

                    Hashtag::whereIn('id', $hashtagIds)->increment('comment_digest_count');
                } else {
                    $userStateCount = $userState?->{'comment_digest_count'} ?? 0;
                    if ($userStateCount > 0) {
                        $userState?->decrement('comment_digest_count');
                    }

                    $groupCommentDigestCount = $group?->{'comment_digest_count'} ?? 0;
                    if ($groupCommentDigestCount > 0) {
                        $group?->decrement('comment_digest_count');
                    }

                    Hashtag::whereIn('id', $hashtagIds)->where('comment_digest_count', '>', 0)->decrement('comment_digest_count');
                }

                if ($comment?->parent_id) {
                    InteractionUtility::parentCommentStats($comment->parent_id, $actionType, 'comment_digest_count');
                }
                break;
        }

        $uid = match ($type) {
            'post' => PrimaryHelper::fresnsModelById('user', $post->user_id)->uid,
            'comment' => PrimaryHelper::fresnsModelById('user', $comment->user_id)->uid,
        };
        $actionObject = match ($type) {
            'post' => Notification::ACTION_OBJECT_POST,
            'comment' => Notification::ACTION_OBJECT_COMMENT,
        };
        $actionFsid = match ($type) {
            'post' => $post->pid,
            'comment' => $comment->cid,
        };

        $wordBody = [
            'uid' => $uid,
            'type' => Notification::TYPE_SYSTEM,
            'content' => null,
            'isMarkdown' => null,
            'isMultilingual' => null,
            'isAccessApp' => null,
            'appFskey' => null,
            'actionUid' => null,
            'actionIsAnonymous' => false,
            'actionType' => Notification::ACTION_TYPE_DIGEST,
            'actionObject' => $actionObject,
            'actionFsid' => $actionFsid,
            'contentFsid' => null,
        ];

        \FresnsCmdWord::plugin('Fresns')->sendNotification($wordBody);
    }

    protected static function parentCommentStats(int $parentId, string $actionType, string $tableColumn): void
    {
        if (! in_array($actionType, ['increment', 'decrement'])) {
            return;
        }

        $comment = Comment::where('id', $parentId)->first();

        if ($actionType == 'increment') {
            $comment?->increment($tableColumn);
        } else {
            $commentColumnCount = $comment?->$tableColumn ?? 0;
            if ($commentColumnCount > 0) {
                $comment?->decrement($tableColumn);
            }
        }

        // parent comment
        if ($comment?->parent_id) {
            InteractionUtility::parentCommentStats($comment->parent_id, $actionType, $tableColumn);
        }
    }

    // send mark notification
    public static function sendMarkNotification(int $notificationType, int $userId, int $markType, int $markId): void
    {
        $user = PrimaryHelper::fresnsModelById('user', $userId);

        $actionModel = match ($markType) {
            Notification::ACTION_OBJECT_USER => PrimaryHelper::fresnsModelById('user', $markId),
            Notification::ACTION_OBJECT_GROUP => PrimaryHelper::fresnsModelById('group', $markId),
            Notification::ACTION_OBJECT_HASHTAG => PrimaryHelper::fresnsModelById('hashtag', $markId),
            Notification::ACTION_OBJECT_POST => PrimaryHelper::fresnsModelById('post', $markId),
            Notification::ACTION_OBJECT_COMMENT => PrimaryHelper::fresnsModelById('comment', $markId),
        };
        $uid = match ($markType) {
            Notification::ACTION_OBJECT_USER => $actionModel->uid,
            Notification::ACTION_OBJECT_GROUP => PrimaryHelper::fresnsModelById('user', $actionModel?->user_id)?->uid,
            Notification::ACTION_OBJECT_HASHTAG => null,
            Notification::ACTION_OBJECT_POST => PrimaryHelper::fresnsModelById('user', $actionModel?->user_id)?->uid,
            Notification::ACTION_OBJECT_COMMENT => PrimaryHelper::fresnsModelById('user', $actionModel?->user_id)?->uid,
        };

        if (empty($uid)) {
            return;
        }

        $actionFsid = match ($markType) {
            Notification::ACTION_OBJECT_USER => $actionModel->uid,
            Notification::ACTION_OBJECT_GROUP => $actionModel->gid,
            Notification::ACTION_OBJECT_HASHTAG => $actionModel->hid,
            Notification::ACTION_OBJECT_POST => $actionModel->pid,
            Notification::ACTION_OBJECT_COMMENT => $actionModel->cid,
        };
        $actionType = match ($notificationType) {
            Notification::TYPE_LIKE => Notification::ACTION_TYPE_LIKE,
            Notification::TYPE_DISLIKE => Notification::ACTION_TYPE_DISLIKE,
            Notification::TYPE_FOLLOW => Notification::ACTION_TYPE_FOLLOW,
            Notification::TYPE_BLOCK => Notification::ACTION_TYPE_BLOCK,
        };

        $notificationWordBody = [
            'uid' => $uid,
            'type' => $notificationType,
            'content' => null,
            'isMarkdown' => null,
            'isMultilingual' => null,
            'isAccessApp' => null,
            'appFskey' => null,
            'actionUid' => $user->uid,
            'actionIsAnonymous' => $actionModel?->is_anonymous,
            'actionType' => $actionType,
            'actionObject' => $markType,
            'actionFsid' => $actionFsid,
            'contentFsid' => null,
        ];
        \FresnsCmdWord::plugin('Fresns')->sendNotification($notificationWordBody);
    }

    // send publish notification
    public static function sendPublishNotification(string $type, int $contentId): void
    {
        Queue::push(function () use ($type, $contentId) {
            $actionModel = match ($type) {
                'post' => PrimaryHelper::fresnsModelById('post', $contentId),
                'comment' => PrimaryHelper::fresnsModelById('comment', $contentId),
                default => null,
            };

            if (empty($actionModel)) {
                return;
            }

            $actionUser = PrimaryHelper::fresnsModelById('user', $actionModel->user_id);
            $actionObject = match ($type) {
                'post' => Notification::ACTION_OBJECT_POST,
                'comment' => Notification::ACTION_OBJECT_COMMENT,
            };
            $actionFsid = match ($type) {
                'post' => $actionModel->pid,
                'comment' => $actionModel->cid,
            };

            $typeNumber = match ($type) {
                'post' => Mention::TYPE_POST,
                'comment' => Mention::TYPE_COMMENT,
            };

            $mentions = Mention::where('user_id', $actionUser->id)
                ->where('mention_type', $typeNumber)
                ->where('mention_id', $actionModel->id)
                ->get();

            foreach ($mentions as $mention) {
                $mentionUser = PrimaryHelper::fresnsModelById('user', $mention->mention_user_id);

                $mentionWordBody = [
                    'uid' => $mentionUser->uid,
                    'type' => Notification::TYPE_MENTION,
                    'content' => Str::limit($actionModel->content),
                    'isMarkdown' => 0,
                    'isMultilingual' => 0,
                    'isAccessApp' => null,
                    'appFskey' => null,
                    'actionUid' => $actionUser->uid,
                    'actionIsAnonymous' => $actionModel?->is_anonymous,
                    'actionType' => Notification::ACTION_TYPE_PUBLISH,
                    'actionObject' => $actionObject,
                    'actionFsid' => $actionFsid,
                    'contentFsid' => null,
                ];

                try {
                    \FresnsCmdWord::plugin('Fresns')->sendNotification($mentionWordBody);
                } catch (\Exception $e) {
                    continue;
                }
            }

            if ($type == 'post' && $actionModel->parent_id) {
                $notifyPost = PrimaryHelper::fresnsModelById('post', $actionModel->parent_id);

                if ($notifyPost->user_id == $actionModel->user_id) {
                    return;
                }

                $notifyUser = PrimaryHelper::fresnsModelById('user', $notifyPost->user_id);

                $quoteWordBody = [
                    'uid' => $notifyUser->uid,
                    'type' => Notification::TYPE_QUOTE,
                    'content' => Str::limit($actionModel->content),
                    'isMarkdown' => 0,
                    'isMultilingual' => 0,
                    'isAccessApp' => null,
                    'appFskey' => null,
                    'actionUid' => $actionUser->uid,
                    'actionIsAnonymous' => $actionModel?->is_anonymous,
                    'actionType' => Notification::ACTION_TYPE_PUBLISH,
                    'actionObject' => Notification::ACTION_OBJECT_POST,
                    'actionFsid' => $actionFsid,
                    'contentFsid' => $notifyPost->pid,
                ];
                \FresnsCmdWord::plugin('Fresns')->sendNotification($quoteWordBody);
            }

            if ($type == 'comment') {
                $notifyUserId = null;
                $commentActionObject = null;
                $commentActionFsid = null;
                $contentFsid = $actionFsid;

                $parentComment = PrimaryHelper::fresnsModelById('comment', $actionModel->parent_id);
                if ($parentComment) {
                    if ($parentComment->user_id == $actionModel->user_id) {
                        return;
                    }

                    $notifyUserId = $parentComment->user_id;
                    $commentActionObject = Notification::ACTION_OBJECT_COMMENT;
                    $commentActionFsid = $parentComment->cid;
                }

                if (empty($notifyUserId)) {
                    $notifyPost = PrimaryHelper::fresnsModelById('post', $actionModel->post_id);
                    if ($notifyPost->user_id == $actionModel->user_id) {
                        return;
                    }

                    $notifyUserId = $notifyPost->user_id;
                    $commentActionObject = Notification::ACTION_OBJECT_POST;
                    $commentActionFsid = $notifyPost->pid;
                }

                $notifyUser = PrimaryHelper::fresnsModelById('user', $notifyUserId);

                $commentWordBody = [
                    'uid' => $notifyUser->uid,
                    'type' => Notification::TYPE_COMMENT,
                    'content' => Str::limit($actionModel->content),
                    'isMarkdown' => 0,
                    'isMultilingual' => 0,
                    'isAccessApp' => null,
                    'appFskey' => null,
                    'actionUid' => $actionUser->uid,
                    'actionIsAnonymous' => $actionModel?->is_anonymous,
                    'actionType' => Notification::ACTION_TYPE_PUBLISH,
                    'actionObject' => $commentActionObject,
                    'actionFsid' => $commentActionFsid,
                    'contentFsid' => $contentFsid,
                ];
                \FresnsCmdWord::plugin('Fresns')->sendNotification($commentWordBody);
            }
        });
    }

    // get follow id array
    public static function getFollowIdArr(int $type, ?int $userId = null): array
    {
        if (empty($userId)) {
            return [];
        }

        $cacheKey = "fresns_user_follow_{$type}_ids_by_{$userId}";
        $cacheTag = match ($type) {
            UserFollow::TYPE_USER => 'fresnsUsers',
            UserFollow::TYPE_GROUP => 'fresnsGroups',
            UserFollow::TYPE_HASHTAG => 'fresnsHashtags',
            UserFollow::TYPE_GEOTAG => 'fresnsGeotags',
            UserFollow::TYPE_POST => 'fresnsPosts',
            UserFollow::TYPE_COMMENT => 'fresnsComments',
        };

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        // get cache
        $followIds = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($followIds)) {
            $followIds = UserFollow::markType(UserFollow::MARK_TYPE_FOLLOW)->type($type)->where('user_id', $userId)->pluck('follow_id')->toArray();

            CacheHelper::put($followIds, $cacheKey, $cacheTag);
        }

        return $followIds;
    }

    // get block id array
    public static function getBlockIdArr(int $type, ?int $userId = null): array
    {
        if (empty($userId)) {
            return [];
        }

        $cacheKey = "fresns_block_{$type}_array_by_{$userId}";
        $cacheTag = match ($type) {
            UserBlock::TYPE_USER => 'fresnsUsers',
            UserBlock::TYPE_GROUP => 'fresnsGroups',
            UserBlock::TYPE_HASHTAG => 'fresnsHashtags',
            UserBlock::TYPE_POST => 'fresnsPosts',
            UserBlock::TYPE_COMMENT => 'fresnsComments',
        };

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        // get cache
        $blockIds = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($blockIds)) {
            switch ($type) {
                case UserBlock::TYPE_USER:
                    $myBlockUserIds = UserBlock::type(UserBlock::TYPE_USER)->where('user_id', $userId)->pluck('block_id')->toArray();
                    $blockMeUserIds = UserBlock::type(UserBlock::TYPE_USER)->where('block_id', $userId)->pluck('user_id')->toArray();

                    $allUserIds = array_unique(array_merge($myBlockUserIds, $blockMeUserIds));

                    $blockIds = array_values($allUserIds);
                    break;

                case UserBlock::TYPE_GROUP:
                    $blockIds = PermissionUtility::getPostFilterByGroupIds($userId);
                    break;

                default:
                    $blockArr = UserBlock::type($type)->where('user_id', $userId)->pluck('block_id')->toArray();

                    $blockIds = array_values($blockArr);
            }

            CacheHelper::put($blockIds, $cacheKey, $cacheTag);
        }

        return $blockIds;
    }

    // get private group id array
    public static function getPrivateGroupIdArr(): array
    {
        $cacheKey = 'fresns_group_private_ids';
        $cacheTag = 'fresnsGroups';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        $groupIdArr = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($groupIdArr)) {
            $groupIdArr = Group::where('privacy', Group::PRIVACY_PRIVATE)->pluck('id')->toArray();

            CacheHelper::put($groupIdArr, $cacheKey, $cacheTag);
        }

        return $groupIdArr;
    }

    // get follow type
    public static function getFollowType(int $creatorId, ?int $authUserId = null, ?int $groupId = null, ?array $hashtags = null): ?string
    {
        if (empty($authUserId)) {
            return null;
        }

        $checkFollowUser = UserFollow::where('user_id', $authUserId)
            ->markType(UserFollow::MARK_TYPE_FOLLOW)
            ->type(UserFollow::TYPE_USER)
            ->where('follow_id', $creatorId)
            ->first();
        if ($checkFollowUser) {
            return 'user';
        }

        if (empty($groupId) && empty($hashtags)) {
            return 'digest';
        }

        if ($groupId) {
            $checkFollowGroup = UserFollow::where('user_id', $authUserId)
                ->markType(UserFollow::MARK_TYPE_FOLLOW)
                ->type(UserFollow::TYPE_GROUP)
                ->where('follow_id', $groupId)
                ->first();

            if ($checkFollowGroup) {
                return 'group';
            }
        }

        if ($hashtags) {
            $hashtagIds = array_column($hashtags, 'id');

            $checkFollowHashtag = UserFollow::where('user_id', $authUserId)
                ->type(UserFollow::TYPE_HASHTAG)
                ->whereIn('follow_id', $hashtagIds)
                ->first();

            if ($checkFollowHashtag) {
                return 'hashtag';
            }
        }

        return null;
    }
}
