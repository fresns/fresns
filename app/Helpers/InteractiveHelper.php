<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Account;
use App\Models\Comment;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;

class InteractiveHelper
{
    /**
     * @return array
     */
    public static function fresnsOverview()
    {
        $overview['accountCount'] = Account::count();
        $overview['userCount'] = User::count();
        $overview['groupCount'] = Group::count();
        $overview['hashtagCount'] = Hashtag::count();
        $overview['postCount'] = Post::count();
        $overview['commentCount'] = Comment::count();
        $overview['postDigest1Count'] = Post::where('digest_state', 2)->count();
        $overview['postDigest2Count'] = Post::where('digest_state', 3)->count();
        $overview['commentDigest1Count'] = Comment::where('digest_state', 2)->count();
        $overview['commentDigest2Count'] = Comment::where('digest_state', 3)->count();

        return $overview;
    }

    /**
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsUserInteractive(?string $langTag = null)
    {
        $itemData = ConfigHelper::fresnsConfigByItemKeys([
            'user_name', 'user_uid_name', 'user_username_name', 'user_nickname_name', 'user_role_name', 'user_bio_name',
            'like_user_setting', 'like_user_name',
            'dislike_user_setting', 'dislike_user_name',
            'follow_user_setting', 'follow_user_name',
            'block_user_setting', 'block_user_name',
            'publish_post_name', 'publish_comment_name',
        ], $langTag);

        $interactive['userName'] = $itemData['user_name'];
        $interactive['userUidName'] = $itemData['user_uid_name'];
        $interactive['userUsernameName'] = $itemData['user_username_name'];
        $interactive['userNicknameName'] = $itemData['user_nickname_name'];
        $interactive['userRoleName'] = $itemData['user_role_name'];
        $interactive['userBioName'] = $itemData['user_bio_name'];
        $interactive['followSetting'] = $itemData['follow_user_setting'];
        $interactive['followName'] = $itemData['follow_user_name'];
        $interactive['likeSetting'] = $itemData['like_user_setting'];
        $interactive['likeName'] = $itemData['like_user_name'];
        $interactive['dislikeSetting'] = $itemData['dislike_user_setting'];
        $interactive['dislikeName'] = $itemData['dislike_user_name'];
        $interactive['blockSetting'] = $itemData['block_user_setting'];
        $interactive['blockName'] = $itemData['block_user_name'];
        $interactive['publishPostName'] = $itemData['publish_post_name'];
        $interactive['publishCommentName'] = $itemData['publish_comment_name'];

        return $interactive;
    }

    /**
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsUserProfileInteractive(?string $langTag = null)
    {
        $itemData = ConfigHelper::fresnsConfigByItemKeys([
            'it_home_list', 'it_posts', 'it_comments', 'it_likers', 'it_followers', 'it_blockers',
            'it_like_users', 'it_like_groups', 'it_like_hashtags', 'it_like_posts', 'it_like_comments',
            'it_dislike_users', 'it_dislike_groups', 'it_dislike_hashtags', 'it_dislike_posts', 'it_dislike_comments',
            'it_follow_users', 'it_follow_groups', 'it_follow_hashtags', 'it_follow_posts', 'it_follow_comments',
            'it_block_users', 'it_block_groups', 'it_block_hashtags', 'it_block_posts', 'it_block_comments',
            'publish_post_name', 'publish_comment_name',
        ], $langTag);

        $interactive['itHomeList'] = $itemData['it_home_list'];
        $interactive['itPosts'] = $itemData['it_posts'];
        $interactive['itComments'] = $itemData['it_comments'];
        $interactive['itLikers'] = $itemData['it_likers'];
        $interactive['itFollowers'] = $itemData['it_followers'];
        $interactive['itBlockers'] = $itemData['it_blockers'];
        $interactive['itLikeUsers'] = $itemData['it_like_users'];
        $interactive['itLikeGroups'] = $itemData['it_like_groups'];
        $interactive['itLikeHashtags'] = $itemData['it_like_hashtags'];
        $interactive['itLikePosts'] = $itemData['it_like_posts'];
        $interactive['itLikeComments'] = $itemData['it_like_comments'];
        $interactive['itDislikeUsers'] = $itemData['it_dislike_users'];
        $interactive['itDislikeGroups'] = $itemData['it_dislike_groups'];
        $interactive['itDislikeHashtags'] = $itemData['it_dislike_hashtags'];
        $interactive['itDislikePosts'] = $itemData['it_dislike_posts'];
        $interactive['itDislikeComments'] = $itemData['it_dislike_comments'];
        $interactive['itFollowUsers'] = $itemData['it_follow_users'];
        $interactive['itFollowGroups'] = $itemData['it_follow_groups'];
        $interactive['itFollowHashtags'] = $itemData['it_follow_hashtags'];
        $interactive['itFollowPosts'] = $itemData['it_follow_posts'];
        $interactive['itFollowComments'] = $itemData['it_follow_comments'];
        $interactive['itBlockUsers'] = $itemData['it_block_users'];
        $interactive['itBlockGroups'] = $itemData['it_block_groups'];
        $interactive['itBlockHashtags'] = $itemData['it_block_hashtags'];
        $interactive['itBlockPosts'] = $itemData['it_block_posts'];
        $interactive['itBlockComments'] = $itemData['it_block_comments'];
        $interactive['publishPostName'] = $itemData['publish_post_name'];
        $interactive['publishCommentName'] = $itemData['publish_comment_name'];

        return $interactive;
    }

    /**
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsGroupInteractive(?string $langTag = null)
    {
        $itemData = ConfigHelper::fresnsConfigByItemKeys([
            'group_name',
            'like_group_setting', 'like_group_name',
            'dislike_group_setting', 'dislike_group_name',
            'follow_group_setting', 'follow_group_name',
            'block_group_setting', 'block_group_name',
            'publish_post_name', 'publish_comment_name',
        ], $langTag);

        $interactive['groupName'] = $itemData['group_name'];
        $interactive['likeSetting'] = $itemData['like_group_setting'];
        $interactive['likeName'] = $itemData['like_group_name'];
        $interactive['dislikeSetting'] = $itemData['dislike_group_setting'];
        $interactive['dislikeName'] = $itemData['dislike_group_name'];
        $interactive['followSetting'] = $itemData['follow_group_setting'];
        $interactive['followName'] = $itemData['follow_group_name'];
        $interactive['blockSetting'] = $itemData['block_group_setting'];
        $interactive['blockName'] = $itemData['block_group_name'];
        $interactive['publishPostName'] = $itemData['publish_post_name'];
        $interactive['publishCommentName'] = $itemData['publish_comment_name'];

        return $interactive;
    }

    /**
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsHashtagInteractive(?string $langTag = null)
    {
        $itemData = ConfigHelper::fresnsConfigByItemKeys([
            'hashtag_name',
            'like_hashtag_setting', 'like_hashtag_name',
            'dislike_hashtag_setting', 'dislike_hashtag_name',
            'follow_hashtag_setting', 'follow_hashtag_name',
            'block_hashtag_setting', 'block_hashtag_name',
            'publish_post_name', 'publish_comment_name',
        ], $langTag);

        $interactive['hashtagName'] = $itemData['hashtag_name'];
        $interactive['likeSetting'] = $itemData['like_hashtag_setting'];
        $interactive['likeName'] = $itemData['like_hashtag_name'];
        $interactive['dislikeSetting'] = $itemData['dislike_hashtag_setting'];
        $interactive['dislikeName'] = $itemData['dislike_hashtag_name'];
        $interactive['followSetting'] = $itemData['follow_hashtag_setting'];
        $interactive['followName'] = $itemData['follow_hashtag_name'];
        $interactive['blockSetting'] = $itemData['block_hashtag_setting'];
        $interactive['blockName'] = $itemData['block_hashtag_name'];
        $interactive['publishPostName'] = $itemData['publish_post_name'];
        $interactive['publishCommentName'] = $itemData['publish_comment_name'];

        return $interactive;
    }

    /**
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsPostInteractive(?string $langTag = null)
    {
        $itemData = ConfigHelper::fresnsConfigByItemKeys([
            'post_name',
            'like_post_setting', 'like_post_name',
            'dislike_post_setting', 'dislike_post_name',
            'follow_post_setting', 'follow_post_name',
            'block_post_setting', 'block_post_name',
            'publish_post_name', 'publish_comment_name',
        ], $langTag);

        $interactive['postName'] = $itemData['post_name'];
        $interactive['likeSetting'] = $itemData['like_post_setting'];
        $interactive['likeName'] = $itemData['like_post_name'];
        $interactive['dislikeSetting'] = $itemData['dislike_post_setting'];
        $interactive['dislikeName'] = $itemData['dislike_post_name'];
        $interactive['followSetting'] = $itemData['follow_post_setting'];
        $interactive['followName'] = $itemData['follow_post_name'];
        $interactive['blockSetting'] = $itemData['block_post_setting'];
        $interactive['blockName'] = $itemData['block_post_name'];
        $interactive['publishPostName'] = $itemData['publish_post_name'];
        $interactive['publishCommentName'] = $itemData['publish_comment_name'];

        return $interactive;
    }

    /**
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsCommentInteractive(?string $langTag = null)
    {
        $itemData = ConfigHelper::fresnsConfigByItemKeys([
            'comment_name',
            'like_comment_setting', 'like_comment_name',
            'dislike_comment_setting', 'dislike_comment_name',
            'follow_comment_setting', 'follow_comment_name',
            'block_comment_setting', 'block_comment_name',
            'publish_post_name', 'publish_comment_name',
        ], $langTag);

        $interactive['commentName'] = $itemData['comment_name'];
        $interactive['likeSetting'] = $itemData['like_comment_setting'];
        $interactive['likeName'] = $itemData['like_comment_name'];
        $interactive['dislikeSetting'] = $itemData['dislike_comment_setting'];
        $interactive['dislikeName'] = $itemData['dislike_comment_name'];
        $interactive['followSetting'] = $itemData['follow_comment_setting'];
        $interactive['followName'] = $itemData['follow_comment_name'];
        $interactive['blockSetting'] = $itemData['block_comment_setting'];
        $interactive['blockName'] = $itemData['block_comment_name'];
        $interactive['publishPostName'] = $itemData['publish_post_name'];
        $interactive['publishCommentName'] = $itemData['publish_comment_name'];

        return $interactive;
    }

    // user anonymous profile
    public static function fresnsUserAnonymousProfile()
    {
        $anonymousAvatar = ConfigHelper::fresnsConfigByItemKey('anonymous_avatar');
        $userAvatar = null;
        if (ConfigHelper::fresnsConfigFileValueTypeByItemKey('anonymous_avatar') == 'URL') {
            $userAvatar = $anonymousAvatar;
        } else {
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink([
                'fileId' => $anonymousAvatar,
            ]);
            $userAvatar = $fresnsResp->getData('imageAvatarUrl');
        }

        $profile['uid'] = null;
        $profile['username'] = null;
        $profile['nickname'] = null;
        $profile['avatar'] = $userAvatar;
        $profile['decorate'] = null;
        $profile['banner'] = null;
        $profile['gender'] = null;
        $profile['birthday'] = null;
        $profile['bio'] = null;
        $profile['location'] = null;
        $profile['dialogLimit'] = null;
        $profile['commentLimit'] = null;
        $profile['timezone'] = null;
        $profile['verifiedStatus'] = false;
        $profile['verifiedIcon'] = null;
        $profile['verifiedDesc'] = null;
        $profile['verifiedDateTime'] = null;
        $profile['expiryDateTime'] = null;
        $profile['lastPublishPost'] = null;
        $profile['lastPublishComment'] = null;
        $profile['lastEditUsername'] = null;
        $profile['lastEditNickname'] = null;
        $profile['registerDateTime'] = null;
        $profile['hasPassword'] = false;
        $profile['status'] = true;
        $profile['deactivate'] = false;
        $profile['deactivateTime'] = null;

        $profile['nicknameColor'] = null;
        $profile['rid'] = null;
        $profile['roleName'] = null;
        $profile['roleNameDisplay'] = false;
        $profile['roleIcon'] = null;
        $profile['roleIconDisplay'] = false;
        $profile['roleExpiryDateTime'] = null;
        $profile['rolePermissions'] = null;
        $profile['roleStatus'] = true;

        return $profile;
    }
}
