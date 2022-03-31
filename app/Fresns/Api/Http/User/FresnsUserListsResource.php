<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\User;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Fresns\Api\FsDb\FresnsLanguages\FresnsLanguagesService;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRoles;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRolesConfig;
use App\Fresns\Api\FsDb\FresnsUserBlocks\FresnsUserBlocksConfig;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollowsConfig;
use App\Fresns\Api\FsDb\FresnsUserIcons\FresnsUserIcons;
use App\Fresns\Api\FsDb\FresnsUserIcons\FresnsUserIconsConfig;
use App\Fresns\Api\FsDb\FresnsUserLikes\FresnsUserLikesConfig;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRolesService;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsUserStats\FresnsUserStats;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Fresns\Api\Helpers\DateHelper;
use Illuminate\Support\Facades\DB;

/**
 * List resource config handle.
 */
class FresnsUserListsResource extends BaseAdminResource
{
    public function toArray($request)
    {
        $langTag = request()->header('langTag');
        $uid = request()->header('uid');
        if ($uid) {
            $uid = FresnsUsers::where('uid', $uid)->value('id');
        }
        $roleId = FresnsUserRolesService::getUserRoles($this->id);
        $userRole = FresnsRoles::where('id', $roleId)->first();
        $userRole = FresnsRoles::where('id', $roleId)->first();
        $rid = null;
        $nicknameColor = null;
        $roleName = null;
        $roleNameDisplay = null;
        $roleIcon = null;
        $roleIconDisplay = null;
        if ($userRole) {
            $rid = $userRole['id'];
            $nicknameColor = $userRole['nickname_color'];
            $roleName = FresnsLanguagesService::getLanguageByTableId(FresnsRolesConfig::CFG_TABLE, 'name', $userRole['id'], $langTag);
            $roleNameDisplay = $userRole['is_display_name'];
            $roleIcon = ApiFileHelper::getImageSignUrlByFileIdUrl($userRole['icon_file_id'], $userRole['icon_file_url']);
            $roleIconDisplay = $userRole['is_display_icon'];
        }

        $likeStatus = DB::table(FresnsUserLikesConfig::CFG_TABLE)->where('user_id', $uid)->where('like_type', 1)->where('like_id', $this->id)->where('deleted_at', null)->count();
        $followStatus = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 1)->where('follow_id', $this->id)->where('deleted_at', null)->count();
        $followMeStatus = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $this->id)->where('follow_type', 1)->where('follow_id', $uid)->where('deleted_at', null)->count();
        $blockStatus = DB::table(FresnsUserBlocksConfig::CFG_TABLE)->where('user_id', $uid)->where('block_type', 1)->where('block_id', $this->id)->where('deleted_at', null)->count();

        $userStats = FresnsUserStats::where('user_id', $this->id)->first();
        $stats['likeUserCount'] = $userStats['like_user_count'] ?? 0;
        $stats['likeGroupCount'] = $userStats['like_group_count'] ?? 0;
        $stats['likeHashtagCount'] = $userStats['like_hashtag_count'] ?? 0;
        $stats['likePostCount'] = $userStats['like_post_count'] ?? 0;
        $stats['likeCommentCount'] = $userStats['like_comment_count'] ?? 0;
        $stats['followUserCount'] = $userStats['follow_user_count'] ?? 0;
        $stats['followGroupCount'] = $userStats['follow_group_count'] ?? 0;
        $stats['followHashtagCount'] = $userStats['follow_hashtag_count'] ?? 0;
        $stats['followPostCount'] = $userStats['follow_post_count'] ?? 0;
        $stats['followCommentCount'] = $userStats['follow_comment_count'] ?? 0;
        $stats['blockUserCount'] = $userStats['block_user_count'] ?? 0;
        $stats['blockGroupCount'] = $userStats['block_group_count'] ?? 0;
        $stats['blockHashtagCount'] = $userStats['block_hashtag_count'] ?? 0;
        $stats['blockPostCount'] = $userStats['block_post_count'] ?? 0;
        $stats['blockCommentCount'] = $userStats['block_comment_count'] ?? 0;
        $stats['likeMeCount'] = $userStats['like_me_count'] ?? 0;
        $stats['followMeCount'] = $userStats['follow_me_count'] ?? 0;
        $stats['blockMeCount'] = $userStats['block_me_count'] ?? 0;
        $stats['postCreateCount'] = $userStats['post_create_count'] ?? 0;
        $stats['postLikeCount'] = $userStats['post_like_count'] ?? 0;
        $stats['commentCreateCount'] = $userStats['comment_create_count'] ?? 0;
        $stats['commentLikeCount'] = $userStats['comment_like_count'] ?? 0;

        // extcredits 1
        $stats['extcredits1Status'] = ApiConfigHelper::getConfigByItemKey('extcredits1_status');
        $stats['extcredits1Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits1_name', $langTag);
        $stats['extcredits1Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits1_unit', $langTag);
        $stats['extcredits1'] = $userStats['extcredits1'] ?? null;

        // extcredits 2
        $stats['extcredits2Status'] = ApiConfigHelper::getConfigByItemKey('extcredits2_status');
        $stats['extcredits2Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits2_name', $langTag);
        $stats['extcredits2Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits2_unit', $langTag);
        $stats['extcredits2'] = $userStats['extcredits2'] ?? null;

        // extcredits 3
        $stats['extcredits3Status'] = ApiConfigHelper::getConfigByItemKey('extcredits3_status');
        $stats['extcredits3Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits3_name', $langTag);
        $stats['extcredits3Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits3_unit', $langTag);
        $stats['extcredits3'] = $userStats['extcredits3'] ?? null;

        // extcredits 4
        $stats['extcredits4Status'] = ApiConfigHelper::getConfigByItemKey('extcredits4_status');
        $stats['extcredits4Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits4_name', $langTag);
        $stats['extcredits4Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits4_unit', $langTag);
        $stats['extcredits4'] = $userStats['extcredits4'] ?? null;

        // extcredits 5
        $stats['extcredits5Status'] = ApiConfigHelper::getConfigByItemKey('extcredits5_status');
        $stats['extcredits5Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits5_name', $langTag);
        $stats['extcredits5Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits5_unit', $langTag);
        $stats['extcredits5'] = $userStats['extcredits5'] ?? null;

        $userIconsArr = FresnsUserIcons::where('user_id', $this->id)->get()->toArray();
        $iconsArr = [];
        foreach ($userIconsArr as $mIcon) {
            $item = [];
            $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($mIcon['icon_file_id'], $mIcon['icon_file_url']);
            $item['name'] = FresnsLanguagesService::getLanguageByTableId(FresnsUserIconsConfig::CFG_TABLE, 'name', $mIcon['id'], $langTag);
            $iconsArr[] = $item;
        }

        if (empty($this->avatar_file_url) && empty($this->avatar_file_id)) {
            $defaultAvatar = ApiConfigHelper::getConfigByItemKey('default_avatar');
            $userAvatar = ApiFileHelper::getImageAvatarUrl($defaultAvatar);
        } else {
            $userAvatar = ApiFileHelper::getImageAvatarUrlByFileIdUrl($this->avatar_file_id, $this->avatar_file_url);
        }

        // Default Field
        $default = [
            'uid' => $this->uid,
            'username' => $this->username,
            'nickname' => $this->nickname,
            'rid' => $rid,
            'nicknameColor' => $nicknameColor,
            'roleName' => $roleName,
            'roleNameDisplay' => $roleNameDisplay,
            'roleIcon' => $roleIcon,
            'roleIconDisplay' => $roleIconDisplay,
            'avatar' => $userAvatar,
            'decorate' => ApiFileHelper::getImageSignUrlByFileIdUrl($this->decorate_file_id, $this->decorate_file_url),
            'gender' => $this->gender,
            'birthday' => DateHelper::fresnsOutputTimeToTimezone($this->birthday),
            'bio' => $this->bio,
            'location' => $this->location,
            'likeSetting' => ApiConfigHelper::getConfigByItemKey('like_user_setting'),
            'likeName' => FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'like_user_name', $langTag),
            'likeStatus' => $likeStatus,
            'followSetting' => ApiConfigHelper::getConfigByItemKey('follow_user_setting'),
            'followName' => FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'follow_user_name', $langTag),
            'followStatus' => $followStatus,
            'followMeStatus' => $followMeStatus,
            'blockSetting' => ApiConfigHelper::getConfigByItemKey('block_user_setting'),
            'blockName' => FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'block_user_name', $langTag),
            'blockStatus' => $blockStatus,
            'verifiedStatus' => $this->verified_status,
            'verifiedIcon' => ApiFileHelper::getImageSignUrlByFileIdUrl($this->verified_file_id, $this->verified_file_url),
            'verifiedDesc' => $this->verified_desc,
            'stats' => $stats,
            'icons' => $iconsArr,
        ];

        return $default;
    }
}
