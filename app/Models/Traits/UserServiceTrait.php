<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;

trait UserServiceTrait
{
    public function getUserProfile()
    {
        $userData = $this;

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'user_identifier',
            'website_user_detail_path',
            'site_url',
            'site_mode',
        ]);

        if ($configKeys['user_identifier'] == 'uid') {
            $profile['fsid'] = $userData->uid;
            $url = $configKeys['site_url'].'/'.$configKeys['website_user_detail_path'].'/'.$userData->uid;
        } else {
            $profile['fsid'] = $userData->username;
            $url = $configKeys['site_url'].'/'.$configKeys['website_user_detail_path'].'/'.$userData->username;
        }

        $isExpiry = false;
        if ($configKeys['site_mode'] == 'private') {
            if (empty($userData->expired_at)) {
                $isExpiry = true;
            } else {
                $now = time();
                $expireTime = strtotime($userData->expired_at);

                $isExpiry = ($expireTime < $now) ? true : false;
            }
        }

        $profile['uid'] = $userData->uid;
        $profile['username'] = $userData->username;
        $profile['url'] = $url;
        $profile['nickname'] = $userData->nickname;
        $profile['avatar'] = static::getUserAvatar($userData->id);
        $profile['decorate'] = null;
        $profile['banner'] = FileHelper::fresnsFileUrlByTableColumn($userData->banner_file_id, $userData->banner_file_url);
        $profile['gender'] = $userData->gender;
        $profile['birthday'] = $userData->birthday;
        $profile['bio'] = $userData->bio;
        $profile['location'] = $userData->location;
        $profile['conversationLimit'] = $userData->conversation_limit;
        $profile['commentLimit'] = $userData->comment_limit;
        $profile['timezone'] = $userData->timezone ?? ConfigHelper::fresnsConfigByItemKey('default_timezone');
        $profile['verifiedStatus'] = (bool) $userData->verified_status;
        $profile['verifiedIcon'] = null;
        $profile['verifiedDesc'] = $userData->verified_desc;
        $profile['verifiedDateTime'] = $userData->verified_at;
        $profile['isExpiry'] = $isExpiry;
        $profile['expiryDateTime'] = $userData->expired_at;
        $profile['lastPublishPost'] = $userData->last_post_at;
        $profile['lastPublishComment'] = $userData->last_comment_at;
        $profile['lastEditUsername'] = $userData->last_username_at;
        $profile['lastEditNickname'] = $userData->last_nickname_at;
        $profile['registerDate'] = $userData->created_at;
        $profile['hasPassword'] = (bool) $userData->password;
        $profile['rankState'] = $userData->rank_state;
        $profile['status'] = (bool) $userData->is_enable;
        $profile['waitDelete'] = (bool) $userData->wait_delete;
        $profile['waitDeleteDateTime'] = $userData->wait_delete_at;
        $profile['deactivate'] = (bool) $userData->deleted_at;
        $profile['deactivateTime'] = $userData->deleted_at;

        return $profile;
    }

    public static function getUserAvatar(int $userId)
    {
        $user = User::where('id', $userId)->first(['avatar_file_id', 'avatar_file_url', 'wait_delete']);

        $avatar = ConfigHelper::fresnsConfigByItemKeys([
            'default_avatar',
            'deactivate_avatar',
        ]);

        if ($user->wait_delete == 0) {
            if (empty($user->avatar_file_url) && empty($user->avatar_file_id)) {
                // default avatar
                if (ConfigHelper::fresnsConfigFileValueTypeByItemKey('default_avatar') == 'URL') {
                    $userAvatar = $avatar['default_avatar'];
                } else {
                    $fileInfo = FileHelper::fresnsFileInfoById($avatar['default_avatar']);
                    $userAvatar = $fileInfo['imageAvatarUrl'];
                }
            } else {
                // user avatar
                $userAvatar = FileHelper::fresnsFileUrlByTableColumn($user->avatar_file_id, $user->avatar_file_url, 'imageAvatarUrl');
            }
        } else {
            // user deactivate avatar
            if (ConfigHelper::fresnsConfigFileValueTypeByItemKey('deactivate_avatar') == 'URL') {
                $userAvatar = $avatar['deactivate_avatar'];
            } else {
                $fileInfo = FileHelper::fresnsFileInfoById($avatar['deactivate_avatar']);
                $userAvatar = $fileInfo['imageAvatarUrl'];
            }
        }

        return $userAvatar;
    }

    public function getUserMainRole(?string $langTag = null)
    {
        $userData = $this;

        $mainRoleData = UserRole::where('user_id', $userData->id)->where('is_main', 1)->first();
        $roleData = Role::where('id', $mainRoleData?->role_id)->first();

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
        $mainRole['roleName'] = $roleData?->id ? LanguageHelper::fresnsLanguageByTableId('roles', 'name', $roleData?->id, $langTag) : null;
        $mainRole['roleNameDisplay'] = (bool) $roleData?->is_display_name;
        $mainRole['roleIcon'] = FileHelper::fresnsFileUrlByTableColumn($roleData?->icon_file_id, $roleData?->icon_file_url);
        $mainRole['roleIconDisplay'] = (bool) $roleData?->is_display_icon;
        $mainRole['roleExpiryDateTime'] = $mainRoleData?->expired_at;
        $mainRole['roleRankState'] = $roleData?->rank_state;
        $mainRole['rolePermissions'] = $permission;
        $mainRole['roleStatus'] = (bool) $roleData?->is_enable;

        return $mainRole;
    }

    public function getUserRoles(?string $langTag = null)
    {
        $userData = $this;

        $roleArr = UserRole::where('user_id', $userData->id)->get();

        $roles = [];
        foreach ($roleArr as $role) {
            $item['id'] = $role->id;
            $item['rid'] = $role->roleInfo->id;
            $item['name'] = $role->roleInfo->getLangName($langTag);
            $item['isMain'] = (bool) $role->is_main;
            $item['expiryDateTime'] = $role->expired_at;
            $item['restoreRoleId'] = $role->restore_role_id;
            $item['restoreRoleName'] = $role?->restoreRole?->getLangName($langTag);

            $roles[] = $item;
        }

        return $roles;
    }

    public function getUserStats(?string $langTag = null)
    {
        $statData = $this->stat;

        $statConfig = ConfigHelper::fresnsConfigByItemKeys([
            'extcredits1_status', 'extcredits1_name', 'extcredits1_unit',
            'extcredits2_status', 'extcredits2_name', 'extcredits2_unit',
            'extcredits3_status', 'extcredits3_name', 'extcredits3_unit',
            'extcredits4_status', 'extcredits4_name', 'extcredits4_unit',
            'extcredits5_status', 'extcredits5_name', 'extcredits5_unit',

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

        $stats['extcredits1'] = ($statConfig['extcredits1_status'] != 1) ? $statData->extcredits1 : null;
        $stats['extcredits1Status'] = $statConfig['extcredits1_status'];
        $stats['extcredits1Name'] = $statConfig['extcredits1_name'];
        $stats['extcredits1Unit'] = $statConfig['extcredits1_unit'];
        $stats['extcredits2'] = ($statConfig['extcredits2_status'] != 1) ? $statData->extcredits2 : null;
        $stats['extcredits2Status'] = $statConfig['extcredits2_status'];
        $stats['extcredits2Name'] = $statConfig['extcredits2_name'];
        $stats['extcredits2Unit'] = $statConfig['extcredits2_unit'];
        $stats['extcredits3'] = ($statConfig['extcredits3_status'] != 1) ? $statData->extcredits3 : null;
        $stats['extcredits3Status'] = $statConfig['extcredits3_status'];
        $stats['extcredits3Name'] = $statConfig['extcredits3_name'];
        $stats['extcredits3Unit'] = $statConfig['extcredits3_unit'];
        $stats['extcredits4'] = ($statConfig['extcredits4_status'] != 1) ? $statData->extcredits4 : null;
        $stats['extcredits4Status'] = $statConfig['extcredits4_status'];
        $stats['extcredits4Name'] = $statConfig['extcredits4_name'];
        $stats['extcredits4Unit'] = $statConfig['extcredits4_unit'];
        $stats['extcredits5'] = ($statConfig['extcredits5_status'] != 1) ? $statData->extcredits5 : null;
        $stats['extcredits5Status'] = $statConfig['extcredits5_status'];
        $stats['extcredits5Name'] = $statConfig['extcredits5_name'];
        $stats['extcredits5Unit'] = $statConfig['extcredits5_unit'];

        return $stats;
    }
}
