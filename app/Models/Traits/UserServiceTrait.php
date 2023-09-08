<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
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

        if ($configKeys['user_identifier'] == 'uid') {
            $profile['fsid'] = $userData->uid;
            $url = $siteUrl.'/'.$configKeys['website_user_detail_path'].'/'.$userData->uid;
        } else {
            $profile['fsid'] = $userData->username;
            $url = $siteUrl.'/'.$configKeys['website_user_detail_path'].'/'.$userData->username;
        }

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

        $profile['uid'] = $userData->uid;
        $profile['username'] = $userData->username;
        $profile['url'] = $url;
        $profile['nickname'] = $userData->nickname;
        $profile['avatar'] = $userData->getUserAvatar();
        $profile['decorate'] = null;
        $profile['banner'] = FileHelper::fresnsFileUrlByTableColumn($userData->banner_file_id, $userData->banner_file_url);
        $profile['gender'] = $userData->gender;
        $profile['birthday'] = $userData->birthday;
        $profile['bio'] = $userData->bio;
        $profile['location'] = $userData->location;
        $profile['conversationLimit'] = $userData->conversation_limit;
        $profile['commentLimit'] = $userData->comment_limit;
        $profile['verifiedStatus'] = (bool) $userData->verified_status;
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
        $profile['hasPassword'] = (bool) $userData->password;
        $profile['rankState'] = $userData->rank_state;
        $profile['status'] = (bool) $userData->is_enabled;
        $profile['waitDelete'] = (bool) $userData->wait_delete;
        $profile['waitDeleteDateTime'] = $userData->wait_delete_at;

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
        $roleData = $mainRoleData->roleInfo;

        if (empty($mainRoleData)) {
            $defaultRoleId = ConfigHelper::fresnsConfigByItemKey('default_role');

            $roleData = Role::where('id', $defaultRoleId)->first();
        }

        $permissions = $roleData?->permissions ?? [];
        foreach ($permissions as $perm) {
            $permission['rid'] = $roleData->id;
            $permission[$perm['permKey']] = $perm['permValue'];
        }

        $mainRole['nicknameColor'] = $roleData?->nickname_color;
        $mainRole['rid'] = $roleData?->id;
        $mainRole['roleName'] = $roleData?->id ? (LanguageHelper::fresnsLanguageByTableId('roles', 'name', $roleData?->id, $langTag) ?? $roleData?->name) : null;
        $mainRole['roleNameDisplay'] = (bool) $roleData?->is_display_name;
        $mainRole['roleIcon'] = FileHelper::fresnsFileUrlByTableColumn($roleData?->icon_file_id, $roleData?->icon_file_url);
        $mainRole['roleIconDisplay'] = (bool) $roleData?->is_display_icon;
        $mainRole['roleExpiryDateTime'] = $mainRoleData?->expired_at;
        $mainRole['roleRankState'] = $roleData?->rank_state;
        $mainRole['rolePermissions'] = $permission;
        $mainRole['roleStatus'] = (bool) $roleData?->is_enabled;

        return $mainRole;
    }

    public function getUserRoles(?string $langTag = null): array
    {
        $userData = $this;

        $roleArr = UserRole::where('user_id', $userData->id)->get();

        $roles = [];
        foreach ($roleArr as $role) {
            $item['id'] = $role->id;
            $item['rid'] = $role->role_id;
            $item['name'] = $role->roleInfo?->getLangName($langTag);
            $item['isMain'] = (bool) $role->is_main;
            $item['expiryDateTime'] = $role->expired_at;
            $item['restoreRoleId'] = $role->restore_role_id;
            $item['restoreRoleName'] = $role->restoreRole?->getLangName($langTag);

            $roles[] = $item;
        }

        return $roles;
    }

    public function getUserStats(?string $langTag = null): array
    {
        $statData = $this->stat;

        $statConfig = ConfigHelper::fresnsConfigByItemKeys([
            'extcredits1_state', 'extcredits1_name', 'extcredits1_unit',
            'extcredits2_state', 'extcredits2_name', 'extcredits2_unit',
            'extcredits3_state', 'extcredits3_name', 'extcredits3_unit',
            'extcredits4_state', 'extcredits4_name', 'extcredits4_unit',
            'extcredits5_state', 'extcredits5_name', 'extcredits5_unit',

            'post_liker_count', 'post_disliker_count', 'post_follower_count', 'post_blocker_count',
            'comment_liker_count', 'comment_disliker_count', 'comment_follower_count', 'comment_blocker_count',
        ], $langTag);

        $stats['likeUserCount'] = $statData->like_user_count;
        $stats['likeGroupCount'] = $statData->like_group_count;
        $stats['likeHashtagCount'] = $statData->like_hashtag_count;
        $stats['likePostCount'] = $statData->like_post_count;
        $stats['likeCommentCount'] = $statData->like_comment_count;

        $stats['dislikeUserCount'] = $statData->dislike_user_count;
        $stats['dislikeGroupCount'] = $statData->dislike_group_count;
        $stats['dislikeHashtagCount'] = $statData->dislike_hashtag_count;
        $stats['dislikePostCount'] = $statData->dislike_post_count;
        $stats['dislikeCommentCount'] = $statData->dislike_comment_count;

        $stats['followUserCount'] = $statData->follow_user_count;
        $stats['followGroupCount'] = $statData->follow_group_count;
        $stats['followHashtagCount'] = $statData->follow_hashtag_count;
        $stats['followPostCount'] = $statData->follow_post_count;
        $stats['followCommentCount'] = $statData->follow_comment_count;

        $stats['blockUserCount'] = $statData->block_user_count;
        $stats['blockGroupCount'] = $statData->block_group_count;
        $stats['blockHashtagCount'] = $statData->block_hashtag_count;
        $stats['blockPostCount'] = $statData->block_post_count;
        $stats['blockCommentCount'] = $statData->block_comment_count;

        $stats['viewMeCount'] = $statData->view_me_count;
        $stats['likeMeCount'] = $statData->like_me_count;
        $stats['dislikeMeCount'] = $statData->dislike_me_count;
        $stats['followMeCount'] = $statData->follow_me_count;
        $stats['blockMeCount'] = $statData->block_me_count;

        $stats['postPublishCount'] = $statData->post_publish_count;
        $stats['postDigestCount'] = $statData->post_digest_count;
        $stats['postLikeCount'] = $statConfig['post_liker_count'] ? $statData->post_like_count : null;
        $stats['postDislikeCount'] = $statConfig['post_disliker_count'] ? $statData->post_dislike_count : null;
        $stats['postFollowCount'] = $statConfig['post_follower_count'] ? $statData->post_follow_count : null;
        $stats['postBlockCount'] = $statConfig['post_blocker_count'] ? $statData->post_block_count : null;

        $stats['commentPublishCount'] = $statData->comment_publish_count;
        $stats['commentDigestCount'] = $statData->comment_digest_count;
        $stats['commentLikeCount'] = $statConfig['comment_liker_count'] ? $statData->comment_like_count : null;
        $stats['commentDislikeCount'] = $statConfig['comment_disliker_count'] ? $statData->comment_dislike_count : null;
        $stats['commentFollowCount'] = $statConfig['comment_follower_count'] ? $statData->comment_follow_count : null;
        $stats['commentBlockCount'] = $statConfig['comment_blocker_count'] ? $statData->comment_block_count : null;

        $stats['extcredits1'] = ($statConfig['extcredits1_state'] != 1) ? $statData->extcredits1 : null;
        $stats['extcredits1State'] = $statConfig['extcredits1_state'];
        $stats['extcredits1Name'] = $statConfig['extcredits1_name'] ?? 'extcredits1';
        $stats['extcredits1Unit'] = $statConfig['extcredits1_unit'];
        $stats['extcredits2'] = ($statConfig['extcredits2_state'] != 1) ? $statData->extcredits2 : null;
        $stats['extcredits2State'] = $statConfig['extcredits2_state'];
        $stats['extcredits2Name'] = $statConfig['extcredits2_name'] ?? 'extcredits2';
        $stats['extcredits2Unit'] = $statConfig['extcredits2_unit'];
        $stats['extcredits3'] = ($statConfig['extcredits3_state'] != 1) ? $statData->extcredits3 : null;
        $stats['extcredits3State'] = $statConfig['extcredits3_state'];
        $stats['extcredits3Name'] = $statConfig['extcredits3_name'] ?? 'extcredits3';
        $stats['extcredits3Unit'] = $statConfig['extcredits3_unit'];
        $stats['extcredits4'] = ($statConfig['extcredits4_state'] != 1) ? $statData->extcredits4 : null;
        $stats['extcredits4State'] = $statConfig['extcredits4_state'];
        $stats['extcredits4Name'] = $statConfig['extcredits4_name'] ?? 'extcredits4';
        $stats['extcredits4Unit'] = $statConfig['extcredits4_unit'];
        $stats['extcredits5'] = ($statConfig['extcredits5_state'] != 1) ? $statData->extcredits5 : null;
        $stats['extcredits5State'] = $statConfig['extcredits5_state'];
        $stats['extcredits5Name'] = $statConfig['extcredits5_name'] ?? 'extcredits5';
        $stats['extcredits5Unit'] = $statConfig['extcredits5_unit'];

        return $stats;
    }
}
