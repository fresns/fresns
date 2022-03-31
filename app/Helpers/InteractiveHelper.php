<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

class InteractiveHelper
{
    /**
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsUserInteractive(string $langTag = '')
    {
        $interactive['userName'] = ConfigHelper::fresnsConfigByItemKey('user_name', $langTag);
        $interactive['userUidName'] = ConfigHelper::fresnsConfigByItemKey('user_uid_name', $langTag);
        $interactive['userUsernameName'] = ConfigHelper::fresnsConfigByItemKey('user_username_name', $langTag);
        $interactive['userNicknameName'] = ConfigHelper::fresnsConfigByItemKey('user_nickname_name', $langTag);
        $interactive['userRoleName'] = ConfigHelper::fresnsConfigByItemKey('user_role_name', $langTag);
        $interactive['followSetting'] = ConfigHelper::fresnsConfigByItemKey('follow_user_setting');
        $interactive['followName'] = ConfigHelper::fresnsConfigByItemKey('follow_user_name', $langTag);
        $interactive['likeSetting'] = ConfigHelper::fresnsConfigByItemKey('like_user_setting');
        $interactive['likeName'] = ConfigHelper::fresnsConfigByItemKey('like_user_name', $langTag);
        $interactive['blockSetting'] = ConfigHelper::fresnsConfigByItemKey('block_user_setting');
        $interactive['blockName'] = ConfigHelper::fresnsConfigByItemKey('block_user_name', $langTag);
        $interactive['publishPostName'] = ConfigHelper::fresnsConfigByItemKey('publish_post_name', $langTag);
        $interactive['publishCommentName'] = ConfigHelper::fresnsConfigByItemKey('publish_comment_name', $langTag);

        return $interactive;
    }

    /**
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsUserProfileInteractive(string $langTag = '')
    {
        $interactive['itHomeList'] = ConfigHelper::fresnsConfigByItemKey('it_home_list');
        $interactive['itPosts'] = ConfigHelper::fresnsConfigByItemKey('it_posts');
        $interactive['itComments'] = ConfigHelper::fresnsConfigByItemKey('it_comments');
        $interactive['itLikers'] = ConfigHelper::fresnsConfigByItemKey('it_likers');
        $interactive['itFollowers'] = ConfigHelper::fresnsConfigByItemKey('it_followers');
        $interactive['itBlockers'] = ConfigHelper::fresnsConfigByItemKey('it_blockers');
        $interactive['itLikeUsers'] = ConfigHelper::fresnsConfigByItemKey('it_like_users');
        $interactive['itLikeGroups'] = ConfigHelper::fresnsConfigByItemKey('it_like_groups');
        $interactive['itLikeHashtags'] = ConfigHelper::fresnsConfigByItemKey('it_like_hashtags');
        $interactive['itLikePosts'] = ConfigHelper::fresnsConfigByItemKey('it_like_posts');
        $interactive['itLikeComments'] = ConfigHelper::fresnsConfigByItemKey('it_like_comments');
        $interactive['itFollowUsers'] = ConfigHelper::fresnsConfigByItemKey('it_follow_users');
        $interactive['itFollowGroups'] = ConfigHelper::fresnsConfigByItemKey('it_follow_groups');
        $interactive['itFollowHashtags'] = ConfigHelper::fresnsConfigByItemKey('it_follow_hashtags');
        $interactive['itFollowPosts'] = ConfigHelper::fresnsConfigByItemKey('it_follow_posts');
        $interactive['itFollowComments'] = ConfigHelper::fresnsConfigByItemKey('it_follow_comments');
        $interactive['itBlockUsers'] = ConfigHelper::fresnsConfigByItemKey('it_block_users');
        $interactive['itBlockGroups'] = ConfigHelper::fresnsConfigByItemKey('it_block_groups');
        $interactive['itBlockHashtags'] = ConfigHelper::fresnsConfigByItemKey('it_block_hashtags');
        $interactive['itBlockPosts'] = ConfigHelper::fresnsConfigByItemKey('it_block_posts');
        $interactive['itBlockComments'] = ConfigHelper::fresnsConfigByItemKey('it_block_comments');
        $interactive['publishPostName'] = ConfigHelper::fresnsConfigByItemKey('publish_post_name', $langTag);
        $interactive['publishCommentName'] = ConfigHelper::fresnsConfigByItemKey('publish_comment_name', $langTag);

        return $interactive;
    }

    /**
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsGroupInteractive(string $langTag = '')
    {
        $interactive['groupName'] = ConfigHelper::fresnsConfigByItemKey('group_name', $langTag);
        $interactive['followSetting'] = ConfigHelper::fresnsConfigByItemKey('follow_group_setting');
        $interactive['followName'] = ConfigHelper::fresnsConfigByItemKey('follow_group_name', $langTag);
        $interactive['likeSetting'] = ConfigHelper::fresnsConfigByItemKey('like_group_setting');
        $interactive['likeName'] = ConfigHelper::fresnsConfigByItemKey('like_group_name', $langTag);
        $interactive['blockSetting'] = ConfigHelper::fresnsConfigByItemKey('block_group_setting');
        $interactive['blockName'] = ConfigHelper::fresnsConfigByItemKey('block_group_name', $langTag);
        $interactive['publishPostName'] = ConfigHelper::fresnsConfigByItemKey('publish_post_name', $langTag);
        $interactive['publishCommentName'] = ConfigHelper::fresnsConfigByItemKey('publish_comment_name', $langTag);

        return $interactive;
    }

    /**
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsHashtagInteractive(string $langTag = '')
    {
        $interactive['hashtagName'] = ConfigHelper::fresnsConfigByItemKey('hashtag_name', $langTag);
        $interactive['followSetting'] = ConfigHelper::fresnsConfigByItemKey('follow_hashtag_setting');
        $interactive['followName'] = ConfigHelper::fresnsConfigByItemKey('follow_hashtag_name', $langTag);
        $interactive['likeSetting'] = ConfigHelper::fresnsConfigByItemKey('like_hashtag_setting');
        $interactive['likeName'] = ConfigHelper::fresnsConfigByItemKey('like_hashtag_name', $langTag);
        $interactive['blockSetting'] = ConfigHelper::fresnsConfigByItemKey('block_hashtag_setting');
        $interactive['blockName'] = ConfigHelper::fresnsConfigByItemKey('block_hashtag_name', $langTag);
        $interactive['publishPostName'] = ConfigHelper::fresnsConfigByItemKey('publish_post_name', $langTag);
        $interactive['publishCommentName'] = ConfigHelper::fresnsConfigByItemKey('publish_comment_name', $langTag);

        return $interactive;
    }

    /**
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsPostInteractive(string $langTag = '')
    {
        $interactive['postName'] = ConfigHelper::fresnsConfigByItemKey('post_name', $langTag);
        $interactive['followSetting'] = ConfigHelper::fresnsConfigByItemKey('follow_post_setting');
        $interactive['followName'] = ConfigHelper::fresnsConfigByItemKey('follow_post_name', $langTag);
        $interactive['likeSetting'] = ConfigHelper::fresnsConfigByItemKey('like_post_setting');
        $interactive['likeName'] = ConfigHelper::fresnsConfigByItemKey('like_post_name', $langTag);
        $interactive['blockSetting'] = ConfigHelper::fresnsConfigByItemKey('block_post_setting');
        $interactive['blockName'] = ConfigHelper::fresnsConfigByItemKey('block_post_name', $langTag);
        $interactive['publishPostName'] = ConfigHelper::fresnsConfigByItemKey('publish_post_name', $langTag);
        $interactive['publishCommentName'] = ConfigHelper::fresnsConfigByItemKey('publish_comment_name', $langTag);

        return $interactive;
    }

    /**
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsCommentInteractive(string $langTag = '')
    {
        $interactive['commentName'] = ConfigHelper::fresnsConfigByItemKey('comment_name', $langTag);
        $interactive['followSetting'] = ConfigHelper::fresnsConfigByItemKey('follow_comment_setting');
        $interactive['followName'] = ConfigHelper::fresnsConfigByItemKey('follow_comment_name', $langTag);
        $interactive['likeSetting'] = ConfigHelper::fresnsConfigByItemKey('like_comment_setting');
        $interactive['likeName'] = ConfigHelper::fresnsConfigByItemKey('like_comment_name', $langTag);
        $interactive['blockSetting'] = ConfigHelper::fresnsConfigByItemKey('block_comment_setting');
        $interactive['blockName'] = ConfigHelper::fresnsConfigByItemKey('block_comment_name', $langTag);
        $interactive['publishPostName'] = ConfigHelper::fresnsConfigByItemKey('publish_post_name', $langTag);
        $interactive['publishCommentName'] = ConfigHelper::fresnsConfigByItemKey('publish_comment_name', $langTag);

        return $interactive;
    }
}
