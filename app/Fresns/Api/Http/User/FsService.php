<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\User;

use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccountsConfig;
use App\Fresns\Api\FsDb\FresnsCommentLogs\FresnsCommentLogs;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Fresns\Api\FsDb\FresnsLanguages\FresnsLanguagesService;
use App\Fresns\Api\FsDb\FresnsPluginBadges\FresnsPluginBadges;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPluginsService;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsagesConfig;
use App\Fresns\Api\FsDb\FresnsPostLogs\FresnsPostLogs;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRoles;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRolesConfig;
use App\Fresns\Api\FsDb\FresnsUserBlocks\FresnsUserBlocks;
use App\Fresns\Api\FsDb\FresnsUserBlocks\FresnsUserBlocksConfig;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollows;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollowsConfig;
use App\Fresns\Api\FsDb\FresnsUserIcons\FresnsUserIcons;
use App\Fresns\Api\FsDb\FresnsUserIcons\FresnsUserIconsConfig;
use App\Fresns\Api\FsDb\FresnsUserLikes\FresnsUserLikes;
use App\Fresns\Api\FsDb\FresnsUserLikes\FresnsUserLikesConfig;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRoles;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRolesService;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsUserStats\FresnsUserStats;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Fresns\Api\Helpers\ApiLanguageHelper;
use App\Fresns\Api\Helpers\DateHelper;
use Illuminate\Support\Facades\DB;

class FsService
{
    public function common($uid, $langTag, $isMe)
    {
        // User SEO Info
        $seoInfoArr = DB::table('seo')->where('linked_type', 1)->where('linked_id', $uid)->where('deleted_at', null)->where('lang_tag', $langTag)->get(['title', 'keywords', 'description'])->first();
        if (empty($seoInfoArr)) {
            $defaultLangTag = ApiLanguageHelper::getDefaultLanguage();
            $seoInfoArr = DB::table('seo')->where('linked_type', 1)->where('linked_id', $uid)->where('deleted_at', null)->where('lang_tag', $defaultLangTag)->get(['title', 'keywords', 'description'])->first();
        }
        $data['seoInfo'] = $seoInfoArr;

        // Manages
        // plugin_usages > type=5 + scene ⊇ 3
        // plugin_usages > roles If the value is empty, then output all; if there is a value, determine whether all the associated role ids of the current request user are in the field configuration.
        $pluginUsagesArr = FresnsPluginUsages::where('type', 5)->where('scene', 'LIKE', '%3%')->get()->toArray();
        $managesArr = [];
        if (! empty($pluginUsagesArr)) {
            foreach ($pluginUsagesArr as $v) {
                if (! empty($v['roles'])) {
                    $rolesArr = explode(',', $v['roles']);
                    if (! in_array($uid, $rolesArr)) {
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

        // User Features
        // plugin_usages > type=7
        // plugin_usages > roles (Permission judgment)
        $features = [];
        if ($isMe == true) {
            $pluginUsagesArr = FresnsPluginUsages::where('type', 7)->get()->toArray();
            if (! empty($pluginUsagesArr)) {
                foreach ($pluginUsagesArr as $v) {
                    if (! empty($v['roles'])) {
                        $rolesArr = explode(',', $v['roles']);
                        if (! in_array($uid, $rolesArr)) {
                            continue;
                        }
                    }
                    $item = [];
                    $item['plugin'] = $v['plugin_unikey'];
                    $item['name'] = FresnsLanguagesService::getLanguageByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $v['id'], $langTag);
                    $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['icon_file_id'], $v['icon_file_url']);
                    $item['url'] = FresnsPluginsService::getPluginUsagesUrl($v['plugin_unikey'], $v['id']);
                    $pluginBadges = FresnsPluginBadges::where('plugin_unikey', $v['plugin_unikey'])->where('user_id', $uid)->first();
                    $item['badgesType'] = $pluginBadges['display_type'] ?? null;
                    $item['badgesValue'] = $pluginBadges['value_text'] ?? null;
                    $features[] = $item;
                }
            }
        }

        $data['features'] = $features;

        // User Profiles
        // plugin_usages > type=8
        // plugin_usages > roles (Permission judgment)
        $profiles = [];
        if ($isMe == true) {
            $pluginUsagesArr = FresnsPluginUsages::where('type', 8)->get()->toArray();
            if (! empty($pluginUsagesArr)) {
                foreach ($pluginUsagesArr as $v) {
                    if (! empty($v['roles'])) {
                        $rolesArr = explode(',', $v['roles']);
                        if (! in_array($uid, $rolesArr)) {
                            continue;
                        }
                    }
                    $item = [];
                    $item['plugin'] = $v['plugin_unikey'];
                    $item['name'] = FresnsLanguagesService::getLanguageByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $v['id'], $langTag);
                    $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['icon_file_id'], $v['icon_file_url']);
                    $item['url'] = FresnsPluginsService::getPluginUsagesUrl($v['plugin_unikey'], $v['id']);
                    $pluginBadges = FresnsPluginBadges::where('plugin_unikey', $v['plugin_unikey'])->where('user_id', $uid)->first();
                    $item['badgesType'] = $pluginBadges['display_type'] ?? null;
                    $item['badgesValue'] = $pluginBadges['value_text'] ?? null;
                    $profiles[] = $item;
                }
            }
        }
        $data['profiles'] = $profiles;

        return $data;
    }

