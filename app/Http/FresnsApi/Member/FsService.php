<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Member;

use App\Helpers\DateHelper;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsDb\FresnsCommentLogs\FresnsCommentLogs;
use App\Http\FresnsDb\FresnsComments\FresnsComments;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguagesService;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollows;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollowsConfig;
use App\Http\FresnsDb\FresnsMemberIcons\FresnsMemberIcons;
use App\Http\FresnsDb\FresnsMemberIcons\FresnsMemberIconsConfig;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikes;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikesConfig;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRelsService;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRolesConfig;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShields;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShieldsConfig;
use App\Http\FresnsDb\FresnsMemberStats\FresnsMemberStats;
use App\Http\FresnsDb\FresnsPluginBadges\FresnsPluginBadges;
use App\Http\FresnsDb\FresnsPluginBadges\FresnsPluginBadgesService;
use App\Http\FresnsDb\FresnsPlugins\FresnsPluginsService;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsagesConfig;
use App\Http\FresnsDb\FresnsPostLogs\FresnsPostLogs;
use App\Http\FresnsDb\FresnsPosts\FresnsPosts;
use App\Http\FresnsDb\FresnsUsers\FresnsUsersConfig;
use Illuminate\Support\Facades\DB;

class FsService
{
    public function common($mid, $langTag, $isMe)
    {
        // Member SEO Info
        $seoInfoArr = DB::table('seo')->where('linked_type', 1)->where('linked_id', $mid)->where('deleted_at', null)->where('lang_tag', $langTag)->get(['title', 'keywords', 'description'])->first();
        if (empty($seoInfoArr)) {
            $defaultLangTag = ApiLanguageHelper::getDefaultLanguage();
            $seoInfoArr = DB::table('seo')->where('linked_type', 1)->where('linked_id', $mid)->where('deleted_at', null)->where('lang_tag', $defaultLangTag)->get(['title', 'keywords', 'description'])->first();
        }
        $data['seoInfo'] = $seoInfoArr;

        // Manages
        // plugin_usages > type=5 + scene ⊇ 3
        // plugin_usages > member_roles If the value is empty, then output all; if there is a value, determine whether all the associated role ids of the current request member are in the field configuration.
        $pluginUsagesArr = FresnsPluginUsages::where('type', 5)->where('scene', 'LIKE', '%3%')->get()->toArray();
        $managesArr = [];
        if (! empty($pluginUsagesArr)) {
            foreach ($pluginUsagesArr as $v) {
                if (! empty($v['member_roles'])) {
                    $rolesArr = explode(',', $v['member_roles']);
                    if (! in_array($mid, $rolesArr)) {
                        continue;
                    }
                }
                $item = [];
                $item['plugin'] = $v['plugin_unikey'];
                $item['name'] = FresnsLanguagesService::getLanguageByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $v['id'], $langTag);
                $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['icon_file_id'], $v['icon_file_url']);
                $item['url'] = FresnsPluginsService::getPluginUsagesUrl($v['plugin_unikey'], $v['id']);
                $managesArr[] = $item;
            }
        }
        $data['manages'] = $managesArr;

        // Member Features
        // plugin_usages > type=7
        // plugin_usages > member_roles (Permission judgment)
        $features = [];
        if ($isMe == true) {
            $pluginUsagesArr = FresnsPluginUsages::where('type', 7)->get()->toArray();
            if (! empty($pluginUsagesArr)) {
                foreach ($pluginUsagesArr as $v) {
                    if (! empty($v['member_roles'])) {
                        $rolesArr = explode(',', $v['member_roles']);
                        if (! in_array($mid, $rolesArr)) {
                            continue;
                        }
                    }
                    $item = [];
                    $item['plugin'] = $v['plugin_unikey'];
                    $item['name'] = FresnsLanguagesService::getLanguageByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $v['id'], $langTag);
                    $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['icon_file_id'], $v['icon_file_url']);
                    $item['url'] = FresnsPluginsService::getPluginUsagesUrl($v['plugin_unikey'], $v['id']);
                    $pluginBadges = FresnsPluginBadges::where('plugin_unikey', $v['plugin_unikey'])->where('member_id', $mid)->first();
                    $item['badgesType'] = $pluginBadges['display_type'] ?? '';
                    $item['badgesValue'] = $pluginBadges['value_text'] ?? '';
                    $features[] = $item;
                }
            }
        }

        $data['features'] = $features;

        // Member Profiles
        // plugin_usages > type=8
        // plugin_usages > member_roles (Permission judgment)
        $profiles = [];
        if ($isMe == true) {
            $pluginUsagesArr = FresnsPluginUsages::where('type', 8)->get()->toArray();
            if (! empty($pluginUsagesArr)) {
                foreach ($pluginUsagesArr as $v) {
                    if (! empty($v['member_roles'])) {
                        $rolesArr = explode(',', $v['member_roles']);
                        if (! in_array($mid, $rolesArr)) {
                            continue;
                        }
                    }
                    $item = [];
                    $item['plugin'] = $v['plugin_unikey'];
                    $item['name'] = FresnsLanguagesService::getLanguageByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $v['id'], $langTag);
                    $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['icon_file_id'], $v['icon_file_url']);
                    $item['url'] = FresnsPluginsService::getPluginUsagesUrl($v['plugin_unikey'], $v['id']);
                    $pluginBadges = FresnsPluginBadges::where('plugin_unikey', $v['plugin_unikey'])->where('member_id', $mid)->first();
                    $item['badgesType'] = $pluginBadges['display_type'] ?? '';
                    $item['badgesValue'] = $pluginBadges['value_text'] ?? '';
                    $profiles[] = $item;
                }
            }
        }
        $data['profiles'] = $profiles;

        return $data;
    }

    // Get Member List
    public static function getMemberList($request)
    {
        $viewMid = $request->input('viewMid');
        $viewType = $request->input('viewType');
        $pageSize = $request->input('pageSize', 20);
        $page = $request->input('page', 1);
        if ($pageSize > 50) {
            $pageSize = 50;
        }
        $query = DB::table('members as me');
        $query = $query->select('me.*')->leftJoin('member_stats as st', 'me.id', '=', 'st.member_id');

        if ($viewType) {
            switch ($viewType) {
                case 1:
                    $memberIdArr = FresnsMemberLikes::where('member_id', $viewMid)->where('like_type', 1)->pluck('like_id')->toArray();
                    break;
                case 2:
                    $memberIdArr = FresnsMemberFollows::where('member_id', $viewMid)->where('follow_type', 1)->pluck('follow_id')->toArray();
                    break;
                default:
                    $memberIdArr = FresnsMemberShields::where('member_id', $viewMid)->where('shield_type', 1)->pluck('shield_id')->toArray();
                    break;
            }
            $query->whereIn('me.id', $memberIdArr);
        }

        $item = $query->paginate($pageSize, ['*'], 'page', $page);

        $data = [];
        $data['list'] = FresnsMemberListsResource::collection($item->items())->toArray($item->items());
        $pagination['total'] = $item->total();
        $pagination['current'] = $page;
        $pagination['pageSize'] = $pageSize;
        $pagination['lastPage'] = $item->lastPage();
        $data['pagination'] = $pagination;

        return $data;
    }

    // Get Member Detail
    public function getMemberDetail($mid, $viewMid, $isMe, $langTag)
    {
        $member = FresnsMembers::where('id', $viewMid)->first();

        $data = [];
        if ($member) {
            $data['mid'] = $member['uuid'];
            $data['mname'] = $member['name'];
            $data['nickname'] = $member['nickname'];
            $roleIdArr = FresnsMemberRoleRels::where('member_id', $member['id'])->pluck('role_id')->toArray();
            $roleId = FresnsMemberRoleRelsService::getMemberRoleRels($member['id']);
            $memberRole = FresnsMemberRoles::where('id', $roleId)->first();
            $data['rid'] = '';
            $data['nicknameColor'] = '';
            $data['roleName'] = '';
            $data['roleNameDisplay'] = '';
            $data['roleIcon'] = '';
            $data['roleIconDisplay'] = '';
            if ($memberRole) {
                $data['rid'] = $memberRole['id'];
                $data['nicknameColor'] = $memberRole['nickname_color'];
                $data['roleName'] = FresnsLanguagesService::getLanguageByTableId(FresnsMemberRolesConfig::CFG_TABLE, 'name', $memberRole['id'], $langTag);
                $data['roleNameDisplay'] = $memberRole['is_display_name'];
                $data['roleIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($memberRole['icon_file_id'], $memberRole['icon_file_url']);
                $data['roleIconDisplay'] = $memberRole['is_display_icon'];
            }
            $users = DB::table(FresnsUsersConfig::CFG_TABLE)->where('id', $member['user_id'])->first();

            if (empty($users->deleted_at)) {
                if (empty($member['avatar_file_url']) && empty($member['avatar_file_id'])) {
                    $defaultAvatar = ApiConfigHelper::getConfigByItemKey('default_avatar');
                    $memberAvatar = ApiFileHelper::getImageAvatarUrl($defaultAvatar);
                } else {
                    $memberAvatar = ApiFileHelper::getImageAvatarUrlByFileIdUrl($member['avatar_file_id'], $member['avatar_file_url']);
                }
            } else {
                $deactivateAvatar = ApiConfigHelper::getConfigByItemKey('deactivate_avatar');
                $memberAvatar = ApiFileHelper::getImageAvatarUrl($deactivateAvatar);
            }
            $data['avatar'] = $memberAvatar;
            $data['decorate'] = ApiFileHelper::getImageSignUrlByFileIdUrl($member['decorate_file_id'], $member['decorate_file_url']);
            $data['gender'] = $member['gender'];
            $data['birthday'] = DateHelper::fresnsOutputTimeToTimezone($member['birthday']);
            $data['bio'] = $member['bio'];
            $data['location'] = $member['location'];
            $data['dialogLimit'] = $member['dialog_limit'];
            $data['timezone'] = $member['timezone'];
            $data['language'] = $member['language'];
            $data['expiredTime'] = DateHelper::fresnsOutputTimeToTimezone($member['expired_at']);
            $data['verifiedStatus'] = $member['verified_status'];
            $data['verifiedIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($member['verified_file_id'], $member['verified_file_url']);
            $data['verifiedDesc'] = $member['verified_desc'];
            $data['lastEditMname'] = $member['last_name_at'];
            $data['lastEditNickname'] = $member['last_nickname_at'];
            $data['createdTime'] = DateHelper::fresnsOutputTimeToTimezone($member['created_at']);
            $data['status'] = $member['is_enable'];
            $memberRolesArr = FresnsMemberRoles::whereIn('id', $roleIdArr)->get()->toArray();
            $rolesArr = [];
            foreach ($memberRolesArr as $v) {
                $item = [];
                $item['type'] = FresnsMemberRoleRels::where('member_id', $mid)->where('role_id', $v['id'])->value('type');
                $item['rid'] = $v['id'];
                $item['name'] = FresnsLanguagesService::getLanguageByTableId(FresnsMemberRolesConfig::CFG_TABLE, 'name', $v['id'], $langTag);
                $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['icon_file_id'], $v['icon_file_url']);
                $item['nicknameColor'] = $v['nickname_color'];
                $item['permission'] = json_decode($v['permission'], true);
                $rolesArr[] = $item;
            }
            $data['roles'] = $rolesArr;
            $memberStats = FresnsMemberStats::where('member_id', $viewMid)->first();
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

            // extcredits1
            $stats['extcredits1Status'] = ApiConfigHelper::getConfigByItemKey('extcredits1_status');
            $stats['extcredits1Name'] = ApiConfigHelper::getConfigByItemKey('extcredits1_name');
            $stats['extcredits1Unit'] = ApiConfigHelper::getConfigByItemKey('extcredits1_unit');
            if ($stats['extcredits1Status'] == 3) {
                $stats['extcredits1'] = $memberStats['extcredits1'];
            }
            // extcredits2
            $stats['extcredits2Status'] = ApiConfigHelper::getConfigByItemKey('extcredits2_status');
            $stats['extcredits2Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits2_name', $langTag);
            $stats['extcredits2Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits2_unit', $langTag);
            if ($stats['extcredits2Status'] == 3) {
                $stats['extcredits2'] = $memberStats['extcredits2'];
            }
            // extcredits3
            $stats['extcredits3Status'] = ApiConfigHelper::getConfigByItemKey('extcredits3_status');
            $stats['extcredits3Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits3_name', $langTag);
            $stats['extcredits3Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits3_unit', $langTag);
            if ($stats['extcredits3Status'] == 3) {
                $stats['extcredits3'] = $memberStats['extcredits3'];
            }
            // extcredits4
            $stats['extcredits4Status'] = ApiConfigHelper::getConfigByItemKey('extcredits4_status');
            $stats['extcredits4Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits4_name', $langTag);
            $stats['extcredits4Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits4_unit', $langTag);
            if ($stats['extcredits4Status'] == 3) {
                $stats['extcredits4'] = $memberStats['extcredits4'];
            }
            // extcredits5
            $stats['extcredits5Status'] = ApiConfigHelper::getConfigByItemKey('extcredits5_status');
            $stats['extcredits5Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits5_name', $langTag);
            $stats['extcredits5Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits5_unit', $langTag);
            if ($stats['extcredits5Status'] == 3) {
                $stats['extcredits5'] = $memberStats['extcredits5'];
            }
            $data['stats'] = $stats;

            $memberIconsArr = FresnsMemberIcons::where('member_id', $viewMid)->get()->toArray();
            $iconsArr = [];
            foreach ($memberIconsArr as $v) {
                $item = [];
                $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['icon_file_id'], $v['icon_file_url']);
                $item['name'] = FresnsLanguagesService::getLanguageByTableId(FresnsMemberIconsConfig::CFG_TABLE, 'name', $v['id'], $langTag);
                $item['type'] = $v['type'];
                $item['url'] = FresnsPluginsService::getPluginUrlByUnikey($v['plugin_unikey']);
                $iconsArr[] = $item;
            }
            $data['icons'] = $iconsArr;

            $data['draftCount'] = null;
            if ($isMe == true) {
                $draftCount['posts'] = FresnsPostLogs::where('member_id', $member['id'])->whereIn('state', [1, 4])->count();
                $draftCount['comments'] = FresnsCommentLogs::where('member_id', $member['id'])->whereIn('state', [1, 4])->count();
                $data['draftCount'] = $draftCount;
            }

            $data['memberName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'member_name', $langTag);
            $data['memberIdName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'member_id_name', $langTag);
            $data['memberNameName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'member_name_name', $langTag);
            $data['memberNicknameName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'member_nickname_name', $langTag);
            $data['memberRoleName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'member_role_name', $langTag);

            $data['followSetting'] = ApiConfigHelper::getConfigByItemKey('follow_member_setting');
            $data['followName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'follow_member_name', $langTag);
            $data['followStatus'] = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 1)->where('follow_id', $viewMid)->where('deleted_at', null)->count();
            $data['followMeStatus'] = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $viewMid)->where('follow_type', 1)->where('follow_id', $mid)->where('deleted_at', null)->count();

            $data['likeSetting'] = ApiConfigHelper::getConfigByItemKey('like_member_setting');
            $data['likeName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'like_member_name', $langTag);
            $data['likeStatus'] = DB::table(FresnsMemberLikesConfig::CFG_TABLE)->where('member_id', $mid)->where('like_type', 1)->where('like_id', $viewMid)->where('deleted_at', null)->count();

            $data['shieldSetting'] = ApiConfigHelper::getConfigByItemKey('shield_member_setting');
            $data['shieldName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'shield_member_name', $langTag);
            $data['shieldStatus'] = DB::table(FresnsMemberShieldsConfig::CFG_TABLE)->where('member_id', $mid)->where('shield_type', 1)->where('shield_id', $viewMid)->where('deleted_at', null)->count();

            if ($isMe = false) {
                $unikeyArr = FresnsPluginBadges::where('member_id', $mid)->pluck('plugin_unikey')->toArray();
                $managesArr = FresnsPluginUsages::whereIn('plugin_unikey', $unikeyArr)->get()->toArray();
                $expandsArr = [];
                foreach ($managesArr as $v) {
                    $item = [];
                    $item['plugin'] = $v['plugin_unikey'];
                    $item['name'] = FresnsLanguagesService::getLanguageByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $v['id'], $langTag);
                    $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['icon_file_id'], $v['icon_file_url']);
                    $item['url'] = FresnsPluginsService::getPluginUsagesUrl($v['plugin_unikey'], $v['id']);

                    $expandsArr[] = $item;
                }
                $data['manages'] = $expandsArr;
            }
        }

        return $data;
    }

    // Get Group List
    public static function getGroupList($request)
    {
        $viewMid = $request->input('viewMid');
        $viewType = $request->input('viewType');
        switch ($viewType) {
            case 1:
                $groupArr = FresnsMemberLikes::where('member_id', $viewMid)->where('like_type', 2)->pluck('like_id')->toArray();
                break;
            case 2:
                $groupArr = FresnsMemberFollows::where('member_id', $viewMid)->where('follow_type', 2)->pluck('follow_id')->toArray();
                break;
            default:
                $groupArr = FresnsMemberShields::where('member_id', $viewMid)->where('shield_type', 2)->pluck('shield_id')->toArray();
                break;
        }

        return $groupArr;
    }

    // Get Hashtag List
    public static function getHashtagList($request)
    {
        $viewMid = $request->input('viewMid');
        $viewType = $request->input('viewType');
        switch ($viewType) {
            case 1:
                $hashtagArr = FresnsMemberLikes::where('member_id', $viewMid)->where('like_type', 3)->pluck('like_id')->toArray();
                break;
            case 2:
                $hashtagArr = FresnsMemberFollows::where('member_id', $viewMid)->where('follow_type', 3)->pluck('follow_id')->toArray();
                break;
            default:
                $hashtagArr = FresnsMemberShields::where('member_id', $viewMid)->where('shield_type', 3)->pluck('shield_id')->toArray();
                break;
        }

        return $hashtagArr;
    }

    // Get Post List
    public static function getPostList($request)
    {
        $viewMid = $request->input('viewMid');
        $viewType = $request->input('viewType');
        switch ($viewType) {
            case 1:
                $postArr = FresnsMemberLikes::where('member_id', $viewMid)->where('like_type', 4)->pluck('like_id')->toArray();
                break;
            case 2:
                $postArr = FresnsMemberFollows::where('member_id', $viewMid)->where('follow_type', 4)->pluck('follow_id')->toArray();
                break;
            default:
                $postArr = FresnsMemberShields::where('member_id', $viewMid)->where('shield_type', 4)->pluck('shield_id')->toArray();
                break;
        }

        return $postArr;
    }

    // Get Comment List
    public static function getCommentList($request)
    {
        $viewMid = $request->input('viewMid');
        $viewType = $request->input('viewType');
        switch ($viewType) {
            case 1:
                $commentArr = FresnsMemberLikes::where('member_id', $viewMid)->where('like_type', 5)->pluck('like_id')->toArray();
                break;
            case 2:
                $commentArr = FresnsMemberFollows::where('member_id', $viewMid)->where('follow_type', 5)->pluck('follow_id')->toArray();
                break;
            default:
                $commentArr = FresnsMemberShields::where('member_id', $viewMid)->where('shield_type', 5)->pluck('shield_id')->toArray();
                break;
        }

        return $commentArr;
    }
}
