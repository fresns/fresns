<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\StrHelper;
use App\Models\Role;
use App\Models\UserRole;
use App\Utilities\PermissionUtility;

trait UserServiceTrait
{
    public function getUserProfile(): array
    {
        $userData = $this;

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'user_identifier',
            'website_user_detail_path',
            'site_url',
            'site_mode',
        ]);

        $siteUrl = $configKeys['site_url'] ?? config('app.url');

        $expired = false;
        if ($configKeys['site_mode'] == 'private') {
            $checkUserRolePrivateWhitelist = PermissionUtility::checkUserRolePrivateWhitelist($userData->id);

            if ($checkUserRolePrivateWhitelist) {
                $expired = false;
            } elseif (empty($userData->expired_at)) {
                $expired = true;
            } else {
                $now = time();
                $expireTime = strtotime($userData->expired_at);

                $expired = ($expireTime < $now) ? true : false;
            }
        }

        $fsid = $configKeys['user_identifier'] == 'uid' ? $userData->uid : $userData->username;

        $profile['fsid'] = $fsid;
        $profile['uid'] = $userData->uid;
        $profile['username'] = $userData->username;
        $profile['url'] = $siteUrl.'/'.$configKeys['website_user_detail_path'].'/'.$fsid;
        $profile['nickname'] = $userData->nickname;
        $profile['avatar'] = $userData->getUserAvatar();
        $profile['decorate'] = null;
        $profile['banner'] = FileHelper::fresnsFileUrlByTableColumn($userData->banner_file_id, $userData->banner_file_url);
        $profile['hasPin'] = (bool) $userData->pin;
        $profile['gender'] = $userData->gender;
        $profile['genderPronoun'] = $userData->gender_pronoun;
        $profile['genderCustom'] = $userData->gender_custom;
        $profile['birthday'] = $userData->birthday;
        $profile['bio'] = $userData->bio;
        $profile['location'] = $userData->location;
        $profile['conversationLimit'] = $userData->conversation_limit;
        $profile['commentLimit'] = $userData->comment_limit;
        $profile['contentLimit'] = $userData->content_limit;
        $profile['verified'] = (bool) $userData->verified_status;
        $profile['verifiedIcon'] = null;
        $profile['verifiedDesc'] = $userData->verified_desc;
        $profile['verifiedDateTime'] = $userData->verified_at;
        $profile['expired'] = $expired;
        $profile['expiryDateTime'] = $userData->expired_at;
        $profile['lastPublishPost'] = $userData->last_post_at;
        $profile['lastPublishComment'] = $userData->last_comment_at;
        $profile['lastEditUsername'] = $userData->last_username_at;
        $profile['lastEditNickname'] = $userData->last_nickname_at;
        $profile['registerDate'] = $userData->created_at;
        $profile['rankState'] = $userData->rank_state;
        $profile['status'] = (bool) $userData->is_enabled;
        $profile['waitDelete'] = (bool) $userData->wait_delete;
        $profile['waitDeleteDateTime'] = $userData->wait_delete_at;
        $profile['moreInfo'] = $userData->more_info;

        return $profile;
    }

    public function getUserAvatar(): ?string
    {
        $userData = $this;

        if ($userData->wait_delete || ! $userData->is_enabled) {
            // user deactivate avatar
            $userAvatar = ConfigHelper::fresnsConfigFileUrlByItemKey('deactivate_avatar', 'imageSquareUrl');
        } else {
            if (empty($userData->avatar_file_url) && empty($userData->avatar_file_id)) {
                // default avatar
                $userAvatar = ConfigHelper::fresnsConfigFileUrlByItemKey('default_avatar', 'imageSquareUrl');
            } else {
                // user avatar
                $userAvatar = FileHelper::fresnsFileUrlByTableColumn($userData->avatar_file_id, $userData->avatar_file_url, 'imageSquareUrl');
            }
        }

        return $userAvatar;
    }

    public function getUserMainRole(?string $langTag = null): array
    {
        $userData = $this;

        $mainRoleData = UserRole::with('roleInfo')->where('user_id', $userData->id)->where('is_main', 1)->first();
        $roleData = $mainRoleData?->roleInfo;

        if (empty($mainRoleData)) {
            $defaultRoleId = ConfigHelper::fresnsConfigByItemKey('default_role');

            $roleData = Role::where('id', $defaultRoleId)->first();
        }

        $mainRole['nicknameColor'] = $roleData?->nickname_color;
        $mainRole['roleRid'] = $roleData?->rid;
        $mainRole['roleName'] = StrHelper::languageContent($roleData?->name, $langTag);
        $mainRole['roleNameDisplay'] = (bool) $roleData?->is_display_name;
        $mainRole['roleIcon'] = FileHelper::fresnsFileUrlByTableColumn($roleData?->icon_file_id, $roleData?->icon_file_url);
        $mainRole['roleIconDisplay'] = (bool) $roleData?->is_display_icon;
        $mainRole['roleExpiryDateTime'] = $mainRoleData?->expired_at;
        $mainRole['roleRankState'] = $roleData?->rank_state;
        $mainRole['roleStatus'] = (bool) $roleData?->is_enabled;

        return $mainRole;
    }

    public function getUserRoles(?string $langTag = null): array
    {
        $userData = $this;

        $roleArr = UserRole::with('roleInfo')->where('user_id', $userData->id)->get();

        $roles = [];
        foreach ($roleArr as $role) {
            $roleInfo = $role?->roleInfo;

            if (empty($roleInfo)) {
                continue;
            }

            $item['rid'] = $roleInfo->rid;
            $item['isMain'] = (bool) $role->is_main;
            $item['nicknameColor'] = $roleInfo->nickname_color;
            $item['name'] = StrHelper::languageContent($roleInfo->name, $langTag);
            $item['nameDisplay'] = (bool) $roleInfo->is_display_name;
            $item['icon'] = FileHelper::fresnsFileUrlByTableColumn($roleInfo->icon_file_id, $roleInfo->icon_file_url);
            $item['iconDisplay'] = (bool) $roleInfo->is_display_icon;
            $item['status'] = (bool) $roleInfo->is_enabled;

            $roles[] = $item;
        }

        return $roles;
    }

    public function getUserStats(?string $langTag = null, ?int $authUserId = null): array
    {
        $statData = $this->stat;

        $isMe = $statData->user_id == $authUserId;

        $config = ConfigHelper::fresnsConfigByItemKeys([
            'profile_posts_enabled', 'profile_comments_enabled',
            'profile_likes_users_enabled', 'profile_likes_groups_enabled', 'profile_likes_hashtags_enabled', 'profile_likes_geotags_enabled', 'profile_likes_posts_enabled', 'profile_likes_comments_enabled',
            'profile_dislikes_users_enabled', 'profile_dislikes_groups_enabled', 'profile_dislikes_hashtags_enabled', 'profile_dislikes_geotags_enabled', 'profile_dislikes_posts_enabled', 'profile_dislikes_comments_enabled',
            'profile_following_users_enabled', 'profile_following_groups_enabled', 'profile_following_hashtags_enabled', 'profile_following_geotags_enabled', 'profile_following_posts_enabled', 'profile_following_comments_enabled',
            'profile_blocking_users_enabled', 'profile_blocking_groups_enabled', 'profile_blocking_hashtags_enabled', 'profile_blocking_geotags_enabled', 'profile_blocking_posts_enabled', 'profile_blocking_comments_enabled',

            'user_like_public_count', 'user_dislike_public_count', 'user_follow_public_count', 'user_block_public_count',
            'post_like_public_count', 'post_dislike_public_count', 'post_follow_public_count', 'post_block_public_count',
            'comment_like_public_count', 'comment_dislike_public_count', 'comment_follow_public_count', 'comment_block_public_count',

            'extcredits1_state', 'extcredits1_name', 'extcredits1_unit',
            'extcredits2_state', 'extcredits2_name', 'extcredits2_unit',
            'extcredits3_state', 'extcredits3_name', 'extcredits3_unit',
            'extcredits4_state', 'extcredits4_name', 'extcredits4_unit',
            'extcredits5_state', 'extcredits5_name', 'extcredits5_unit',
        ], $langTag);

        $stats['likeUserCount'] = $config['profile_likes_users_enabled'] ? $statData->like_user_count : null;
        $stats['likeGroupCount'] = $config['profile_likes_groups_enabled'] ? $statData->like_group_count : null;
        $stats['likeHashtagCount'] = $config['profile_likes_hashtags_enabled'] ? $statData->like_hashtag_count : null;
        $stats['likeGeotagCount'] = $config['profile_likes_geotags_enabled'] ? $statData->like_geotag_count : null;
        $stats['likePostCount'] = $config['profile_likes_posts_enabled'] ? $statData->like_post_count : null;
        $stats['likeCommentCount'] = $config['profile_likes_comments_enabled'] ? $statData->like_comment_count : null;

        $stats['dislikeUserCount'] = $config['profile_dislikes_users_enabled'] ? $statData->dislike_user_count : null;
        $stats['dislikeGroupCount'] = $config['profile_dislikes_groups_enabled'] ? $statData->dislike_group_count : null;
        $stats['dislikeHashtagCount'] = $config['profile_dislikes_hashtags_enabled'] ? $statData->dislike_hashtag_count : null;
        $stats['dislikeGeotagCount'] = $config['profile_dislikes_geotags_enabled'] ? $statData->dislike_geotag_count : null;
        $stats['dislikePostCount'] = $config['profile_dislikes_posts_enabled'] ? $statData->dislike_post_count : null;
        $stats['dislikeCommentCount'] = $config['profile_dislikes_comments_enabled'] ? $statData->dislike_comment_count : null;

        $stats['followUserCount'] = $config['profile_following_users_enabled'] ? $statData->follow_user_count : null;
        $stats['followGroupCount'] = $config['profile_following_groups_enabled'] ? $statData->follow_group_count : null;
        $stats['followHashtagCount'] = $config['profile_following_hashtags_enabled'] ? $statData->follow_hashtag_count : null;
        $stats['followGeotagCount'] = $config['profile_following_geotags_enabled'] ? $statData->follow_geotag_count : null;
        $stats['followPostCount'] = $config['profile_following_posts_enabled'] ? $statData->follow_post_count : null;
        $stats['followCommentCount'] = $config['profile_following_comments_enabled'] ? $statData->follow_comment_count : null;

        $stats['blockUserCount'] = $config['profile_blocking_users_enabled'] ? $statData->block_user_count : null;
        $stats['blockGroupCount'] = $config['profile_blocking_groups_enabled'] ? $statData->block_group_count : null;
        $stats['blockHashtagCount'] = $config['profile_blocking_hashtags_enabled'] ? $statData->block_hashtag_count : null;
        $stats['blockGeotagCount'] = $config['profile_blocking_geotags_enabled'] ? $statData->block_geotag_count : null;
        $stats['blockPostCount'] = $config['profile_blocking_posts_enabled'] ? $statData->block_post_count : null;
        $stats['blockCommentCount'] = $config['profile_blocking_comments_enabled'] ? $statData->block_comment_count : null;

        $stats['viewMeCount'] = $statData->view_me_count;
        $stats['likeMeCount'] = ($config['user_like_public_count'] == 3 || $isMe) ? $statData->like_me_count : null;
        $stats['dislikeMeCount'] = ($config['user_dislike_public_count'] == 3 || $isMe) ? $statData->dislike_me_count : null;
        $stats['followMeCount'] = ($config['user_follow_public_count'] == 3 || $isMe) ? $statData->follow_me_count : null;
        $stats['blockMeCount'] = ($config['user_block_public_count'] == 3 || $isMe) ? $statData->block_me_count : null;

        $stats['postPublishCount'] = $config['profile_posts_enabled'] ? $statData->post_publish_count : null;
        $stats['postDigestCount'] = $config['profile_posts_enabled'] ? $statData->post_digest_count : null;
        $stats['postLikeCount'] = $config['post_like_public_count'] ? $statData->post_like_count : null;
        $stats['postDislikeCount'] = $config['post_dislike_public_count'] ? $statData->post_dislike_count : null;
        $stats['postFollowCount'] = $config['post_follow_public_count'] ? $statData->post_follow_count : null;
        $stats['postBlockCount'] = $config['post_block_public_count'] ? $statData->post_block_count : null;

        $stats['commentPublishCount'] = $config['profile_comments_enabled'] ? $statData->comment_publish_count : null;
        $stats['commentDigestCount'] = $config['profile_comments_enabled'] ? $statData->comment_digest_count : null;
        $stats['commentLikeCount'] = $config['comment_like_public_count'] ? $statData->comment_like_count : null;
        $stats['commentDislikeCount'] = $config['comment_dislike_public_count'] ? $statData->comment_dislike_count : null;
        $stats['commentFollowCount'] = $config['comment_follow_public_count'] ? $statData->comment_follow_count : null;
        $stats['commentBlockCount'] = $config['comment_block_public_count'] ? $statData->comment_block_count : null;

        $stats['extcredits1'] = ($config['extcredits1_state'] == 3 || $isMe) ? $statData->extcredits1 : null;
        $stats['extcredits1State'] = $config['extcredits1_state'];
        $stats['extcredits1Name'] = $config['extcredits1_name'] ?? 'extcredits1';
        $stats['extcredits1Unit'] = $config['extcredits1_unit'];
        $stats['extcredits2'] = ($config['extcredits2_state'] == 3 || $isMe) ? $statData->extcredits2 : null;
        $stats['extcredits2State'] = $config['extcredits2_state'];
        $stats['extcredits2Name'] = $config['extcredits2_name'] ?? 'extcredits2';
        $stats['extcredits2Unit'] = $config['extcredits2_unit'];
        $stats['extcredits3'] = ($config['extcredits3_state'] == 3 || $isMe) ? $statData->extcredits3 : null;
        $stats['extcredits3State'] = $config['extcredits3_state'];
        $stats['extcredits3Name'] = $config['extcredits3_name'] ?? 'extcredits3';
        $stats['extcredits3Unit'] = $config['extcredits3_unit'];
        $stats['extcredits4'] = ($config['extcredits4_state'] == 3 || $isMe) ? $statData->extcredits4 : null;
        $stats['extcredits4State'] = $config['extcredits4_state'];
        $stats['extcredits4Name'] = $config['extcredits4_name'] ?? 'extcredits4';
        $stats['extcredits4Unit'] = $config['extcredits4_unit'];
        $stats['extcredits5'] = ($config['extcredits5_state'] == 3 || $isMe) ? $statData->extcredits5 : null;
        $stats['extcredits5State'] = $config['extcredits5_state'];
        $stats['extcredits5Name'] = $config['extcredits5_name'] ?? 'extcredits5';
        $stats['extcredits5Unit'] = $config['extcredits5_unit'];

        return $stats;
    }
}