    // Get User List
    public static function getUserList($request)
    {
        $viewUid = $request->input('viewUid');
        $viewType = $request->input('viewType');
        $pageSize = $request->input('pageSize', 20);
        $page = $request->input('page', 1);
        if ($pageSize > 50) {
            $pageSize = 50;
        }
        $query = DB::table('users as me');
        $query = $query->select('me.*')->leftJoin('user_stats as st', 'me.id', '=', 'st.user_id');

        if ($viewType) {
            switch ($viewType) {
                case 1:
                    $userIdArr = FresnsUserLikes::where('user_id', $viewUid)->where('like_type', 1)->pluck('like_id')->toArray();
                    break;
                case 2:
                    $userIdArr = FresnsUserFollows::where('user_id', $viewUid)->where('follow_type', 1)->pluck('follow_id')->toArray();
                    break;
                default:
                    $userIdArr = FresnsUserBlocks::where('user_id', $viewUid)->where('block_type', 1)->pluck('block_id')->toArray();
                    break;
            }
            $query->whereIn('me.id', $userIdArr);
        }

        $item = $query->paginate($pageSize, ['*'], 'page', $page);

        $data = [];
        $data['list'] = FresnsUserListsResource::collection($item->items())->toArray($item->items());
        $pagination['total'] = $item->total();
        $pagination['current'] = $page;
        $pagination['pageSize'] = $pageSize;
        $pagination['lastPage'] = $item->lastPage();
        $data['pagination'] = $pagination;

        return $data;
    }

