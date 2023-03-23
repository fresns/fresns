<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Account;
use App\Models\Comment;
use App\Models\File;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;

class InteractionHelper
{
    public static function fresnsOverview(): array
    {
        $overview['accountCount'] = Account::count();
        $overview['userCount'] = User::count();
        $overview['groupCount'] = Group::count();
        $overview['hashtagCount'] = Hashtag::count();
        $overview['postCount'] = Post::count();
        $overview['commentCount'] = Comment::count();
        $overview['postDigest1Count'] = Post::where('digest_state', Post::DIGEST_GENERAL)->count();
        $overview['postDigest2Count'] = Post::where('digest_state', Post::DIGEST_BEST)->count();
        $overview['commentDigest1Count'] = Comment::where('digest_state', Comment::DIGEST_GENERAL)->count();
        $overview['commentDigest2Count'] = Comment::where('digest_state', Comment::DIGEST_BEST)->count();

        return $overview;
    }

    public static function fresnsRoleInfo(int $roleId, ?string $langTag = null): array
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_role_{$roleId}_{$langTag}";
        $cacheTag = 'fresnsConfigs';

        $roleData = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($roleData)) {
            $roleModel = Role::whereId($roleId)->first();

            if (empty($roleModel)) {
                return null;
            }

            foreach ($roleModel->permissions as $perm) {
                $permission['rid'] = $roleModel->id;
                $permission[$perm['permKey']] = $perm['permValue'];
            }

            $item['rid'] = $roleModel->id;
            $item['isMain'] = false;
            $item['nicknameColor'] = $roleModel->nickname_color;
            $item['name'] = LanguageHelper::fresnsLanguageByTableId('roles', 'name', $roleModel->id, $langTag);
            $item['nameDisplay'] = (bool) $roleModel->is_display_name;
            $item['icon'] = FileHelper::fresnsFileUrlByTableColumn($roleModel->icon_file_id, $roleModel->icon_file_url);
            $item['iconDisplay'] = (bool) $roleModel->is_display_icon;
            $item['expiryDateTime'] = null;
            $item['rankState'] = $roleModel->rank_state;
            $item['permissions'] = $permission;
            $item['status'] = (bool) $roleModel->is_enable;

            $roleData = $item;

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($roleData, $cacheKey, $cacheTag, null, $cacheTime);
        }

        return $roleData;
    }

    public static function fresnsUserInteraction(?string $langTag = null): array
    {
        $itemData = ConfigHelper::fresnsConfigByItemKeys([
            'user_name', 'user_uid_name', 'user_username_name', 'user_nickname_name', 'user_role_name', 'user_bio_name',
            'like_user_setting', 'like_user_name',
            'dislike_user_setting', 'dislike_user_name',
            'follow_user_setting', 'follow_user_name',
            'block_user_setting', 'block_user_name',
            'publish_post_name', 'publish_comment_name',
        ], $langTag);

        $interaction['userName'] = $itemData['user_name'];
        $interaction['userUidName'] = $itemData['user_uid_name'];
        $interaction['userUsernameName'] = $itemData['user_username_name'];
        $interaction['userNicknameName'] = $itemData['user_nickname_name'];
        $interaction['userRoleName'] = $itemData['user_role_name'];
        $interaction['userBioName'] = $itemData['user_bio_name'];
        $interaction['followSetting'] = $itemData['follow_user_setting'];
        $interaction['followName'] = $itemData['follow_user_name'];
        $interaction['likeSetting'] = $itemData['like_user_setting'];
        $interaction['likeName'] = $itemData['like_user_name'];
        $interaction['dislikeSetting'] = $itemData['dislike_user_setting'];
        $interaction['dislikeName'] = $itemData['dislike_user_name'];
        $interaction['blockSetting'] = $itemData['block_user_setting'];
        $interaction['blockName'] = $itemData['block_user_name'];
        $interaction['publishPostName'] = $itemData['publish_post_name'];
        $interaction['publishCommentName'] = $itemData['publish_comment_name'];

        return $interaction;
    }

    public static function fresnsUserProfileInteraction(?string $langTag = null): array
    {
        $itemData = ConfigHelper::fresnsConfigByItemKeys([
            'it_home_list', 'it_posts', 'it_comments', 'it_likers', 'it_followers', 'it_blockers',
            'it_like_users', 'it_like_groups', 'it_like_hashtags', 'it_like_posts', 'it_like_comments',
            'it_dislike_users', 'it_dislike_groups', 'it_dislike_hashtags', 'it_dislike_posts', 'it_dislike_comments',
            'it_follow_users', 'it_follow_groups', 'it_follow_hashtags', 'it_follow_posts', 'it_follow_comments',
            'it_block_users', 'it_block_groups', 'it_block_hashtags', 'it_block_posts', 'it_block_comments',
            'publish_post_name', 'publish_comment_name',
        ], $langTag);

        $interaction['itHomeList'] = $itemData['it_home_list'];
        $interaction['itPosts'] = $itemData['it_posts'];
        $interaction['itComments'] = $itemData['it_comments'];
        $interaction['itLikers'] = $itemData['it_likers'];
        $interaction['itFollowers'] = $itemData['it_followers'];
        $interaction['itBlockers'] = $itemData['it_blockers'];
        $interaction['itLikeUsers'] = $itemData['it_like_users'];
        $interaction['itLikeGroups'] = $itemData['it_like_groups'];
        $interaction['itLikeHashtags'] = $itemData['it_like_hashtags'];
        $interaction['itLikePosts'] = $itemData['it_like_posts'];
        $interaction['itLikeComments'] = $itemData['it_like_comments'];
        $interaction['itDislikeUsers'] = $itemData['it_dislike_users'];
        $interaction['itDislikeGroups'] = $itemData['it_dislike_groups'];
        $interaction['itDislikeHashtags'] = $itemData['it_dislike_hashtags'];
        $interaction['itDislikePosts'] = $itemData['it_dislike_posts'];
        $interaction['itDislikeComments'] = $itemData['it_dislike_comments'];
        $interaction['itFollowUsers'] = $itemData['it_follow_users'];
        $interaction['itFollowGroups'] = $itemData['it_follow_groups'];
        $interaction['itFollowHashtags'] = $itemData['it_follow_hashtags'];
        $interaction['itFollowPosts'] = $itemData['it_follow_posts'];
        $interaction['itFollowComments'] = $itemData['it_follow_comments'];
        $interaction['itBlockUsers'] = $itemData['it_block_users'];
        $interaction['itBlockGroups'] = $itemData['it_block_groups'];
        $interaction['itBlockHashtags'] = $itemData['it_block_hashtags'];
        $interaction['itBlockPosts'] = $itemData['it_block_posts'];
        $interaction['itBlockComments'] = $itemData['it_block_comments'];
        $interaction['publishPostName'] = $itemData['publish_post_name'];
        $interaction['publishCommentName'] = $itemData['publish_comment_name'];

        return $interaction;
    }

    public static function fresnsGroupInteraction(?string $langTag = null): array
    {
        $itemData = ConfigHelper::fresnsConfigByItemKeys([
            'group_name',
            'like_group_setting', 'like_group_name',
            'dislike_group_setting', 'dislike_group_name',
            'follow_group_setting', 'follow_group_name',
            'block_group_setting', 'block_group_name',
            'publish_post_name', 'publish_comment_name',
        ], $langTag);

        $interaction['groupName'] = $itemData['group_name'];
        $interaction['likeSetting'] = $itemData['like_group_setting'];
        $interaction['likeName'] = $itemData['like_group_name'];
        $interaction['dislikeSetting'] = $itemData['dislike_group_setting'];
        $interaction['dislikeName'] = $itemData['dislike_group_name'];
        $interaction['followSetting'] = $itemData['follow_group_setting'];
        $interaction['followName'] = $itemData['follow_group_name'];
        $interaction['blockSetting'] = $itemData['block_group_setting'];
        $interaction['blockName'] = $itemData['block_group_name'];
        $interaction['publishPostName'] = $itemData['publish_post_name'];
        $interaction['publishCommentName'] = $itemData['publish_comment_name'];

        return $interaction;
    }

    public static function fresnsHashtagInteraction(?string $langTag = null): array
    {
        $itemData = ConfigHelper::fresnsConfigByItemKeys([
            'hashtag_name',
            'like_hashtag_setting', 'like_hashtag_name',
            'dislike_hashtag_setting', 'dislike_hashtag_name',
            'follow_hashtag_setting', 'follow_hashtag_name',
            'block_hashtag_setting', 'block_hashtag_name',
            'publish_post_name', 'publish_comment_name',
        ], $langTag);

        $interaction['hashtagName'] = $itemData['hashtag_name'];
        $interaction['likeSetting'] = $itemData['like_hashtag_setting'];
        $interaction['likeName'] = $itemData['like_hashtag_name'];
        $interaction['dislikeSetting'] = $itemData['dislike_hashtag_setting'];
        $interaction['dislikeName'] = $itemData['dislike_hashtag_name'];
        $interaction['followSetting'] = $itemData['follow_hashtag_setting'];
        $interaction['followName'] = $itemData['follow_hashtag_name'];
        $interaction['blockSetting'] = $itemData['block_hashtag_setting'];
        $interaction['blockName'] = $itemData['block_hashtag_name'];
        $interaction['publishPostName'] = $itemData['publish_post_name'];
        $interaction['publishCommentName'] = $itemData['publish_comment_name'];

        return $interaction;
    }

    public static function fresnsPostInteraction(?string $langTag = null): array
    {
        $itemData = ConfigHelper::fresnsConfigByItemKeys([
            'post_name',
            'like_post_setting', 'like_post_name',
            'dislike_post_setting', 'dislike_post_name',
            'follow_post_setting', 'follow_post_name',
            'block_post_setting', 'block_post_name',
            'publish_post_name', 'publish_comment_name',
        ], $langTag);

        $interaction['postName'] = $itemData['post_name'];
        $interaction['likeSetting'] = $itemData['like_post_setting'];
        $interaction['likeName'] = $itemData['like_post_name'];
        $interaction['dislikeSetting'] = $itemData['dislike_post_setting'];
        $interaction['dislikeName'] = $itemData['dislike_post_name'];
        $interaction['followSetting'] = $itemData['follow_post_setting'];
        $interaction['followName'] = $itemData['follow_post_name'];
        $interaction['blockSetting'] = $itemData['block_post_setting'];
        $interaction['blockName'] = $itemData['block_post_name'];
        $interaction['publishPostName'] = $itemData['publish_post_name'];
        $interaction['publishCommentName'] = $itemData['publish_comment_name'];

        return $interaction;
    }

    public static function fresnsCommentInteraction(?string $langTag = null): array
    {
        $itemData = ConfigHelper::fresnsConfigByItemKeys([
            'comment_name',
            'like_comment_setting', 'like_comment_name',
            'dislike_comment_setting', 'dislike_comment_name',
            'follow_comment_setting', 'follow_comment_name',
            'block_comment_setting', 'block_comment_name',
            'publish_post_name', 'publish_comment_name',
        ], $langTag);

        $interaction['commentName'] = $itemData['comment_name'];
        $interaction['likeSetting'] = $itemData['like_comment_setting'];
        $interaction['likeName'] = $itemData['like_comment_name'];
        $interaction['dislikeSetting'] = $itemData['dislike_comment_setting'];
        $interaction['dislikeName'] = $itemData['dislike_comment_name'];
        $interaction['followSetting'] = $itemData['follow_comment_setting'];
        $interaction['followName'] = $itemData['follow_comment_name'];
        $interaction['blockSetting'] = $itemData['block_comment_setting'];
        $interaction['blockName'] = $itemData['block_comment_name'];
        $interaction['publishPostName'] = $itemData['publish_post_name'];
        $interaction['publishCommentName'] = $itemData['publish_comment_name'];

        return $interaction;
    }

    // user substitution profile
    public static function fresnsUserSubstitutionProfile(?string $type = null): array
    {
        $avatar = match ($type) {
            'anonymous' => ConfigHelper::fresnsConfigFileUrlByItemKey('anonymous_avatar', 'imageSquareUrl'),
            'deactivate' => ConfigHelper::fresnsConfigFileUrlByItemKey('deactivate_avatar', 'imageSquareUrl'),
            default => ConfigHelper::fresnsConfigFileUrlByItemKey('anonymous_avatar', 'imageSquareUrl'),
        };

        $status = match ($type) {
            'anonymous' => true,
            'deactivate' => false,
            default => true,
        };

        $profile = [
            'fsid' => null,
            'uid' => null,
            'username' => null,
            'url' => null,
            'nickname' => null,
            'avatar' => $avatar,
            'decorate' => null,
            'banner' => null,
            'gender' => 1,
            'birthday' => null,
            'bio' => null,
            'bioHtml' => null,
            'location' => null,
            'conversationLimit' => 1,
            'commentLimit' => 1,
            'timezone' => null,
            'verifiedStatus' => false,
            'verifiedIcon' => null,
            'verifiedDesc' => null,
            'verifiedDateTime' => null,
            'expired' => false,
            'expiryDateTime' => null,
            'lastPublishPost' => null,
            'lastPublishComment' => null,
            'lastEditUsername' => null,
            'lastEditNickname' => null,
            'registerDate' => null,
            'hasPassword' => false,
            'rankState' => 1,
            'status' => $status,
            'waitDelete' => false,
            'waitDeleteDateTime' => null,

            'nicknameColor' => null,
            'rid' => null,
            'roleName' => null,
            'roleNameDisplay' => false,
            'roleIcon' => null,
            'roleIconDisplay' => false,
            'roleExpiryDateTime' => null,
            'roleRankState' => 1,
            'rolePermissions' => [],
            'roleStatus' => true,

            'stats' => null,
            'archives' => [],
            'operations' => [
                'customizes' => [],
                'buttonIcons' => [],
                'diversifyImages' => [],
                'tips' => [],
            ],
            'extends' => [
                'textBox' => [],
                'infoBox' => [],
                'interactionBox' => [],
            ],
        ];

        return $profile;
    }

    // group count
    public static function fresnsGroupCount(): int
    {
        $cacheKey = 'fresns_group_count';
        $cacheTag = 'fresnsGroups';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return 0;
        }

        $groupCount = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($groupCount)) {
            $groupCount = Group::count();

            CacheHelper::put($groupCount, $cacheKey, $cacheTag);
        }

        return $groupCount;
    }
}
