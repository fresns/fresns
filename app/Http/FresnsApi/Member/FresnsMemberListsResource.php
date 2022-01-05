<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Member;

use App\Base\Resources\BaseAdminResource;
use App\Helpers\DateHelper;
use App\Http\Center\Common\GlobalService;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguagesService;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollowsConfig;
use App\Http\FresnsDb\FresnsMemberIcons\FresnsMemberIcons;
use App\Http\FresnsDb\FresnsMemberIcons\FresnsMemberIconsConfig;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikesConfig;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRelsService;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRolesConfig;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShieldsConfig;
use App\Http\FresnsDb\FresnsMemberStats\FresnsMemberStats;
use Illuminate\Support\Facades\DB;

/**
 * List resource config handle.
 */
class FresnsMemberListsResource extends BaseAdminResource
{
    public function toArray($request)
    {
        $langTag = request()->header('langTag');
        $mid = request()->header('mid');
        if ($mid) {
            $mid = FresnsMembers::where('uuid', $mid)->value('id');
        }
        $roleId = FresnsMemberRoleRelsService::getMemberRoleRels($this->id);
        $memberRole = FresnsMemberRoles::where('id', $roleId)->first();
        $memberRole = FresnsMemberRoles::where('id', $roleId)->first();
        $rid = '';
        $nicknameColor = '';
        $roleName = '';
        $roleNameDisplay = '';
        $roleIcon = '';
        $roleIconDisplay = '';
        if ($memberRole) {
            $rid = $memberRole['id'];
            $nicknameColor = $memberRole['nickname_color'];
            $roleName = FresnsLanguagesService::getLanguageByTableId(FresnsMemberRolesConfig::CFG_TABLE, 'name', $memberRole['id'], $langTag);
            $roleNameDisplay = $memberRole['is_display_name'];
            $roleIcon = ApiFileHelper::getImageSignUrlByFileIdUrl($memberRole['icon_file_id'], $memberRole['icon_file_url']);
            $roleIconDisplay = $memberRole['is_display_icon'];
        }

        $likeStatus = DB::table(FresnsMemberLikesConfig::CFG_TABLE)->where('member_id', $mid)->where('like_type', 1)->where('like_id', $this->id)->where('deleted_at', null)->count();
        $followStatus = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 1)->where('follow_id', $this->id)->where('deleted_at', null)->count();
        $followMeStatus = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $this->id)->where('follow_type', 1)->where('follow_id', $mid)->where('deleted_at', null)->count();
        $shieldStatus = DB::table(FresnsMemberShieldsConfig::CFG_TABLE)->where('member_id', $mid)->where('shield_type', 1)->where('shield_id', $this->id)->where('deleted_at', null)->count();

        $memberStats = FresnsMemberStats::where('member_id', $this->id)->first();
        $stats['likeMemberCount'] = $memberStats['like_member_count'] ?? 0;
        $stats['likeGroupCount'] = $memberStats['like_group_count'] ?? 0;
        $stats['likeHashtagCount'] = $memberStats['like_hashtag_count'] ?? 0;
        $stats['likePostCount'] = $memberStats['like_post_count'] ?? 0;
        $stats['likeCommentCount'] = $memberStats['like_comment_count'] ?? 0;
        $stats['followMemberCount'] = $memberStats['follow_member_count'] ?? 0;
        $stats['followGroupCount'] = $memberStats['follow_group_count'] ?? 0;
        $stats['followHashtagCount'] = $memberStats['follow_hashtag_count'] ?? 0;
        $stats['followPostCount'] = $memberStats['follow_post_count'] ?? 0;
        $stats['followCommentCount'] = $memberStats['follow_comment_count'] ?? 0;
        $stats['shieldMemberCount'] = $memberStats['shield_member_count'] ?? 0;
        $stats['shieldGroupCount'] = $memberStats['shield_group_count'] ?? 0;
        $stats['shieldHashtagCount'] = $memberStats['shield_hashtag_count'] ?? 0;
        $stats['shieldPostCount'] = $memberStats['shield_post_count'] ?? 0;
        $stats['shieldCommentCount'] = $memberStats['shield_comment_count'] ?? 0;
        $stats['likeMeCount'] = $memberStats['like_me_count'] ?? 0;
        $stats['followMeCount'] = $memberStats['follow_me_count'] ?? 0;
        $stats['shieldMeCount'] = $memberStats['shield_me_count'] ?? 0;
        $stats['postPublishCount'] = $memberStats['post_publish_count'] ?? 0;
        $stats['postLikeCount'] = $memberStats['post_like_count'] ?? 0;
        $stats['commentPublishCount'] = $memberStats['comment_publish_count'] ?? 0;
        $stats['commentLikeCount'] = $memberStats['comment_like_count'] ?? 0;

        // extcredits 1
        $stats['extcredits1Status'] = ApiConfigHelper::getConfigByItemKey('extcredits1_status');
        $stats['extcredits1Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits1_name', $langTag);
        $stats['extcredits1Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits1_unit', $langTag);
        $stats['extcredits1'] = $memberStats['extcredits1'] ?? '';

        // extcredits 2
        $stats['extcredits2Status'] = ApiConfigHelper::getConfigByItemKey('extcredits2_status');
        $stats['extcredits2Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits2_name', $langTag);
        $stats['extcredits2Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits2_unit', $langTag);
        $stats['extcredits2'] = $memberStats['extcredits2'] ?? '';

        // extcredits 3
        $stats['extcredits3Status'] = ApiConfigHelper::getConfigByItemKey('extcredits3_status');
        $stats['extcredits3Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits3_name', $langTag);
        $stats['extcredits3Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits3_unit', $langTag);
        $stats['extcredits3'] = $memberStats['extcredits3'] ?? '';

        // extcredits 4
        $stats['extcredits4Status'] = ApiConfigHelper::getConfigByItemKey('extcredits4_status');
        $stats['extcredits4Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits4_name', $langTag);
        $stats['extcredits4Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits4_unit', $langTag);
        $stats['extcredits4'] = $memberStats['extcredits4'] ?? '';

        // extcredits 5
        $stats['extcredits5Status'] = ApiConfigHelper::getConfigByItemKey('extcredits5_status');
        $stats['extcredits5Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits5_name', $langTag);
        $stats['extcredits5Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits5_unit', $langTag);
        $stats['extcredits5'] = $memberStats['extcredits5'] ?? '';

        $memberIconsArr = FresnsMemberIcons::where('member_id', $this->id)->get()->toArray();
        $iconsArr = [];
        foreach ($memberIconsArr as $mIcon) {
            $item = [];
            $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($mIcon['icon_file_id'], $mIcon['icon_file_url']);
            $item['name'] = FresnsLanguagesService::getLanguageByTableId(FresnsMemberIconsConfig::CFG_TABLE, 'name', $mIcon['id'], $langTag);
            $iconsArr[] = $item;
        }

        if (empty($this->avatar_file_url) && empty($this->avatar_file_id)) {
            $defaultAvatar = ApiConfigHelper::getConfigByItemKey('default_avatar');
            $memberAvatar = ApiFileHelper::getImageAvatarUrl($defaultAvatar);
        } else {
            $memberAvatar = ApiFileHelper::getImageAvatarUrlByFileIdUrl($this->avatar_file_id, $this->avatar_file_url);
        }

        // Default Field
        $default = [
            'mid' => $this->uuid,
            'mname' => $this->name,
            'nickname' => $this->nickname,
            'rid' => $rid,
            'nicknameColor' => $nicknameColor,
            'roleName' => $roleName,
            'roleNameDisplay' => $roleNameDisplay,
            'roleIcon' => $roleIcon,
            'roleIconDisplay' => $roleIconDisplay,
            'avatar' => $memberAvatar,
            'decorate' => ApiFileHelper::getImageSignUrlByFileIdUrl($this->decorate_file_id, $this->decorate_file_url),
            'gender' => $this->gender,
            'birthday' => DateHelper::fresnsOutputTimeToTimezone($this->birthday),
            'bio' => $this->bio,
            'likeSetting' => ApiConfigHelper::getConfigByItemKey('like_member_setting'),
            'likeName' => FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'like_member_name', $langTag),
            'likeStatus' => $likeStatus,
            'followSetting' => ApiConfigHelper::getConfigByItemKey('follow_member_setting'),
            'followName' => FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'follow_member_name', $langTag),
            'followStatus' => $followStatus,
            'followMeStatus' => $followMeStatus,
            'shieldSetting' => ApiConfigHelper::getConfigByItemKey('shield_member_setting'),
            'shieldName' => FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'shield_member_name', $langTag),
            'shieldStatus' => $shieldStatus,
            'verifiedStatus' => $this->verified_status,
            'verifiedIcon' => ApiFileHelper::getImageSignUrlByFileIdUrl($this->verified_file_id, $this->verified_file_url),
            'verifiedDesc' => $this->verified_desc,
            'stats' => $stats,
            'icons' => $iconsArr,
        ];

        return $default;
    }
}
