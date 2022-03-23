<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\User;

use App\Fresns\Api\FsDb\FresnsUserBlocks\FresnsUserBlocks;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollows;
use App\Fresns\Api\FsDb\FresnsUserLikes\FresnsUserLikes;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Http\Base\FsApiChecker;

class FsChecker extends FsApiChecker
{
    /**
     * Check if likes, followers and blockers are added.
     *
     * markType 1.like / 2.follow / 3.block
     * markTarget 1.user / 2.group / 3.hashtag / 4.post / 5.comment
     */
    public static function checkMark($markType, $markTarget, $userId, $toUserId)
    {
        switch ($markType) {
            case 1:
                $likeCount = FresnsUserLikes::where('user_id', $userId)->where('like_type', $markTarget)->where('like_id', $toUserId)->count();
                if ($likeCount > 0) {
                    return true;
                }
                break;
            case 2:
                $followCount = FresnsUserFollows::where('user_id', $userId)->where('follow_type', $markTarget)->where('follow_id', $toUserId)->count();
                if ($followCount > 0) {
                    return true;
                }
                break;
            default:
                $blockCount = FresnsUserBlocks::where('user_id', $userId)->where('block_type', $markTarget)->where('block_id', $toUserId)->count();
                if ($blockCount > 0) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Whether the right to operate
     * https://fresns.org/database/keyname/interactives.html
     * Interactive behavior settings.
     *
     * markType 1.like / 2.follow / 3.block
     * markTarget 1.user / 2.group / 3.hashtag / 4.post / 5.comment
     */
    public static function checkMarkApi($markType, $markTarget)
    {
        switch ($markType) {
            case 1:
                switch ($markTarget) {
                    case 1:
                        $isMark = ApiConfigHelper::getConfigByItemKey('like_user_setting');
                        break;
                    case 2:
                        $isMark = ApiConfigHelper::getConfigByItemKey('like_group_setting');
                        break;
                    case 3:
                        $isMark = ApiConfigHelper::getConfigByItemKey('like_hashtag_setting');
                        break;
                    case 4:
                        $isMark = ApiConfigHelper::getConfigByItemKey('like_post_setting');
                        break;
                    default:
                        $isMark = ApiConfigHelper::getConfigByItemKey('like_comment_setting');
                        break;
                }
                break;
            case 2:
                switch ($markTarget) {
                    case 1:
                        $isMark = ApiConfigHelper::getConfigByItemKey('follow_user_setting');
                        break;
                    case 2:
                        $isMark = ApiConfigHelper::getConfigByItemKey('follow_group_setting');
                        break;
                    case 3:
                        $isMark = ApiConfigHelper::getConfigByItemKey('follow_hashtag_setting');
                        break;
                    case 4:
                        $isMark = ApiConfigHelper::getConfigByItemKey('follow_post_setting');
                        break;
                    default:
                        $isMark = ApiConfigHelper::getConfigByItemKey('follow_comment_setting');
                        break;
                }
                break;

            default:
                switch ($markTarget) {
                    case 1:
                        $isMark = ApiConfigHelper::getConfigByItemKey('block_user_setting');
                        break;
                    case 2:
                        $isMark = ApiConfigHelper::getConfigByItemKey('block_group_setting');
                        break;
                    case 3:
                        $isMark = ApiConfigHelper::getConfigByItemKey('block_hashtag_setting');
                        break;
                    case 4:
                        $isMark = ApiConfigHelper::getConfigByItemKey('block_post_setting');
                        break;
                    default:
                        $isMark = ApiConfigHelper::getConfigByItemKey('block_comment_setting');
                        break;
                }
                break;
        }

        return $isMark;
    }

    /**
     * Whether to output data when viewing other people's information
     * https://fresns.org/database/keyname/interactives.html
     * View other people's content settings.
     *
     * markType 1.like / 2.follow / 3.block
     * markTarget 1.user / 2.group / 3.hashtag / 4.post / 5.comment
     */
    public static function checkMarkLists($viewType, $viewTarget)
    {
        switch ($viewType) {
            case 1:
                switch ($viewTarget) {
                    case 1:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_like_users');
                        break;
                    case 2:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_like_groups');
                        break;
                    case 3:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_like_hashtags');
                        break;
                    case 4:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_like_posts');
                        break;
                    default:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_like_comments');
                        break;
                }
                break;
            case 2:
                switch ($viewTarget) {
                    case 1:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_follow_users');
                        break;
                    case 2:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_follow_groups');
                        break;
                    case 3:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_follow_hashtags');
                        break;
                    case 4:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_follow_posts');
                        break;
                    default:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_follow_comments');
                        break;
                }
                break;

            default:
                switch ($viewTarget) {
                    case 1:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_block_users');
                        break;
                    case 2:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_block_groups');
                        break;
                    case 3:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_block_hashtags');
                        break;
                    case 4:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_block_posts');
                        break;
                    default:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_block_comments');
                        break;
                }
                break;
        }

        return $isMark;
    }
}
