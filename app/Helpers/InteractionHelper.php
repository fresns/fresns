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
use App\Models\Geotag;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Utilities\ArrUtility;

class InteractionHelper
{
    public static function fresnsOverview(): array
    {
        $overview['accountCount'] = Account::count();
        $overview['userCount'] = User::count();
        $overview['groupCount'] = Group::count();
        $overview['hashtagCount'] = Hashtag::count();
        $overview['geotagCount'] = Geotag::count();
        $overview['postCount'] = Post::count();
        $overview['commentCount'] = Comment::count();
        $overview['postDigest1Count'] = Post::where('digest_state', Post::DIGEST_GENERAL)->count();
        $overview['postDigest2Count'] = Post::where('digest_state', Post::DIGEST_PREMIUM)->count();
        $overview['commentDigest1Count'] = Comment::where('digest_state', Comment::DIGEST_GENERAL)->count();
        $overview['commentDigest2Count'] = Comment::where('digest_state', Comment::DIGEST_PREMIUM)->count();

        return $overview;
    }

    public static function fresnsRoleInfo(int $id, ?string $langTag = null): array
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_role_{$id}";
        $cacheTag = 'fresnsConfigs';

        $roleData = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($roleData)) {
            $roleModel = Role::where('id', $id)->first();

            if (empty($roleModel)) {
                return null;
            }

            foreach ($roleModel->permissions as $perm) {
                $permission[$perm['permKey']] = $perm['permValue'];
            }

            $item['id'] = $roleModel->id;
            $item['rid'] = $roleModel->rid;
            $item['isMain'] = false;
            $item['nicknameColor'] = $roleModel->nickname_color;
            $item['name'] = $roleModel->name;
            $item['nameDisplay'] = (bool) $roleModel->is_display_name;
            $item['icon'] = FileHelper::fresnsFileUrlByTableColumn($roleModel->icon_file_id, $roleModel->icon_file_url);
            $item['iconDisplay'] = (bool) $roleModel->is_display_icon;
            $item['rankState'] = $roleModel->rank_state;
            $item['permissions'] = $permission;
            $item['status'] = (bool) $roleModel->is_enabled;

            $roleData = $item;

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($roleData, $cacheKey, $cacheTag, $cacheTime);
        }

        $roleData['name'] = StrHelper::languageContent($roleData['name'], $langTag);

        return $roleData;
    }

    public static function fresnsInteraction(string $type, ?string $langTag = null, ?int $followMethod = 1, ?string $followAppFskey = null): array
    {
        $itemData = ConfigHelper::fresnsConfigByItemKeys([
            "{$type}_like_enabled", "{$type}_like_name", "{$type}_like_user_title", "{$type}_like_public_record", "{$type}_like_public_count",
            "{$type}_dislike_enabled", "{$type}_dislike_name", "{$type}_dislike_user_title", "{$type}_dislike_public_record", "{$type}_dislike_public_count",
            "{$type}_follow_enabled", "{$type}_follow_name", "{$type}_follow_user_title", "{$type}_follow_public_record", "{$type}_follow_public_count",
            "{$type}_block_enabled", "{$type}_block_name", "{$type}_block_user_title", "{$type}_block_public_record", "{$type}_block_public_count",
        ], $langTag);

        $interaction['likeEnabled'] = $itemData["{$type}_like_enabled"];
        $interaction['likeName'] = $itemData["{$type}_like_name"];
        $interaction['likeUserTitle'] = $itemData["{$type}_like_user_title"];
        $interaction['likePublicRecord'] = $itemData["{$type}_like_public_record"];
        $interaction['likePublicCount'] = $itemData["{$type}_like_public_count"];
        $interaction['dislikeEnabled'] = $itemData["{$type}_dislike_enabled"];
        $interaction['dislikeName'] = $itemData["{$type}_dislike_name"];
        $interaction['dislikeUserTitle'] = $itemData["{$type}_dislike_user_title"];
        $interaction['dislikePublicRecord'] = $itemData["{$type}_dislike_public_record"];
        $interaction['dislikePublicCount'] = $itemData["{$type}_dislike_public_count"];
        $interaction['followEnabled'] = $itemData["{$type}_follow_enabled"];
        $interaction['followName'] = $itemData["{$type}_follow_name"];
        $interaction['followUserTitle'] = $itemData["{$type}_follow_user_title"];
        $interaction['followPublicRecord'] = $itemData["{$type}_follow_public_record"];
        $interaction['followPublicCount'] = $itemData["{$type}_follow_public_count"];
        $interaction['blockEnabled'] = $itemData["{$type}_block_enabled"];
        $interaction['blockName'] = $itemData["{$type}_block_name"];
        $interaction['blockUserTitle'] = $itemData["{$type}_block_user_title"];
        $interaction['blockPublicRecord'] = $itemData["{$type}_block_public_record"];
        $interaction['blockPublicCount'] = $itemData["{$type}_block_public_count"];
        $interaction['likeStatus'] = false;
        $interaction['dislikeStatus'] = false;
        $interaction['followStatus'] = false;
        $interaction['blockStatus'] = false;
        $interaction['note'] = null;

        switch ($type) {
            case 'user':
                $interaction['followMeStatus'] = false;
                $interaction['blockMeStatus'] = false;

                $interaction['followMethod'] = 'api';
                $interaction['followAppUrl'] = PluginHelper::fresnsPluginUrlByFskey($followAppFskey);
                $interaction['followExpired'] = false;
                $interaction['followExpiryDateTime'] = null;
                break;

            case 'group':
                $interaction['followEnabled'] = ($followMethod == Group::FOLLOW_METHOD_CLOSE) ? false : $itemData["{$type}_follow_enabled"];
                $interaction['followMethod'] = match ($followMethod) {
                    Group::FOLLOW_METHOD_API => 'api',
                    Group::FOLLOW_METHOD_PLUGIN => 'page',
                    default => null,
                };
                $interaction['followAppUrl'] = PluginHelper::fresnsPluginUrlByFskey($followAppFskey);
                $interaction['followExpired'] = false;
                $interaction['followExpiryDateTime'] = null;
                break;

            case 'comment':
                $item['postAuthorLikeStatus'] = false;
                break;
        }

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

    // user substitution profile
    public static function fresnsUserSubstitutionProfile(?string $type = null, ?string $filterType = null, ?string $filterKeys = null): array
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
            'hasPin' => false,
            'gender' => 1,
            'genderCustom' => null,
            'genderPronoun' => null,
            'birthday' => null,
            'bio' => null,
            'bioHtml' => null,
            'location' => null,
            'conversationPolicy' => 1,
            'commentPolicy' => 1,
            'verified' => false,
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
            'rankState' => 1,
            'status' => $status,
            'waitDelete' => false,
            'waitDeleteDateTime' => null,
            'moreInfo' => [],

            'archives' => [],
            'operations' => [
                'customizes' => [],
                'buttonIcons' => [],
                'diversifyImages' => [],
                'tips' => [],
            ],
            'extends' => [
                'texts' => [],
                'infos' => [],
                'actions' => [],
            ],

            'nicknameColor' => null,
            'roleRid' => null,
            'roleName' => null,
            'roleNameDisplay' => false,
            'roleIcon' => null,
            'roleIconDisplay' => false,
            'roleExpiryDateTime' => null,
            'roleRankState' => 1,
            'roleStatus' => true,

            'stats' => null,

            'roles' => [],
        ];

        // filter
        $filterKeysArr = $filterKeys ? array_filter(explode(',', $filterKeys)) : [];

        if ($filterType && $filterKeysArr) {
            return ArrUtility::filter($profile, $filterType, $filterKeysArr);
        }

        return $profile;
    }
}
