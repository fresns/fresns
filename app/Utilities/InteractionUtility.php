<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
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
            switch ($type) {
                case InteractionUtility::TYPE_USER:
                    $status['followMeStatus'] = false;
                    $status['blockMeStatus'] = false;
                    $status['followExpired'] = false;
                    $status['followExpiryDateTime'] = null;
                    break;

                case InteractionUtility::TYPE_GROUP:
                    $status['followExpired'] = false;
                    $status['followExpiryDateTime'] = null;
                    break;

                case InteractionUtility::TYPE_COMMENT:
                    $status['postAuthorLikeStatus'] = $postAuthorLikeStatus;
                    break;
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

            switch ($type) {
                case InteractionUtility::TYPE_USER:
                    $userFollowMe = UserFollow::where('user_id', $markId)->type($type)->where('follow_id', $userId)->first();

                    $status['followMeStatus'] = $userFollowMe?->mark_type === UserFollow::MARK_TYPE_FOLLOW;
                    $status['blockMeStatus'] = $userFollowMe?->mark_type === UserFollow::MARK_TYPE_BLOCK;
                    $status['followExpired'] = $userFollow?->expired_at?->isPast();
                    $status['followExpiryDateTime'] = $userFollow?->expired_at;
                    break;

                case InteractionUtility::TYPE_GROUP:
                    $status['followExpired'] = $userFollow?->expired_at?->isPast();
                    $status['followExpiryDateTime'] = $userFollow?->expired_at;
                    break;

                case InteractionUtility::TYPE_COMMENT:
                    $status['postAuthorLikeStatus'] = $postAuthorLikeStatus;
                    break;
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
        $userLike = UserLike::withTrashed()->where('user_id', $userId)->type($likeType)->where('like_id', $likeId)->first();

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
        $userDislike = UserLike::withTrashed()->where('user_id', $userId)->type($dislikeType)->where('like_id', $dislikeId)->first();

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
        $userFollow = UserFollow::withTrashed()->where('user_id', $userId)->type($followType)->where('follow_id', $followId)->first();

        $markType = 'follow'; // follow, unfollow

        if ($userFollow?->trashed() || empty($userFollow)) {
            // create
            if ($userFollow?->trashed() && $userFollow->mark_type == UserFollow::MARK_TYPE_FOLLOW) {
                // trashed data, mark type=follow
                $userFollow->restore();

                $markType = 'follow';

                InteractionUtility::markStats($userId, 'follow', $followType, $followId, 'increment');
            } elseif ($userFollow?->trashed() && $userFollow->mark_type == UserFollow::MARK_TYPE_BLOCK) {
                // trashed data, mark type=block
                $userFollow->restore();

                $userFollow->update([
                    'mark_type' => UserFollow::MARK_TYPE_FOLLOW,
                ]);

                $markType = 'follow';

                InteractionUtility::markStats($userId, 'follow', $followType, $followId, 'increment');
                InteractionUtility::markStats($userId, 'block', $followType, $followId, 'decrement');
            } else {
                // follow null
                $userFollow = UserFollow::updateOrCreate([
                    'user_id' => $userId,
                    'follow_type' => $followType,
                    'follow_id' => $followId,
                ], [
                    'mark_type' => UserFollow::MARK_TYPE_FOLLOW,
                ]);

                $markType = 'follow';

                InteractionUtility::markStats($userId, 'follow', $followType, $followId, 'increment');
            }

            // send notification
            InteractionUtility::sendMarkNotification(Notification::TYPE_FOLLOW, $userId, $followType, $followId);
        } else {
            if ($userFollow->mark_type == UserFollow::MARK_TYPE_FOLLOW) {
                // documented, mark type=follow
                $userFollow->delete();

                $markType = 'unfollow';

                InteractionUtility::markStats($userId, 'follow', $followType, $followId, 'decrement');
            } else {
                // documented, mark type=block
                $userFollow->update([
                    'mark_type' => UserFollow::MARK_TYPE_FOLLOW,
                ]);

                $markType = 'follow';

                InteractionUtility::markStats($userId, 'follow', $followType, $followId, 'increment');
                InteractionUtility::markStats($userId, 'block', $followType, $followId, 'decrement');
            }
        }

        $itFollow = UserFollow::where('user_id', $followId)->markType(UserFollow::MARK_TYPE_FOLLOW)->type($followType)->where('follow_id', $userId)->first();

        if (empty($itFollow) || $markType == 'unfollow') {
            $userFollow->update([
                'is_mutual' => false,
            ]);

            $itFollow?->update([
                'is_mutual' => false,
            ]);

            return;
        }

        $userFollow->update([
            'is_mutual' => true,
        ]);

        $itFollow->update([
            'is_mutual' => true,
        ]);
    }

    public static function markUserBlock(int $userId, int $blockType, int $blockId): void
    {
        $userBlock = UserFollow::withTrashed()->where('user_id', $userId)->type($blockType)->where('follow_id', $blockId)->first();

        $markType = 'block'; // block, unblock

        if ($userBlock?->trashed() || empty($userBlock)) {
            if ($userBlock?->trashed() && $userBlock->mark_type == UserFollow::MARK_TYPE_BLOCK) {
                // trashed data, mark type=block
                $userBlock->restore();

                $markType = 'block';

                InteractionUtility::markStats($userId, 'block', $blockType, $blockId, 'increment');
            } elseif ($userBlock?->trashed() && $userBlock->mark_type == UserFollow::MARK_TYPE_FOLLOW) {
                // trashed data, mark type=follow
                $userBlock->restore();

                $userBlock->update([
                    'mark_type' => UserFollow::MARK_TYPE_BLOCK,
                ]);

                $markType = 'block';

                InteractionUtility::markStats($userId, 'block', $blockType, $blockId, 'increment');
                InteractionUtility::markStats($userId, 'follow', $blockType, $blockId, 'decrement');
            } else {
                // dislike null
                $userBlock = UserFollow::updateOrCreate([
                    'user_id' => $userId,
                    'like_type' => $blockType,
                    'like_id' => $blockId,
                ], [
                    'mark_type' => UserFollow::MARK_TYPE_BLOCK,
                ]);

                $markType = 'block';

                InteractionUtility::markStats($userId, 'block', $blockType, $blockId, 'increment');
            }

            // send notification
            InteractionUtility::sendMarkNotification(Notification::TYPE_BLOCK, $userId, $blockType, $blockId);
        } else {
            if ($userBlock->mark_type == UserFollow::MARK_TYPE_BLOCK) {
                // documented, mark type=block
                $userBlock->delete();

                $markType = 'unblock';

                InteractionUtility::markStats($userId, 'block', $blockType, $blockId, 'decrement');
            } else {
                // documented, mark type=follow
                $userBlock->update([
                    'mark_type' => UserFollow::MARK_TYPE_BLOCK,
                ]);

                $markType = 'block';

                InteractionUtility::markStats($userId, 'block', $blockType, $blockId, 'increment');
                InteractionUtility::markStats($userId, 'follow', $blockType, $blockId, 'decrement');
            }
        }

        $itBlock = UserFollow::where('user_id', $blockId)->markType(UserFollow::MARK_TYPE_BLOCK)->type($blockType)->where('follow_id', $userId)->first();

        if (empty($itBlock) || $markType == 'unblock') {
            $userBlock->update([
                'is_mutual' => false,
            ]);

            $itBlock?->update([
                'is_mutual' => false,
            ]);

            return;
        }

        $userBlock->update([
            'is_mutual' => true,
        ]);

        $itBlock->update([
            'is_mutual' => true,
        ]);
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

                CacheHelper::forgetFresnsMultilingual('fresns_web_sticky_posts_by_global', 'fresnsWeb');

                if ($stickyState == Post::STICKY_GROUP && $post->group_id) {
                    $group = PrimaryHelper::fresnsModelById('group', $post->group_id);

                    CacheHelper::forgetFresnsMultilingual("fresns_web_sticky_posts_by_group_{$group?->gid}", 'fresnsWeb');
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
                CacheHelper::forgetFresnsMultilingual("fresns_web_sticky_comments_by_{$post?->pid}", 'fresnsWeb');
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

        $model = match ($type) {
            'post' => Post::with(['author', 'quotedPost', 'group', 'hashtags', 'geotag'])->where('id', $id)->first(),
            'comment' => Comment::with(['author', 'post', 'hashtags', 'geotag'])->where('id', $id)->first(),
        };

        if (empty($model)) {
            return;
        }

        // user
        $userState = UserStat::where('user_id', $model->user_id)->first();
        $author = $model->author;

        // group
        $group = match ($type) {
            'post' => $model->group,
            'comment' => Group::where('id', $model->post?->group_id)->first(),
        };

        // hashtag
        $hashtagIds = $model->hashtags?->pluck('id') ?? [];

        // geotag
        $geotag = $model->geotag;

        // link
        $linkType = match ($type) {
            'post' => DomainLinkUsage::TYPE_POST,
            'comment' => DomainLinkUsage::TYPE_COMMENT,
        };
        $linkIds = DomainLinkUsage::type($linkType)->where('usage_id', $model->id)->pluck('link_id')->toArray();
        $domainIds = DomainLink::whereIn('id', $linkIds)->pluck('domain_id')->toArray();

        // column name
        $publishCountColumn = "{$type}_publish_count"; // post_publish_count or comment_publish_count
        $countColumn = "{$type}_count"; // post_count or comment_count
        $timeColumn = "last_{$type}_at"; // last_post_at or last_comment_at

        switch ($actionType) {
            case 'increment':
                // user
                $userState?->increment($publishCountColumn);
                $author?->update([
                    $timeColumn => now(),
                ]);

                // group
                $group?->increment($countColumn);
                $group?->update([
                    $timeColumn => now(),
                ]);

                // hashtag
                Hashtag::whereIn('id', $hashtagIds)->increment($countColumn);
                Hashtag::whereIn('id', $hashtagIds)->update([
                    $timeColumn => now(),
                ]);

                // geotag
                $geotag?->increment($countColumn);
                $geotag?->update([
                    $timeColumn => now(),
                ]);

                // post and comment
                switch ($type) {
                    case 'post':
                        $model->quotedPost?->increment('quote_count');
                        break;

                    case 'comment':
                        $model->post?->increment('comment_count');
                        break;
                }

                // link
                DomainLink::whereIn('id', $linkIds)->increment($countColumn);
                Domain::whereIn('id', $domainIds)->increment($countColumn);
                break;

            case 'decrement':
                // user
                $userStateCount = $userState?->$publishCountColumn ?? 0;
                if ($userStateCount > 0) {
                    $userState->decrement($publishCountColumn);
                }

                // group
                $groupCount = $group?->$countColumn ?? 0;
                if ($groupCount > 0) {
                    $group->decrement($countColumn);
                }

                // hashtag
                Hashtag::whereIn('id', $hashtagIds)->where($countColumn, '>', 0)->decrement($countColumn);

                // geotag
                $geotagCount = $geotag?->$countColumn ?? 0;
                if ($geotagCount > 0) {
                    $geotag->decrement($countColumn);
                }

                // post and comment
                switch ($type) {
                    case 'post':
                        $quotedPostCount = $model->quotedPost?->quote_count ?? 0;
                        if ($quotedPostCount > 0) {
                            $model->quotedPost->decrement('quote_count');
                        }
                        break;

                    case 'comment':
                        $postCommentCount = $model->post?->comment_count ?? 0;
                        if ($postCommentCount > 0) {
                            $model->post->decrement('comment_count');
                        }
                        break;
                }

                // link
                DomainLink::whereIn('id', $linkIds)->where($countColumn, '>', 0)->decrement($countColumn);
                Domain::whereIn('id', $domainIds)->where($countColumn, '>', 0)->decrement($countColumn);
                break;
        }

        if ($model->parent_id) {
            InteractionUtility::parentCommentStats($model->parent_id, $actionType, 'comment_count');
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

        $model = match ($type) {
            'post' => Post::with(['quotedPost', 'group', 'hashtags', 'geotag'])->where('id', $id)->first(),
            'comment' => Comment::with(['hashtags', 'geotag'])->where('id', $id)->first(),
        };

        if (empty($model)) {
            return;
        }

        // group
        $group = match ($type) {
            'post' => $model->group,
            'comment' => null,
        };

        // hashtag
        $hashtagIds = $model->hashtags?->pluck('id') ?? [];

        // geotag
        $geotag = $model->geotag;

        // link
        $linkType = match ($type) {
            'post' => DomainLinkUsage::TYPE_POST,
            'comment' => DomainLinkUsage::TYPE_COMMENT,
        };
        $linkIds = DomainLinkUsage::type($linkType)->where('usage_id', $model->id)->pluck('link_id')->toArray();
        $domainIds = DomainLink::whereIn('id', $linkIds)->pluck('domain_id')->toArray();

        // column name
        $countColumn = "{$type}_count"; // post_count or comment_count

        switch ($actionType) {
            case 'increment':
                // group
                $group?->increment($countColumn);

                // hashtag
                Hashtag::whereIn('id', $hashtagIds)->increment($countColumn);

                // geotag
                $geotag?->increment($countColumn);

                // post
                if ($type == 'post') {
                    $model->quotedPost?->increment('quote_count');
                }

                // link
                DomainLink::whereIn('id', $linkIds)->increment($countColumn);
                Domain::whereIn('id', $domainIds)->increment($countColumn);
                break;

            case 'decrement':
                // group
                $groupCount = $group?->$countColumn ?? 0;
                if ($groupCount > 0) {
                    $group->decrement($countColumn);
                }

                // hashtag
                Hashtag::whereIn('id', $hashtagIds)->where($countColumn, '>', 0)->decrement($countColumn);

                // geotag
                $geotagCount = $geotag?->$countColumn ?? 0;
                if ($geotagCount > 0) {
                    $geotag->decrement($countColumn);
                }

                // post
                if ($type == 'post') {
                    $quotedPostCount = $model->quotedPost?->quote_count ?? 0;
                    if ($quotedPostCount > 0) {
                        $model->quotedPost->decrement('quote_count');
                    }
                }

                // link
                DomainLink::whereIn('id', $linkIds)->where($countColumn, '>', 0)->decrement($countColumn);
                Domain::whereIn('id', $domainIds)->where($countColumn, '>', 0)->decrement($countColumn);
                break;
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

        $model = match ($type) {
            'post' => Post::with(['group', 'hashtags', 'geotag'])->where('id', $id)->first(),
            'comment' => Comment::with(['post', 'hashtags', 'geotag'])->where('id', $id)->first(),
        };

        if (empty($model)) {
            return;
        }

        // user
        $userState = UserStat::where('user_id', $model->user_id)->first();

        // group
        $group = match ($type) {
            'post' => $model->group,
            'comment' => Group::where('id', $model->post?->group_id)->first(),
        };

        // hashtag
        $hashtagIds = $model->hashtags?->pluck('id') ?? [];

        // geotag
        $geotag = $model->geotag;

        // column name
        $countColumn = "{$type}_digest_count"; // post_digest_count or comment_digest_count

        switch ($actionType) {
            case 'increment':
                $userState?->increment($countColumn);
                $group?->increment($countColumn);
                Hashtag::whereIn('id', $hashtagIds)->increment($countColumn);
                $geotag?->increment($countColumn);
                if ($type == 'comment') {
                    $model->post?->increment('comment_digest_count');
                }
                break;

            case 'decrement':
                $userStateCount = $userState?->$countColumn ?? 0;
                if ($userStateCount > 0) {
                    $userState->decrement($countColumn);
                }

                $groupDigestCount = $group?->$countColumn ?? 0;
                if ($groupDigestCount > 0) {
                    $group->decrement($countColumn);
                }

                Hashtag::whereIn('id', $hashtagIds)->where($countColumn, '>', 0)->decrement($countColumn);

                if ($type == 'comment') {
                    $model->post?->decrement('comment_digest_count');
                }
                break;
        }

        if ($type == 'comment' && $model?->parent_id) {
            InteractionUtility::parentCommentStats($model->parent_id, $actionType, 'comment_digest_count');
        }

        $uid = PrimaryHelper::fresnsModelById('user', $model->user_id)?->uid;

        $actionTarget = match ($type) {
            'post' => Notification::ACTION_TARGET_POST,
            'comment' => Notification::ACTION_TARGET_COMMENT,
        };
        $actionFsid = match ($type) {
            'post' => $model->pid,
            'comment' => $model->cid,
        };

        $wordBody = [
            'uid' => $uid,
            'type' => Notification::TYPE_SYSTEM,
            'content' => null,
            'isMarkdown' => null,
            'isAccessApp' => null,
            'appFskey' => null,
            'actionUid' => null,
            'actionIsAnonymous' => false,
            'actionType' => Notification::ACTION_TYPE_DIGEST,
            'actionTarget' => $actionTarget,
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

        if (empty($comment)) {
            return;
        }

        switch ($actionType) {
            case 'increment':
                $comment->increment($tableColumn);
                $comment->update([
                    'last_comment_at' => now(),
                ]);
                break;

            case 'decrement':
                $commentColumnCount = $comment->$tableColumn ?? 0;
                if ($commentColumnCount > 0) {
                    $comment->decrement($tableColumn);
                }
                break;
        }

        // parent comment
        if ($comment->parent_id) {
            InteractionUtility::parentCommentStats($comment->parent_id, $actionType, $tableColumn);
        }
    }

    // send mark notification
    public static function sendMarkNotification(int $notificationType, int $userId, int $markType, int $markId): void
    {
        $user = PrimaryHelper::fresnsModelById('user', $userId);

        $actionModel = match ($markType) {
            Notification::ACTION_TARGET_USER => PrimaryHelper::fresnsModelById('user', $markId),
            Notification::ACTION_TARGET_GROUP => PrimaryHelper::fresnsModelById('group', $markId),
            Notification::ACTION_TARGET_HASHTAG => PrimaryHelper::fresnsModelById('hashtag', $markId),
            Notification::ACTION_TARGET_POST => PrimaryHelper::fresnsModelById('post', $markId),
            Notification::ACTION_TARGET_COMMENT => PrimaryHelper::fresnsModelById('comment', $markId),
            default => null,
        };
        $uid = match ($markType) {
            Notification::ACTION_TARGET_USER => $actionModel?->uid,
            Notification::ACTION_TARGET_GROUP => PrimaryHelper::fresnsModelById('user', $actionModel?->user_id)?->uid,
            Notification::ACTION_TARGET_HASHTAG => null,
            Notification::ACTION_TARGET_POST => PrimaryHelper::fresnsModelById('user', $actionModel?->user_id)?->uid,
            Notification::ACTION_TARGET_COMMENT => PrimaryHelper::fresnsModelById('user', $actionModel?->user_id)?->uid,
            default => null,
        };

        if (empty($uid)) {
            return;
        }

        $actionFsid = match ($markType) {
            Notification::ACTION_TARGET_USER => $actionModel->uid,
            Notification::ACTION_TARGET_GROUP => $actionModel->gid,
            Notification::ACTION_TARGET_HASHTAG => $actionModel->hid,
            Notification::ACTION_TARGET_POST => $actionModel->pid,
            Notification::ACTION_TARGET_COMMENT => $actionModel->cid,
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
            'isAccessApp' => null,
            'appFskey' => null,
            'actionUid' => $user->uid,
            'actionIsAnonymous' => $actionModel?->is_anonymous,
            'actionType' => $actionType,
            'actionTarget' => $markType,
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
            $actionTarget = match ($type) {
                'post' => Notification::ACTION_TARGET_POST,
                'comment' => Notification::ACTION_TARGET_COMMENT,
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

            $contentLangTag = $actionModel?->lang_tag ?? ConfigHelper::fresnsConfigDefaultLangTag();

            foreach ($mentions as $mention) {
                $mentionUser = PrimaryHelper::fresnsModelById('user', $mention->mention_user_id);

                $mentionWordBody = [
                    'uid' => $mentionUser->uid,
                    'type' => Notification::TYPE_MENTION,
                    'content' => [
                        $contentLangTag => Str::limit($actionModel->content),
                    ],
                    'isMarkdown' => 0,
                    'isAccessApp' => null,
                    'appFskey' => null,
                    'actionUid' => $actionUser->uid,
                    'actionIsAnonymous' => $actionModel?->is_anonymous,
                    'actionType' => Notification::ACTION_TYPE_PUBLISH,
                    'actionTarget' => $actionTarget,
                    'actionFsid' => $actionFsid,
                    'contentFsid' => null,
                ];

                try {
                    \FresnsCmdWord::plugin('Fresns')->sendNotification($mentionWordBody);
                } catch (\Exception $e) {
                    continue;
                }
            }

            if ($type == 'post' && $actionModel->quoted_post_id) {
                $notifyPost = PrimaryHelper::fresnsModelById('post', $actionModel->quoted_post_id);

                if ($notifyPost->user_id == $actionModel->user_id) {
                    return;
                }

                $notifyUser = PrimaryHelper::fresnsModelById('user', $notifyPost->user_id);

                $quoteWordBody = [
                    'uid' => $notifyUser->uid,
                    'type' => Notification::TYPE_QUOTE,
                    'content' => [
                        $contentLangTag => Str::limit($actionModel->content),
                    ],
                    'isMarkdown' => 0,
                    'isAccessApp' => null,
                    'appFskey' => null,
                    'actionUid' => $actionUser->uid,
                    'actionIsAnonymous' => $actionModel?->is_anonymous,
                    'actionType' => Notification::ACTION_TYPE_PUBLISH,
                    'actionTarget' => Notification::ACTION_TARGET_POST,
                    'actionFsid' => $actionFsid,
                    'contentFsid' => $notifyPost->pid,
                ];
                \FresnsCmdWord::plugin('Fresns')->sendNotification($quoteWordBody);
            }

            if ($type == 'comment') {
                $notifyUserId = null;
                $commentActionTarget = null;
                $commentActionFsid = null;
                $contentFsid = $actionFsid;

                $parentComment = PrimaryHelper::fresnsModelById('comment', $actionModel->parent_id);
                if ($parentComment) {
                    if ($parentComment->user_id == $actionModel->user_id) {
                        return;
                    }

                    $notifyUserId = $parentComment->user_id;
                    $commentActionTarget = Notification::ACTION_TARGET_COMMENT;
                    $commentActionFsid = $parentComment->cid;
                }

                if (empty($notifyUserId)) {
                    $notifyPost = PrimaryHelper::fresnsModelById('post', $actionModel->post_id);
                    if ($notifyPost->user_id == $actionModel->user_id) {
                        return;
                    }

                    $notifyUserId = $notifyPost->user_id;
                    $commentActionTarget = Notification::ACTION_TARGET_POST;
                    $commentActionFsid = $notifyPost->pid;
                }

                $notifyUser = PrimaryHelper::fresnsModelById('user', $notifyUserId);

                $commentWordBody = [
                    'uid' => $notifyUser->uid,
                    'type' => Notification::TYPE_COMMENT,
                    'content' => [
                        $contentLangTag => Str::limit($actionModel->content),
                    ],
                    'isMarkdown' => 0,
                    'isAccessApp' => null,
                    'appFskey' => null,
                    'actionUid' => $actionUser->uid,
                    'actionIsAnonymous' => $actionModel?->is_anonymous,
                    'actionType' => Notification::ACTION_TYPE_PUBLISH,
                    'actionTarget' => $commentActionTarget,
                    'actionFsid' => $commentActionFsid,
                    'contentFsid' => $contentFsid,
                ];
                \FresnsCmdWord::plugin('Fresns')->sendNotification($commentWordBody);
            }
        });
    }

    // get follow id array
    public static function getFollowIdArr(int $type, ?int $userId = null): ?array
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
            $followIdArr = UserFollow::markType(UserFollow::MARK_TYPE_FOLLOW)->type($type)->where('user_id', $userId)->pluck('follow_id')->toArray();

            $followIds = array_values($followIdArr);

            CacheHelper::put($followIds, $cacheKey, $cacheTag);
        }

        return $followIds;
    }

    // get block id array
    public static function getBlockIdArr(int $type, ?int $userId = null): ?array
    {
        if (empty($userId)) {
            return [];
        }

        $cacheKey = "fresns_user_block_{$type}_ids_by_{$userId}";
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
        $blockIds = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($blockIds)) {
            $blockIdArr = UserFollow::markType(UserFollow::MARK_TYPE_BLOCK)->type($type)->where('user_id', $userId)->pluck('follow_id')->toArray();

            $blockIds = array_values($blockIdArr);

            CacheHelper::put($blockIds, $cacheKey, $cacheTag);
        }

        return $blockIds;
    }

    // explode id array
    public static function explodeIdArr(string $type, ?string $string = null): ?array
    {
        if (empty($string)) {
            return [];
        }

        $fsidArr = array_filter(explode(',', $string));

        $idArr = [];
        foreach ($fsidArr as $fsid) {
            $id = PrimaryHelper::fresnsPrimaryId($type, $fsid);

            if (empty($id)) {
                continue;
            }

            $idArr[] = $id;
        }

        return $idArr;
    }

    // get private group id array
    public static function getPrivateGroupIdArr(): ?array
    {
        $cacheKey = 'fresns_group_private_ids';
        $cacheTag = 'fresnsGroups';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        $groupIds = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($groupIds)) {
            $groupIds = Group::where('privacy', Group::PRIVACY_PRIVATE)->pluck('id')->toArray();

            CacheHelper::put($groupIds, $cacheKey, $cacheTag);
        }

        return $groupIds;
    }

    // get follow type
    public static function getFollowType(string $type, int $creatorId, int $digestState, int $authUserId, ?int $groupId = null, ?int $geotagId = null): ?string
    {
        if ($type != 'all') {
            return $type;
        }

        // user
        $userInteractionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_USER, $creatorId, $authUserId);
        if ($userInteractionStatus['followStatus']) {
            return 'user';
        }

        // group
        if ($groupId) {
            $groupInteractionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_GROUP, $groupId, $authUserId);

            if ($groupInteractionStatus['followStatus']) {
                return 'group';
            }
        }

        // geotag
        if ($geotagId) {
            $geotagInteractionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_GEOTAG, $geotagId, $authUserId);

            if ($geotagInteractionStatus['followStatus']) {
                return 'geotag';
            }
        }

        // digest
        if ($digestState != Post::DIGEST_NO) {
            return 'digest';
        }

        // hashtag
        return 'hashtag';
    }
}
