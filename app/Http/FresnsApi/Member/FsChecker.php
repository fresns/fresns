<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Member;

use App\Http\FresnsApi\Base\FresnsBaseChecker;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollows;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikes;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShields;

class FsChecker extends FresnsBaseChecker
{
    /**
     * Check if likes, followers and blockers are added.
     *
     * markType 1.like / 2.follow / 3.block
     * markTarget 1.member / 2.group / 3.hashtag / 4.post / 5.comment
     */
    public static function checkMark($markType, $markTarget, $memberId, $toMemberId)
    {
        switch ($markType) {
            case 1:
                $likeCount = FresnsMemberLikes::where('member_id', $memberId)->where('like_type', $markTarget)->where('like_id', $toMemberId)->count();
                if ($likeCount > 0) {
                    return true;
                }
                break;
            case 2:
                $followCount = FresnsMemberFollows::where('member_id', $memberId)->where('follow_type', $markTarget)->where('follow_id', $toMemberId)->count();
                if ($followCount > 0) {
                    return true;
                }
                break;
            default:
                $shieldCount = FresnsMemberShields::where('member_id', $memberId)->where('shield_type', $markTarget)->where('shield_id', $toMemberId)->count();
                if ($shieldCount > 0) {
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
     * markTarget 1.member / 2.group / 3.hashtag / 4.post / 5.comment
     */
    public static function checkMarkApi($markType, $markTarget)
    {
        switch ($markType) {
            case 1:
                switch ($markTarget) {
                    case 1:
                        $isMark = ApiConfigHelper::getConfigByItemKey('like_member_setting');
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
                        $isMark = ApiConfigHelper::getConfigByItemKey('follow_member_setting');
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
                        $isMark = ApiConfigHelper::getConfigByItemKey('shield_member_setting');
                        break;
                    case 2:
                        $isMark = ApiConfigHelper::getConfigByItemKey('shield_group_setting');
                        break;
                    case 3:
                        $isMark = ApiConfigHelper::getConfigByItemKey('shield_hashtag_setting');
                        break;
                    case 4:
                        $isMark = ApiConfigHelper::getConfigByItemKey('shield_post_setting');
                        break;
                    default:
                        $isMark = ApiConfigHelper::getConfigByItemKey('shield_comment_setting');
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
     * markTarget 1.member / 2.group / 3.hashtag / 4.post / 5.comment
     */
    public static function checkMarkLists($viewType, $viewTarget)
    {
        switch ($viewType) {
            case 1:
                switch ($viewTarget) {
                    case 1:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_like_members');
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
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_follow_members');
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
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_shield_members');
                        break;
                    case 2:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_shield_groups');
                        break;
                    case 3:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_shield_hashtags');
                        break;
                    case 4:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_shield_posts');
                        break;
                    default:
                        $isMark = ApiConfigHelper::getConfigByItemKey('it_shield_comments');
                        break;
                }
                break;
        }

        return $isMark;
    }
}