    // Get User Detail
    public function getUserDetail($uid, $viewUid, $isMe, $langTag)
    {
        $user = FresnsUsers::where('id', $viewUid)->first();

        $data = [];
        if ($user) {
            $data['uid'] = $user['uid'];
            $data['username'] = $user['username'];
            $data['nickname'] = $user['nickname'];
            $roleIdArr = FresnsUserRoles::where('user_id', $user['id'])->pluck('role_id')->toArray();
            $roleId = FresnsUserRolesService::getUserRoles($user['id']);
            $userRole = FresnsRoles::where('id', $roleId)->first();
            $data['rid'] = null;
            $data['nicknameColor'] = null;
            $data['roleName'] = null;
            $data['roleNameDisplay'] = null;
            $data['roleIcon'] = null;
            $data['roleIconDisplay'] = null;
            if ($userRole) {
                $data['rid'] = $userRole['id'];
                $data['nicknameColor'] = $userRole['nickname_color'];
                $data['roleName'] = FresnsLanguagesService::getLanguageByTableId(FresnsRolesConfig::CFG_TABLE, 'name', $userRole['id'], $langTag);
                $data['roleNameDisplay'] = $userRole['is_display_name'];
                $data['roleIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($userRole['icon_file_id'], $userRole['icon_file_url']);
                $data['roleIconDisplay'] = $userRole['is_display_icon'];
            }
            $accounts = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('id', $user['account_id'])->first();

            if (empty($accounts->deleted_at)) {
                if (empty($user['avatar_file_url']) && empty($user['avatar_file_id'])) {
                    $defaultAvatar = ApiConfigHelper::getConfigByItemKey('default_avatar');
                    $userAvatar = ApiFileHelper::getImageAvatarUrl($defaultAvatar);
                } else {
                    $userAvatar = ApiFileHelper::getImageAvatarUrlByFileIdUrl($user['avatar_file_id'], $user['avatar_file_url']);
                }
            } else {
                $deactivateAvatar = ApiConfigHelper::getConfigByItemKey('deactivate_avatar');
                $userAvatar = ApiFileHelper::getImageAvatarUrl($deactivateAvatar);
            }
            $data['avatar'] = $userAvatar;
            $data['decorate'] = ApiFileHelper::getImageSignUrlByFileIdUrl($user['decorate_file_id'], $user['decorate_file_url']);
            $data['gender'] = $user['gender'];
            $data['birthday'] = DateHelper::fresnsOutputTimeToTimezone($user['birthday']);
            $data['bio'] = $user['bio'];
            $data['location'] = $user['location'];
            $data['dialogLimit'] = $user['dialog_limit'];
            $data['timezone'] = $user['timezone'];
            $data['language'] = $user['language'];
            $data['expiredTime'] = DateHelper::fresnsOutputTimeToTimezone($user['expired_at']);
            $data['verifiedStatus'] = $user['verified_status'];
            $data['verifiedIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($user['verified_file_id'], $user['verified_file_url']);
            $data['verifiedDesc'] = $user['verified_desc'];
            $data['lastEditUsername'] = $user['last_username_at'];
            $data['lastEditNickname'] = $user['last_nickname_at'];
            $data['createdTime'] = DateHelper::fresnsOutputTimeToTimezone($user['created_at']);
            $data['status'] = $user['is_enable'];
            $userRolesArr = FresnsRoles::whereIn('id', $roleIdArr)->get()->toArray();
            $rolesArr = [];
            foreach ($userRolesArr as $v) {
                $item = [];
                $item['type'] = FresnsUserRoles::where('user_id', $uid)->where('role_id', $v['id'])->value('type');
                $item['rid'] = $v['id'];
                $item['name'] = FresnsLanguagesService::getLanguageByTableId(FresnsRolesConfig::CFG_TABLE, 'name', $v['id'], $langTag);
                $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['icon_file_id'], $v['icon_file_url']);
                $item['nicknameColor'] = $v['nickname_color'];
                $item['permission'] = json_decode($v['permission'], true);
                $rolesArr[] = $item;
            }
            $data['roles'] = $rolesArr;
            $userStats = FresnsUserStats::where('user_id', $viewUid)->first();
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

            // extcredits1
            $stats['extcredits1Status'] = ApiConfigHelper::getConfigByItemKey('extcredits1_status');
            $stats['extcredits1Name'] = ApiConfigHelper::getConfigByItemKey('extcredits1_name');
            $stats['extcredits1Unit'] = ApiConfigHelper::getConfigByItemKey('extcredits1_unit');
            if ($stats['extcredits1Status'] == 3) {
                $stats['extcredits1'] = $userStats['extcredits1'];
            }
            // extcredits2
            $stats['extcredits2Status'] = ApiConfigHelper::getConfigByItemKey('extcredits2_status');
            $stats['extcredits2Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits2_name', $langTag);
            $stats['extcredits2Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits2_unit', $langTag);
            if ($stats['extcredits2Status'] == 3) {
                $stats['extcredits2'] = $userStats['extcredits2'];
            }
            // extcredits3
            $stats['extcredits3Status'] = ApiConfigHelper::getConfigByItemKey('extcredits3_status');
            $stats['extcredits3Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits3_name', $langTag);
            $stats['extcredits3Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits3_unit', $langTag);
            if ($stats['extcredits3Status'] == 3) {
                $stats['extcredits3'] = $userStats['extcredits3'];
            }
            // extcredits4
            $stats['extcredits4Status'] = ApiConfigHelper::getConfigByItemKey('extcredits4_status');
            $stats['extcredits4Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits4_name', $langTag);
            $stats['extcredits4Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits4_unit', $langTag);
            if ($stats['extcredits4Status'] == 3) {
                $stats['extcredits4'] = $userStats['extcredits4'];
            }
            // extcredits5
            $stats['extcredits5Status'] = ApiConfigHelper::getConfigByItemKey('extcredits5_status');
            $stats['extcredits5Name'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits5_name', $langTag);
            $stats['extcredits5Unit'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'extcredits5_unit', $langTag);
            if ($stats['extcredits5Status'] == 3) {
                $stats['extcredits5'] = $userStats['extcredits5'];
            }
            $data['stats'] = $stats;

            $userIconsArr = FresnsUserIcons::where('user_id', $viewUid)->get()->toArray();
            $iconsArr = [];
            foreach ($userIconsArr as $v) {
                $item = [];
                $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['icon_file_id'], $v['icon_file_url']);
                $item['name'] = FresnsLanguagesService::getLanguageByTableId(FresnsUserIconsConfig::CFG_TABLE, 'name', $v['id'], $langTag);
                $item['type'] = $v['type'];
                $item['url'] = FresnsPluginsService::getPluginUrlByUnikey($v['plugin_unikey']);
                $iconsArr[] = $item;
            }
            $data['icons'] = $iconsArr;

            $data['draftCount'] = null;
            if ($isMe == true) {
                $draftCount['posts'] = FresnsPostLogs::where('user_id', $user['id'])->whereIn('state', [1, 4])->count();
                $draftCount['comments'] = FresnsCommentLogs::where('user_id', $user['id'])->whereIn('state', [1, 4])->count();
                $data['draftCount'] = $draftCount;
            }

            $data['userName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'user_name', $langTag);
            $data['userUidName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'user_uid_name', $langTag);
            $data['userUsernameName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'user_username_name', $langTag);
            $data['userNicknameName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'user_nickname_name', $langTag);
            $data['userRoleName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'user_role_name', $langTag);

            $data['followSetting'] = ApiConfigHelper::getConfigByItemKey('follow_user_setting');
            $data['followName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'follow_user_name', $langTag);
            $data['followStatus'] = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 1)->where('follow_id', $viewUid)->where('deleted_at', null)->count();
            $data['followMeStatus'] = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $viewUid)->where('follow_type', 1)->where('follow_id', $uid)->where('deleted_at', null)->count();

            $data['likeSetting'] = ApiConfigHelper::getConfigByItemKey('like_user_setting');
            $data['likeName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'like_user_name', $langTag);
            $data['likeStatus'] = DB::table(FresnsUserLikesConfig::CFG_TABLE)->where('user_id', $uid)->where('like_type', 1)->where('like_id', $viewUid)->where('deleted_at', null)->count();

            $data['blockSetting'] = ApiConfigHelper::getConfigByItemKey('block_user_setting');
            $data['blockName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'block_user_name', $langTag);
            $data['blockStatus'] = DB::table(FresnsUserBlocksConfig::CFG_TABLE)->where('user_id', $uid)->where('block_type', 1)->where('block_id', $viewUid)->where('deleted_at', null)->count();

            if ($isMe = false) {
                $unikeyArr = FresnsPluginBadges::where('user_id', $uid)->pluck('plugin_unikey')->toArray();
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
        $viewUid = $request->input('viewUid');
        $viewType = $request->input('viewType');
        switch ($viewType) {
            case 1:
                $groupArr = FresnsUserLikes::where('user_id', $viewUid)->where('like_type', 2)->pluck('like_id')->toArray();
                break;
            case 2:
                $groupArr = FresnsUserFollows::where('user_id', $viewUid)->where('follow_type', 2)->pluck('follow_id')->toArray();
                break;
            default:
                $groupArr = FresnsUserBlocks::where('user_id', $viewUid)->where('block_type', 2)->pluck('block_id')->toArray();
                break;
        }

        return $groupArr;
    }

    // Get Hashtag List
    public static function getHashtagList($request)
    {
        $viewUid = $request->input('viewUid');
        $viewType = $request->input('viewType');
        switch ($viewType) {
            case 1:
                $hashtagArr = FresnsUserLikes::where('user_id', $viewUid)->where('like_type', 3)->pluck('like_id')->toArray();
                break;
            case 2:
                $hashtagArr = FresnsUserFollows::where('user_id', $viewUid)->where('follow_type', 3)->pluck('follow_id')->toArray();
                break;
            default:
                $hashtagArr = FresnsUserBlocks::where('user_id', $viewUid)->where('block_type', 3)->pluck('block_id')->toArray();
                break;
        }

        return $hashtagArr;
    }

    // Get Post List
    public static function getPostList($request)
    {
        $viewUid = $request->input('viewUid');
        $viewType = $request->input('viewType');
        switch ($viewType) {
            case 1:
                $postArr = FresnsUserLikes::where('user_id', $viewUid)->where('like_type', 4)->pluck('like_id')->toArray();
                break;
            case 2:
                $postArr = FresnsUserFollows::where('user_id', $viewUid)->where('follow_type', 4)->pluck('follow_id')->toArray();
                break;
            default:
                $postArr = FresnsUserBlocks::where('user_id', $viewUid)->where('block_type', 4)->pluck('block_id')->toArray();
                break;
        }

        return $postArr;
    }

    // Get Comment List
    public static function getCommentList($request)
    {
        $viewUid = $request->input('viewUid');
        $viewType = $request->input('viewType');
        switch ($viewType) {
            case 1:
                $commentArr = FresnsUserLikes::where('user_id', $viewUid)->where('like_type', 5)->pluck('like_id')->toArray();
                break;
            case 2:
                $commentArr = FresnsUserFollows::where('user_id', $viewUid)->where('follow_type', 5)->pluck('follow_id')->toArray();
                break;
            default:
                $commentArr = FresnsUserBlocks::where('user_id', $viewUid)->where('block_type', 5)->pluck('block_id')->toArray();
                break;
        }

        return $commentArr;
    }
}
