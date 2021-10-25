<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsGroups;

use App\Http\Center\Common\GlobalService;
use App\Http\FresnsApi\Base\FresnsBaseService;
use App\Http\FresnsApi\Content\FsConfig as ContentConfig;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollows;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsPluginBadges\FresnsPluginBadges;
use App\Http\FresnsDb\FresnsPlugins\FresnsPluginsService;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsagesConfig;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsagesService;
use Illuminate\Support\Facades\DB;

class FresnsGroupsService extends FresnsBaseService
{
    public $needCommon = true;

    public function __construct()
    {
        $this->config = new FsConfig();
        $this->model = new FsModel();
        $this->resource = FsResource::class;
        $this->resourceDetail = FsResourceDetail::class;
    }

    // Group Common
    public function common()
    {
        // Group SEO Info
        $id = request()->input('gid');
        $langTag = request()->header('langTag');
        $mid = GlobalService::getGlobalKey('member_id');
        $group = FresnsGroups::where('uuid', $id)->first();
        $common['seoInfo'] = [];
        if (! $langTag) {
            $langTag = FresnsPluginUsagesService::getDefaultLanguage();
        }
        if ($group) {
            $seo = DB::table('seo')->where('linked_type', 2)->where('linked_id', $group['id'])->where('lang_tag', $langTag)->where('deleted_at', null)->first();
            $seoInfo = [];
            if ($seo) {
                $seoInfo['title'] = $seo->title;
                $seoInfo['keywords'] = $seo->keywords;
                $seoInfo['description'] = $seo->description;
                $common['seoInfo'] = $seoInfo;
            }
        }

        // Group Plugin Extensions
        $extendsArr = [];
        if ($group) {
            $pluginUsagesArr = FresnsPluginUsages::where('type', 6)->where('group_id', $group['id'])->get();
            if ($pluginUsagesArr) {
                foreach ($pluginUsagesArr as $pluginUsages) {
                    $extends = [];
                    $pluginBadges = FresnsPluginBadges::where('plugin_unikey', $pluginUsages['plugin_unikey'])->first();
                    $extends['plugin'] = $pluginUsages['plugin_unikey'] ?? '';
                    $manages['name'] = ApiLanguageHelper::getLanguagesByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $pluginUsages['id']);
                    $extends['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($pluginUsages['icon_file_id'], $pluginUsages['icon_file_url']);
                    $extends['url'] = FresnsPluginsService::getPluginUsagesUrl($pluginUsages['plugin_unikey'], $pluginUsages['id']);
                    $extends['badgesType'] = $pluginBadges['display_type'] ?? '';
                    $extends['badgesValue'] = ($pluginBadges['value_text'] ?? '') ?? ($pluginBadges['value_number'] ?? '');
                    // Determine if a member role has permissions
                    if ($pluginUsages['member_roles']) {
                        $member_roles = $pluginUsages['member_roles'];
                        $memberRoleArr = FresnsMemberRoleRels::where('member_id', $mid)->pluck('role_id')->toArray();
                        $memberPluginRolesArr = explode(',', $member_roles);
                        $status = array_intersect($memberRoleArr, $memberPluginRolesArr);
                        if (! $status) {
                            $extends = [];
                        }
                    }
                    $extendsArr[] = $extends;
                }
            }
        }
        $common['extensions'] = $extendsArr;
        $common['seoInfo'] = (object) $common['seoInfo'];
        // $common['extensions'] = $common['extensions'];

        return $common;
    }

