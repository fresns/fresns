<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;

trait UserServiceTrait
{
    public function getUserProfile(?string $langTag = null, ?string $timezone = null)
    {
        $userData = $this;

        $profile['uid'] = $userData->uid;
        $profile['username'] = $userData->username;
        $profile['nickname'] = $userData->nickname;
        $profile['avatar'] = static::getUserAvatar($userData->id);
        $profile['decorate'] = FileHelper::fresnsFileUrlByTableColumn($userData->decorate_file_id, $userData->decorate_file_url, 'imageAvatarUrl');
        $profile['banner'] = FileHelper::fresnsFileUrlByTableColumn($userData->banner_file_id, $userData->banner_file_url);
        $profile['gender'] = $userData->gender;
        $profile['birthday'] = DateHelper::fresnsDateTimeByTimezone($userData->birthday, $timezone, $langTag);
        $profile['bio'] = $userData->bio;
        $profile['location'] = $userData->location;
        $profile['dialogLimit'] = $userData->dialog_limit;
        $profile['commentLimit'] = $userData->comment_limit;
        $profile['timezone'] = $userData->timezone;
        $profile['verifiedStatus'] = (bool) $userData->verified_status;
        $profile['verifiedIcon'] = FileHelper::fresnsFileUrlByTableColumn($userData->verified_file_id, $userData->verified_file_url);
        $profile['verifiedDesc'] = $userData->verified_desc;
        $profile['verifiedDateTime'] = DateHelper::fresnsDateTimeByTimezone($userData->verified_at, $timezone, $langTag);
        $profile['expiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($userData->expired_at, $timezone, $langTag);
        $profile['lastPublishPost'] = DateHelper::fresnsDateTimeByTimezone($userData->last_post_at, $timezone, $langTag);
        $profile['lastPublishComment'] = DateHelper::fresnsDateTimeByTimezone($userData->last_comment_at, $timezone, $langTag);
        $profile['lastEditUsername'] = DateHelper::fresnsDateTimeByTimezone($userData->last_username_at, $timezone, $langTag);
        $profile['lastEditNickname'] = DateHelper::fresnsDateTimeByTimezone($userData->last_nickname_at, $timezone, $langTag);
        $profile['registerDateTime'] = DateHelper::fresnsDateTimeByTimezone($userData->created_at, $timezone, $langTag);
        $profile['hasPassword'] = !! $userData->password;
        $profile['status'] = (bool) $userData->is_enable;
        $profile['waitDelete'] = (bool) $userData->wait_delete;
        $profile['waitDeleteDateTime'] = DateHelper::fresnsDateTimeByTimezone($userData->wait_delete_at, $timezone, $langTag);
        $profile['deactivate'] = !! $userData->deleted_at;
        $profile['deactivateTime'] = DateHelper::fresnsDateTimeByTimezone($userData->deleted_at, $timezone, $langTag);

        return $profile;
    }

    public static function getUserAvatar(int $userId)
    {
        $user = User::where('id', $userId)->first(['avatar_file_id', 'avatar_file_url', 'deleted_at']);

        $avatar = ConfigHelper::fresnsConfigByItemKeys([
            'default_avatar',
            'deactivate_avatar',
        ]);

        if (empty($user->deleted_at)) {
            if (empty($user->avatar_file_url) && empty($user->avatar_file_id)) {
                // default avatar
                if (ConfigHelper::fresnsConfigFileValueTypeByItemKey('default_avatar') == 'URL') {
                    $userAvatar = $avatar['default_avatar'];
                } else {
                    $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink([
                        'fileId' => $avatar['default_avatar'],
                    ]);
                    $userAvatar = $fresnsResp->getData('imageAvatarUrl');
                }
            } else {
                // user avatar
                $userAvatar = FileHelper::fresnsFileUrlByTableColumn($user->avatar_file_id, $user->avatar_file_url, 'imageAvatarUrl');
            }
        } else {
            // user deactivate avatar
            if (ConfigHelper::fresnsConfigFileValueTypeByItemKey('deactivate_avatar') === 'URL') {
                $userAvatar = $avatar['deactivate_avatar'];
            } else {
                $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink([
                    'fileId' => $avatar['deactivate_avatar'],
                ]);
                $userAvatar = $fresnsResp->getData('imageAvatarUrl');
            }
        }

        return $userAvatar;
    }

    public function getUserMainRole(?string $langTag = null, ?string $timezone = null)
    {
        $userData = $this;

        $mainRoleData = UserRole::where('user_id', $userData->id)->where('is_main', 1)->first(['role_id', 'expired_at']);
        $roleData = Role::where('id', $mainRoleData->role_id)->first();

        foreach ($roleData->permission as $perm) {
            $permission['rid'] = $roleData->id;
            $permission[$perm['permKey']] = $perm['permValue'];
        }

        $mainRole['nicknameColor'] = $roleData->nickname_color;
        $mainRole['rid'] = $roleData->id;
        $mainRole['roleName'] = LanguageHelper::fresnsLanguageByTableId('roles', 'name', $roleData->id, $langTag);
        $mainRole['roleNameDisplay'] = (bool) $roleData->is_display_name;
        $mainRole['roleIcon'] = FileHelper::fresnsFileUrlByTableColumn($roleData->icon_file_id, $roleData->icon_file_url);
        $mainRole['roleIconDisplay'] = (bool) $roleData->is_display_icon;
        $mainRole['roleExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($mainRoleData->expired_at, $timezone, $langTag);
        $mainRole['rolePermission'] = $permission;
        $mainRole['roleStatus'] = (bool) $roleData->is_enable;

        return $mainRole;
    }

    public function getUserRoles(?string $langTag = null, ?string $timezone = null)
    {
        $userData = $this;

        $userRoleArr = UserRole::where('user_id', $userData->id)->get()->toArray();
        $roleArr = Role::whereIn('id', array_column($userRoleArr, 'role_id'))->get();

        $roleList = null;
        foreach ($roleArr as $role) {
            foreach ($userRoleArr as $userRole) {
                if ($userRole['role_id'] !== $role['id']) {
                    continue;
                }
                $item['rid'] = $role['id'];
                $item['isMain'] = (bool) $userRole['is_main'];
                $item['expiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($userRole['expired_at'], $timezone, $langTag);
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

    public function getUserArchives(?string $langTag = null)
    {
        $archiveArr = $this->archives;

        $archiveList = [];
        foreach ($archiveArr as $archive) {
            $item['itemKey'] = $archive->config_key;
            $item['itemValue'] = ConfigHelper::fresnsConfigByItemKey($archive->config_key, $langTag);
            $item['archiveValue'] = $archive->archive_value;
            $item['archiveType'] = $archive->archive_type;
            $archiveList[] = $item;
        }

        return $archiveList;
    }

    public function getUserStats(?string $langTag = null)
    {
        $statsData = $this->stat;

        $extcredits = ConfigHelper::fresnsConfigByItemKeys([
            'extcredits1_status', 'extcredits1_name', 'extcredits1_unit',
            'extcredits2_status', 'extcredits2_name', 'extcredits2_unit',
            'extcredits3_status', 'extcredits3_name', 'extcredits3_unit',
            'extcredits4_status', 'extcredits4_name', 'extcredits4_unit',
            'extcredits5_status', 'extcredits5_name', 'extcredits5_unit',
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
        $stats['followMeCount'] = $statsData->follow_me_count;
        $stats['blockMeCount'] = $statsData->block_me_count;
        $stats['postPublishCount'] = $statsData->post_publish_count;
        $stats['postDigestCount'] = $statsData->post_digest_count;
        $stats['postLikeCount'] = $statsData->post_like_count;
        $stats['postDislikeCount'] = $statsData->post_dislike_count;
        $stats['postFollowCount'] = $statsData->post_follow_count;
        $stats['postBlockCount'] = $statsData->post_block_count;
        $stats['commentPublishCount'] = $statsData->comment_publish_count;
        $stats['commentDigestCount'] = $statsData->comment_digest_count;
        $stats['commentLikeCount'] = $statsData->comment_like_count;
        $stats['commentDislikeCount'] = $statsData->comment_dislike_count;
        $stats['commentFollowCount'] = $statsData->comment_follow_count;
        $stats['commentBlockCount'] = $statsData->comment_block_count;
        $stats['extcredits1'] = $statsData->extcredits1;
        $stats['extcredits1Status'] = $extcredits['extcredits1_status'];
        $stats['extcredits1Name'] = $extcredits['extcredits1_name'];
        $stats['extcredits1Unit'] = $extcredits['extcredits1_unit'];
        $stats['extcredits2'] = $statsData->extcredits2;
        $stats['extcredits2Status'] = $extcredits['extcredits2_status'];
        $stats['extcredits2Name'] = $extcredits['extcredits2_name'];
        $stats['extcredits2Unit'] = $extcredits['extcredits2_unit'];
        $stats['extcredits3'] = $statsData->extcredits3;
        $stats['extcredits3Status'] = $extcredits['extcredits3_status'];
        $stats['extcredits3Name'] = $extcredits['extcredits3_name'];
        $stats['extcredits3Unit'] = $extcredits['extcredits3_unit'];
        $stats['extcredits4'] = $statsData->extcredits4;
        $stats['extcredits4Status'] = $extcredits['extcredits4_status'];
        $stats['extcredits4Name'] = $extcredits['extcredits4_name'];
        $stats['extcredits4Unit'] = $extcredits['extcredits4_unit'];
        $stats['extcredits5'] = $statsData->extcredits5;
        $stats['extcredits5Status'] = $extcredits['extcredits5_status'];
        $stats['extcredits5Name'] = $extcredits['extcredits5_name'];
        $stats['extcredits5Unit'] = $extcredits['extcredits5_unit'];

        return $stats;
    }
}
