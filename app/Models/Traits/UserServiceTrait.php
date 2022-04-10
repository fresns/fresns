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
use App\Helpers\PluginHelper;
use App\Models\CommentLog;
use App\Models\PostLog;
use App\Models\Role;
use App\Models\User;
use App\Models\UserArchive;
use App\Models\UserIcon;
use App\Models\UserRole;
use App\Models\UserStat;

trait UserServiceTrait
{
    public function getUserProfile(string $timezone = '')
    {
        $userData = $this;

        $profile['uid'] = $userData['uid'];
        $profile['username'] = $userData['username'];
        $profile['nickname'] = $userData['nickname'];
        $profile['avatar'] = static::getUserAvatar($userData['uid']);
        $profile['decorate'] = FileHelper::fresnsFileImageUrlByColumn($userData['decorate_file_id'], $userData['decorate_file_url'], 'imageAvatarUrl');
        $profile['gender'] = $userData['gender'];
        $profile['birthday'] = DateHelper::fresnsDateTimeByTimezone($userData['birthday'], $timezone);
        $profile['bio'] = $userData['bio'];
        $profile['location'] = $userData['location'];
        $profile['dialogLimit'] = $userData['dialog_limit'];
        $profile['commentLimit'] = $userData['comment_limit'];
        $profile['timezone'] = $userData['timezone'];
        $profile['language'] = $userData['language'];
        $profile['verifiedStatus'] = $userData['verified_status'];
        $profile['verifiedIcon'] = FileHelper::fresnsFileImageUrlByColumn($userData['verified_file_id'], $userData['verified_file_url'], 'imageConfigUrl');
        $profile['verifiedDesc'] = $userData['verified_desc'];
        $profile['verifiedDateTime'] = DateHelper::fresnsDateTimeByTimezone($userData['verified_at'], $timezone);
        $profile['expiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($userData['expired_at'], $timezone);
        $profile['lastCreatePost'] = DateHelper::fresnsDateTimeByTimezone($userData['last_username_at'], $timezone);
        $profile['lastCreateComment'] = DateHelper::fresnsDateTimeByTimezone($userData['last_nickname_at'], $timezone);
        $profile['lastEditUsername'] = DateHelper::fresnsDateTimeByTimezone($userData['last_username_at'], $timezone);
        $profile['lastEditNickname'] = DateHelper::fresnsDateTimeByTimezone($userData['last_nickname_at'], $timezone);
        $profile['registerDateTime'] = DateHelper::fresnsDateTimeByTimezone($userData['created_at'], $timezone);
        $profile['hasPassword'] = ! empty($userData['password']) ? true : false;
        $profile['status'] = $userData['is_enable'];
        $profile['deactivate'] = ! empty($userData['deleted_at']) ? true : false;
        $profile['deactivateTime'] = DateHelper::fresnsDateTimeByTimezone($userData['deleted_at'], $timezone);

        return $profile;
    }

    public static function getUserAvatar(int $uid)
    {
        $user = User::where('uid', $uid)->first(['avatar_file_id', 'avatar_file_url', 'deleted_at']);
        $defaultAvatar = ConfigHelper::fresnsConfigByItemKey('default_avatar');
        $deactivateAvatar = ConfigHelper::fresnsConfigByItemKey('deactivate_avatar');

        if (empty($user->deleted_at)) {
            if (empty($user->avatar_file_url) && empty($user->avatar_file_id)) {
                // default avatar
                if (ConfigHelper::fresnsConfigFileValueTypeByItemKey('default_avatar') == 'URL') {
                    $userAvatar = $defaultAvatar;
                } else {
                    $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink([
                        'fileId' => $defaultAvatar,
                    ]);
                    $userAvatar = $fresnsResp->getData('imageAvatarUrl');
                }
            } else {
                // user avatar
                $userAvatar = FileHelper::fresnsFileImageUrlByColumn($user->avatar_file_id, $user->avatar_file_url, 'imageAvatarUrl');
            }
        } else {
            // user deactivate avatar
            if (ConfigHelper::fresnsConfigFileValueTypeByItemKey('deactivate_avatar') === 'URL') {
                $userAvatar = $deactivateAvatar;
            } else {
                $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink([
                    'fileId' => $deactivateAvatar,
                ]);
                $userAvatar = $fresnsResp->getData('imageAvatarUrl');
            }
        }

        return $userAvatar;
    }

    public function getUserMainRole(string $langTag = '', string $timezone = '')
    {
        $userData = $this;

        $mainRoleData = UserRole::where('user_id', $userData->id)->where('is_main', 1)->first(['role_id', 'expired_at']);
        $roleData = Role::where('id', $mainRoleData->role_id)->first();

        $mainRole['nicknameColor'] = $roleData['nickname_color'];
        $mainRole['rid'] = $roleData['id'];
        $mainRole['roleName'] = LanguageHelper::fresnsLanguageByTableId('roles', 'name', $roleData['id'], $langTag);
        $mainRole['roleNameDisplay'] = $roleData['is_display_name'];
        $mainRole['roleIcon'] = FileHelper::fresnsFileImageUrlByColumn($roleData['icon_file_id'], $roleData['icon_file_url'], 'imageConfigUrl');
        $mainRole['roleIconDisplay'] = $roleData['is_display_icon'];
        $mainRole['roleExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($mainRoleData->expired_at, $timezone);
        $mainRole['rolePermission'] = $roleData['permission'];

        return $mainRole;
    }

    public function getUserRoles(string $langTag = '', string $timezone = '')
    {
        $userData = $this;

        $userRoleArr = UserRole::where('user_id', $userData->id)->get()->toArray();
        $roleArr = Role::whereIn('id', array_column($userRoleArr, 'role_id'))->get()->toArray();

        $roleList = [];
        foreach ($roleArr as $role) {
            foreach ($userRoleArr as $userRole) {
                if ($userRole['role_id'] !== $role['id']) {
                    continue;
                }
                $item['rid'] = $role['id'];
                $item['isMain'] = $userRole['is_main'];
                $item['expiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($userRole['expired_at'], $timezone);
                $item['nicknameColor'] = $role['nickname_color'];
                $item['name'] = LanguageHelper::fresnsLanguageByTableId('roles', 'name', $role['id'], $langTag);
                $item['nameDisplay'] = $role['is_display_name'];
                $item['icon'] = FileHelper::fresnsFileImageUrlByColumn($role['icon_file_id'], $role['icon_file_url'], 'imageConfigUrl');
                $item['iconDisplay'] = $role['is_display_icon'];
            }
            $roleList[] = $item;
        }

        return $roleList;
    }

    public function getUserArchives(string $langTag = '')
    {
        $userData = $this;

        $archiveArr = UserArchive::where('user_id', $userData->id)->where('is_enable', 1)->get();

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

    public function getUserIcons(string $langTag = '')
    {
        $userData = $this;

        $iconArr = UserIcon::where('user_id', $userData->id)->where('is_enable', 1)->get()->toArray();

        $iconList = [];
        foreach ($iconArr as $icon) {
            $item['image'] = FileHelper::fresnsFileImageUrlByColumn($icon['icon_file_id'], $icon['icon_file_url'], 'imageConfigUrl');
            $item['name'] = LanguageHelper::fresnsLanguageByTableId('user_icons', 'name', $icon['id'], $langTag);
            $item['type'] = $icon['type'];
            $item['function'] = ! empty($icon['plugin_unikey']) ? PluginHelper::fresnsPluginUrlByUnikey($icon['plugin_unikey']) : null;
            $iconList[] = $item;
        }

        return $iconList;
    }

    public function getUserStats(string $langTag = '')
    {
        $userData = $this;

        $statsData = UserStat::where('user_id', $userData->id)->first();

        $stats['likeUserCount'] = $statsData['like_user_count'];
        $stats['likeGroupCount'] = $statsData['like_group_count'];
        $stats['likeHashtagCount'] = $statsData['like_hashtag_count'];
        $stats['likePostCount'] = $statsData['like_post_count'];
        $stats['likeCommentCount'] = $statsData['like_comment_count'];
        $stats['followUserCount'] = $statsData['followuserCount'];
        $stats['followGroupCount'] = $statsData['follow_group_count'];
        $stats['followHashtagCount'] = $statsData['follow_hashtag_count'];
        $stats['followPostCount'] = $statsData['follow_post_count'];
        $stats['followCommentCount'] = $statsData['follow_comment_count'];
        $stats['blockUserCount'] = $statsData['block_user_count'];
        $stats['blockGroupCount'] = $statsData['block_group_count'];
        $stats['blockHashtagCount'] = $statsData['block_hashtag_count'];
        $stats['blockPostCount'] = $statsData['block_post_count'];
        $stats['blockCommentCount'] = $statsData['block_comment_count'];
        $stats['likeMeCount'] = $statsData['like_me_count'];
        $stats['followMeCount'] = $statsData['follow_me_count'];
        $stats['blockMeCount'] = $statsData['block_me_count'];
        $stats['postPublishCount'] = $statsData['post_publish_count'];
        $stats['postLikeCount'] = $statsData['post_like_count'];
        $stats['commentPublishCount'] = $statsData['comment_publish_count'];
        $stats['commentLikeCount'] = $statsData['comment_like_count'];
        $stats['extcredits1'] = $statsData['extcredits1'];
        $stats['extcredits1Status'] = ConfigHelper::fresnsConfigByItemKey('extcredits1_status');
        $stats['extcredits1Name'] = ConfigHelper::fresnsConfigByItemKey('extcredits1_name', $langTag);
        $stats['extcredits1Unit'] = ConfigHelper::fresnsConfigByItemKey('extcredits1_unit', $langTag);
        $stats['extcredits2'] = $statsData['extcredits2'];
        $stats['extcredits2Status'] = ConfigHelper::fresnsConfigByItemKey('extcredits2_status');
        $stats['extcredits2Name'] = ConfigHelper::fresnsConfigByItemKey('extcredits2_name', $langTag);
        $stats['extcredits2Unit'] = ConfigHelper::fresnsConfigByItemKey('extcredits2_unit', $langTag);
        $stats['extcredits3'] = $statsData['extcredits3'];
        $stats['extcredits3Status'] = ConfigHelper::fresnsConfigByItemKey('extcredits3_status');
        $stats['extcredits3Name'] = ConfigHelper::fresnsConfigByItemKey('extcredits3_name', $langTag);
        $stats['extcredits3Unit'] = ConfigHelper::fresnsConfigByItemKey('extcredits3_unit', $langTag);
        $stats['extcredits4'] = $statsData['extcredits4'];
        $stats['extcredits4Status'] = ConfigHelper::fresnsConfigByItemKey('extcredits4_status');
        $stats['extcredits4Name'] = ConfigHelper::fresnsConfigByItemKey('extcredits4_name', $langTag);
        $stats['extcredits4Unit'] = ConfigHelper::fresnsConfigByItemKey('extcredits4_unit', $langTag);
        $stats['extcredits5'] = $statsData['extcredits5'];
        $stats['extcredits5Status'] = ConfigHelper::fresnsConfigByItemKey('extcredits5_status');
        $stats['extcredits5Name'] = ConfigHelper::fresnsConfigByItemKey('extcredits5_name', $langTag);
        $stats['extcredits5Unit'] = ConfigHelper::fresnsConfigByItemKey('extcredits5_unit', $langTag);

        return $stats;
    }

    public function getUserDrafts()
    {
        $userData = $this;

        $draftCount['posts'] = PostLog::where('user_id', $userData->id)->whereIn('state', [1, 4])->count();
        $draftCount['comments'] = CommentLog::where('user_id', $userData->id)->whereIn('state', [1, 4])->count();

        return $draftCount;
    }
}