    // Permission data
    public static function publishRule($mid, $permission, $group_id)
    {
        $permissionArr = json_decode($permission, true);
        $admin_member = $permissionArr['admin_members'];
        $publish_post = $permissionArr['publish_post'];
        $publish_post_roles = $permissionArr['publish_post_roles'];
        $publish_post_review = $permissionArr['publish_post_review'];
        $publish_comment = $permissionArr['publish_comment'];
        $publish_comment_roles = $permissionArr['publish_comment_roles'];
        $publish_comment_review = $permissionArr['publish_comment_review'];

        $adminMemberArr = [];
        if ($admin_member) {
            foreach ($admin_member as $a) {
                $array = [];
                $memberInfo = FresnsMembers::find($a);
                if ($memberInfo) {
                    $array['mid'] = $memberInfo['uuid'];
                    $array['mname'] = $memberInfo['name'];
                    $array['nickname'] = $memberInfo['nickname'];
                    $array['nicknameColor'] = $memberInfo['uuid'];
                    // Member Role Association Table
                    $roleRels = FresnsMemberRoleRels::where('member_id', $memberInfo['id'])->first();
                    if (! empty($roleRels)) {
                        $memberRole = FresnsMemberRoles::find($roleRels['role_id']);
                    }
                    $array['nicknameColor'] = $memberRole['nickname_color'] ?? '';
                    $array['avatar'] = $memberInfo['avatar_file_url'];
                    $adminMemberArr[] = $array;
                }
            }
        }
        $arr['adminMemberArr'] = $adminMemberArr;

        // Posts
        // Whether the member currently requesting the interface has permission to post to the group
        $publishRule = [];
        $publishRule['allowPost'] = false;
        // 1.All Members
        if ($publish_post == 1) {
            $publishRule['allowPost'] = true;
        }
        // 2.Anyone in the group
        if ($publish_post == 2) {
            $followCount = FresnsMemberFollows::where('member_id', $mid)->where('follow_type', 2)->where('follow_id',
                $group_id)->count();
            if ($followCount > 0) {
                $publishRule['allowPost'] = true;
            }
        }
        // 3.Specified role members only
        if ($publish_post == 3) {
            $memberRoleArr = FresnsMemberRoleRels::where('member_id', $mid)->pluck('role_id')->toArray();
            $arrIntersect = array_intersect($memberRoleArr, $publish_post_roles);
            if ($arrIntersect) {
                $publishRule['allowPost'] = true;
            }
        }
        // Members of the current request interface, whether the post needs to be reviewed (if it is an administrator, no review is required)
        $publishRule['reviewPost'] = true;
        if ($publish_post_review == 0) {
            $publishRule['reviewPost'] = false;
        }
        if ($admin_member) {
            if (in_array($mid, $admin_member)) {
                $publishRule['reviewPost'] = false;
            }
        }

        // Comments
        // Whether the member currently requesting the interface has permission to comment to the group
        $publishRule['allowComment'] = false;
        // 1.All Members
        if ($publish_comment == 1) {
            $publishRule['allowComment'] = true;
        }
        // 2.Anyone in the group
        if ($publish_comment == 2) {
            $followCount = FresnsMemberFollows::where('member_id', $mid)->where('follow_type', 2)->where('follow_id',
                $group_id)->count();
            if ($followCount > 0) {
                $publishRule['allowComment'] = true;
            }
        }
        // 3.Specified role members only
        if ($publish_comment == 3) {
            $memberRoleArr = FresnsMemberRoleRels::where('member_id', $mid)->pluck('role_id')->toArray();
            $arrIntersect = array_intersect($memberRoleArr, $publish_comment_roles);
            if ($arrIntersect) {
                $publishRule['allowComment'] = true;
            }
        }
        // Members of the current request interface, whether the comment needs to be reviewed (if it is an administrator, no review is required)
        $publishRule['reviewComment'] = true;
        if ($publish_comment_review == 0) {
            $publishRule['reviewComment'] = false;
        }
        if ($admin_member) {
            if (in_array($mid, $admin_member)) {
                $publishRule['reviewComment'] = false;
            }
        }

        return $publishRule;
    }

    // Group Administrator Data
    public static function adminData($permission)
    {
        $permissionArr = json_decode($permission, true);
        $admin_member = $permissionArr['admin_members'];
        $publish_post = $permissionArr['publish_post'];
        $publish_post_roles = $permissionArr['publish_post_roles'];
        $publish_post_review = $permissionArr['publish_post_review'];
        $publish_comment = $permissionArr['publish_comment'];
        $publish_comment_roles = $permissionArr['publish_comment_roles'];
        $publish_comment_review = $permissionArr['publish_comment_review'];

        $adminMemberArr = [];
        if ($admin_member) {
            foreach ($admin_member as $a) {
                $array = [];
                $memberInfo = FresnsMembers::find($a);
                if ($memberInfo) {
                    $array['mid'] = $memberInfo['uuid'];
                    $array['mname'] = $memberInfo['name'];
                    $array['nickname'] = $memberInfo['nickname'];
                    $array['nicknameColor'] = $memberInfo['uuid'];
                    // Member Role Association Table
                    $roleRels = FresnsMemberRoleRels::where('member_id', $memberInfo['id'])->first();
                    if (! empty($roleRels)) {
                        $memberRole = FresnsMemberRoles::find($roleRels['role_id']);
                    }
                    $array['nicknameColor'] = $memberRole['nickname_color'] ?? '';
                    // $array['avatar'] = $memberInfo['avatar_file_url'];
                    $avatar = $memberInfo['avatar_file_url'] ?? '';
                    // Empty with default avatar
                    if (empty($avatar)) {
                        $defaultIcon = ApiConfigHelper::getConfigByItemKey(ContentConfig::DEFAULT_AVATAR);
                        $avatar = $defaultIcon;
                    }
                    $avatar = ApiFileHelper::getImageAvatarUrl($avatar);
                    $array['avatar'] = $avatar;
                    $adminMemberArr[] = $array;
                }
            }
        }

        return $adminMemberArr;
    }

    // Other pession
    public static function othetPession($permission)
    {
        $permissionArr = json_decode($permission, true);
        $arr = [];
        if (! $permissionArr) {
            return $arr;
        }
        unset($permissionArr['admin_members']);
        unset($permissionArr['publish_post']);
        // unset($permissionArr['publish_post_roles']);
        unset($permissionArr['publish_post_review']);
        unset($permissionArr['publish_comment']);
        // unset($permissionArr['publish_comment_roles']);
        unset($permissionArr['publish_comment_review']);

        return $permissionArr;
    }
}
