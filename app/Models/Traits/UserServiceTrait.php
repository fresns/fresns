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

        $mainRoleData = UserRole::where('user_id', $userData->id)->where('is_main', 1)->first(['role_id', 'expired_at']);
        $roleData = Role::where('id', $mainRoleData->role_id)->first();

        foreach ($roleData->permissions as $perm) {
            $permission['rid'] = $roleData->id;
            $permission[$perm['permKey']] = $perm['permValue'];
        }

        $mainRole['nicknameColor'] = $roleData->nickname_color;
        $mainRole['rid'] = $roleData->id;
        $mainRole['roleName'] = LanguageHelper::fresnsLanguageByTableId('roles', 'name', $roleData->id, $langTag);
        $mainRole['roleNameDisplay'] = (bool) $roleData->is_display_name;
        $mainRole['roleIcon'] = FileHelper::fresnsFileUrlByTableColumn($roleData->icon_file_id, $roleData->icon_file_url);
        $mainRole['roleIconDisplay'] = (bool) $roleData->is_display_icon;
        $mainRole['roleExpiryDateTime'] = $mainRoleData->expired_at;
        $mainRole['roleRankState'] = $roleData->rank_state;
        $mainRole['rolePermissions'] = $permission;
        $mainRole['roleStatus'] = (bool) $roleData->is_enable;

        return $mainRole;
    }

    public function getUserRoles(?string $langTag = null)
    {
        $userData = $this;

        $userRoleArr = UserRole::where('user_id', $userData->id)->get()->toArray();
        $roleArr = Role::whereIn('id', array_column($userRoleArr, 'role_id'))->get();

        $roleList = [];
        foreach ($roleArr as $role) {
            foreach ($userRoleArr as $userRole) {
                if ($userRole['role_id'] !== $role['id']) {
                    continue;
                }
                $item['rid'] = $role['id'];
                $item['isMain'] = (bool) $userRole['is_main'];
                $item['nicknameColor'] = $role['nickname_color'];
                $item['name'] = LanguageHelper::fresnsLanguageByTableId('roles', 'name', $role['id'], $langTag);
                $item['nameDisplay'] = (bool) $role['is_display_name'];
                $item['icon'] = FileHelper::fresnsFileUrlByTableColumn($role['icon_file_id'], $role['icon_file_url']);
                $item['iconDisplay'] = (bool) $role['is_display_icon'];
                $item['status'] = (bool) $role['is_enable'];
            }
            $roleList[] = $item;
        }

        return $roleList;
    }

    public function getUserStats(?string $langTag = null)
    {
        $statsData = $this->stat;

        $statConfig = ConfigHelper::fresnsConfigByItemKeys([
            'extcredits1_status', 'extcredits1_name', 'extcredits1_unit',
            'extcredits2_status', 'extcredits2_name', 'extcredits2_unit',
            'extcredits3_status', 'extcredits3_name', 'extcredits3_unit',
            'extcredits4_status', 'extcredits4_name', 'extcredits4_unit',
            'extcredits5_status', 'extcredits5_name', 'extcredits5_unit',

            'post_liker_count', 'post_disliker_count', 'post_follower_count', 'post_blocker_count',
            'comment_liker_count', 'comment_disliker_count', 'comment_follower_count', 'comment_blocker_count',
        ], $langTag);

        $stats['likeUserCount'] = $statsData->like_user_count;
        $stats['likeGroupCount'] = $statsData->like_group_count;
        $stats['likeHashtagCount'] = $statsData->like_hashtag_count;
        $stats['likePostCount'] = $statsData->like_post_count;
        $stats['likeCommentCount'] = $statsData->like_comment_count;

        $stats['dislikeUserCount'] = $statsData->dislike_user_count;
        $stats['dislikeGroupCount'] = $statsData->dislike_group_count;
        $stats['dislikeHashtagCount'] = $statsData->dislike_hashtag_count;
        $stats['dislikePostCount'] = $statsData->dislike_post_count;
        $stats['dislikeCommentCount'] = $statsData->dislike_comment_count;

        $stats['followUserCount'] = $statsData->follow_user_count;
        $stats['followGroupCount'] = $statsData->follow_group_count;
        $stats['followHashtagCount'] = $statsData->follow_hashtag_count;
        $stats['followPostCount'] = $statsData->follow_post_count;
        $stats['followCommentCount'] = $statsData->follow_comment_count;

        $stats['blockUserCount'] = $statsData->block_user_count;
        $stats['blockGroupCount'] = $statsData->block_group_count;
        $stats['blockHashtagCount'] = $statsData->block_hashtag_count;
        $stats['blockPostCount'] = $statsData->block_post_count;
        $stats['blockCommentCount'] = $statsData->block_comment_count;

        $stats['likeMeCount'] = $statsData->like_me_count;
        $stats['dislikeMeCount'] = $statsData->dislike_me_count;
        $stats['followMeCount'] = $statsData->follow_me_count;
        $stats['blockMeCount'] = $statsData->block_me_count;

        $stats['postPublishCount'] = $statsData->post_publish_count;
        $stats['postDigestCount'] = $statsData->post_digest_count;
        $stats['postLikeCount'] = $statConfig['post_liker_count'] ? $statsData->post_like_count : null;
        $stats['postDislikeCount'] = $statConfig['post_disliker_count'] ? $statsData->post_dislike_count : null;
        $stats['postFollowCount'] = $statConfig['post_follower_count'] ? $statsData->post_follow_count : null;
        $stats['postBlockCount'] = $statConfig['post_blocker_count'] ? $statsData->post_block_count : null;

        $stats['commentPublishCount'] = $statsData->comment_publish_count;
        $stats['commentDigestCount'] = $statsData->comment_digest_count;
        $stats['commentLikeCount'] = $statConfig['comment_liker_count'] ? $statsData->comment_like_count : null;
        $stats['commentDislikeCount'] = $statConfig['comment_disliker_count'] ? $statsData->comment_dislike_count : null;
        $stats['commentFollowCount'] = $statConfig['comment_follower_count'] ? $statsData->comment_follow_count : null;
        $stats['commentBlockCount'] = $statConfig['comment_blocker_count'] ? $statsData->comment_block_count : null;

        $stats['extcredits1'] = ($statConfig['extcredits1_status'] != 1) ? $statsData->extcredits1 : null;
        $stats['extcredits1Status'] = $statConfig['extcredits1_status'];
        $stats['extcredits1Name'] = $statConfig['extcredits1_name'];
        $stats['extcredits1Unit'] = $statConfig['extcredits1_unit'];
        $stats['extcredits2'] = ($statConfig['extcredits2_status'] != 1) ? $statsData->extcredits2 : null;
        $stats['extcredits2Status'] = $statConfig['extcredits2_status'];
        $stats['extcredits2Name'] = $statConfig['extcredits2_name'];
        $stats['extcredits2Unit'] = $statConfig['extcredits2_unit'];
        $stats['extcredits3'] = ($statConfig['extcredits3_status'] != 1) ? $statsData->extcredits3 : null;
        $stats['extcredits3Status'] = $statConfig['extcredits3_status'];
        $stats['extcredits3Name'] = $statConfig['extcredits3_name'];
        $stats['extcredits3Unit'] = $statConfig['extcredits3_unit'];
        $stats['extcredits4'] = ($statConfig['extcredits4_status'] != 1) ? $statsData->extcredits4 : null;
        $stats['extcredits4Status'] = $statConfig['extcredits4_status'];
        $stats['extcredits4Name'] = $statConfig['extcredits4_name'];
        $stats['extcredits4Unit'] = $statConfig['extcredits4_unit'];
        $stats['extcredits5'] = ($statConfig['extcredits5_status'] != 1) ? $statsData->extcredits5 : null;
        $stats['extcredits5Status'] = $statConfig['extcredits5_status'];
        $stats['extcredits5Name'] = $statConfig['extcredits5_name'];
        $stats['extcredits5Unit'] = $statConfig['extcredits5_unit'];

        return $stats;
    }
}
