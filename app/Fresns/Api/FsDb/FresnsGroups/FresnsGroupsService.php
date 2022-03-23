<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsGroups;

use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\Http\Base\FsApiService;
use App\Fresns\Api\Http\Content\FsConfig as ContentConfig;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Fresns\Api\Helpers\ApiLanguageHelper;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollows;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRoles;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRoles;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsPluginBadges\FresnsPluginBadges;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPluginsService;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsagesConfig;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsagesService;
use Illuminate\Support\Facades\DB;

class FresnsGroupsService extends FsApiService
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
        $uid = GlobalService::getGlobalKey('user_id');
        $group = FresnsGroups::where('gid', $id)->first();
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
                    $extends['plugin'] = $pluginUsages['plugin_unikey'] ?? null;
                    $manages['name'] = ApiLanguageHelper::getLanguagesByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $pluginUsages['id']);
                    $extends['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($pluginUsages['icon_file_id'], $pluginUsages['icon_file_url']);
                    $extends['url'] = FresnsPluginsService::getPluginUsagesUrl($pluginUsages['plugin_unikey'], $pluginUsages['id']);
                    $extends['badgesType'] = $pluginBadges['display_type'] ?? null;
                    $extends['badgesValue'] = ($pluginBadges['value_text'] ?? null) ?? ($pluginBadges['value_number'] ?? null);
                    // Determine if a user role has permissions
                    if ($pluginUsages['roles']) {
                        $roles = $pluginUsages['roles'];
                        $userRoleArr = FresnsUserRoles::where('user_id', $uid)->pluck('role_id')->toArray();
                        $userPluginRolesArr = explode(',', $roles);
                        $status = array_intersect($userRoleArr, $userPluginRolesArr);
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
    public static function publishRule($uid, $permission, $group_id)
    {
        $permissionArr = json_decode($permission, true);
        $admin_user = $permissionArr['admin_users'];
        $publish_post = $permissionArr['publish_post'];
        $publish_post_roles = $permissionArr['publish_post_roles'];
        $publish_post_review = $permissionArr['publish_post_review'];
        $publish_comment = $permissionArr['publish_comment'];
        $publish_comment_roles = $permissionArr['publish_comment_roles'];
        $publish_comment_review = $permissionArr['publish_comment_review'];

        $adminUserArr = [];
        if ($admin_user) {
            foreach ($admin_user as $a) {
                $array = [];
                $userInfo = FresnsUsers::find($a);
                if ($userInfo) {
                    $array['uid'] = $userInfo['uid'];
                    $array['username'] = $userInfo['username'];
                    $array['nickname'] = $userInfo['nickname'];
                    $array['nicknameColor'] = $userInfo['uid'];
                    // User Role Association Table
                    $roleRels = FresnsUserRoles::where('user_id', $userInfo['id'])->first();
                    if (! empty($roleRels)) {
                        $userRole = FresnsRoles::find($roleRels['role_id']);
                    }
                    $array['nicknameColor'] = $userRole['nickname_color'] ?? null;
                    $array['avatar'] = $userInfo['avatar_file_url'];
                    $adminUserArr[] = $array;
                }
            }
        }
        $arr['adminUserArr'] = $adminUserArr;

        // Posts
        // Whether the user currently requesting the interface has permission to post to the group
        $publishRule = [];
        $publishRule['allowPost'] = false;
        // 1.All Users
        if ($publish_post == 1) {
            $publishRule['allowPost'] = true;
        }
        // 2.Anyone in the group
        if ($publish_post == 2) {
            $followCount = FresnsUserFollows::where('user_id', $uid)->where('follow_type', 2)->where('follow_id',
                $group_id)->count();
            if ($followCount > 0) {
                $publishRule['allowPost'] = true;
            }
        }
        // 3.Specified role users only
        if ($publish_post == 3) {
            $userRoleArr = FresnsUserRoles::where('user_id', $uid)->pluck('role_id')->toArray();
            $arrIntersect = array_intersect($userRoleArr, $publish_post_roles);
            if ($arrIntersect) {
                $publishRule['allowPost'] = true;
            }
        }
        // Users of the current request interface, whether the post needs to be reviewed (if it is an administrator, no review is required)
        $publishRule['reviewPost'] = true;
        if ($publish_post_review == 0) {
            $publishRule['reviewPost'] = false;
        }
        if ($admin_user) {
            if (in_array($uid, $admin_user)) {
                $publishRule['reviewPost'] = false;
            }
        }

        // Comments
        // Whether the user currently requesting the interface has permission to comment to the group
        $publishRule['allowComment'] = false;
        // 1.All Users
        if ($publish_comment == 1) {
            $publishRule['allowComment'] = true;
        }
        // 2.Anyone in the group
        if ($publish_comment == 2) {
            $followCount = FresnsUserFollows::where('user_id', $uid)->where('follow_type', 2)->where('follow_id',
                $group_id)->count();
            if ($followCount > 0) {
                $publishRule['allowComment'] = true;
            }
        }
        // 3.Specified role users only
        if ($publish_comment == 3) {
            $userRoleArr = FresnsUserRoles::where('user_id', $uid)->pluck('role_id')->toArray();
            $arrIntersect = array_intersect($userRoleArr, $publish_comment_roles);
            if ($arrIntersect) {
                $publishRule['allowComment'] = true;
            }
        }
        // Users of the current request interface, whether the comment needs to be reviewed (if it is an administrator, no review is required)
        $publishRule['reviewComment'] = true;
        if ($publish_comment_review == 0) {
            $publishRule['reviewComment'] = false;
        }
        if ($admin_user) {
            if (in_array($uid, $admin_user)) {
                $publishRule['reviewComment'] = false;
            }
        }

        return $publishRule;
    }

    // Group Administrator Data
    public static function adminData($permission)
    {
        $permissionArr = json_decode($permission, true);
        $admin_user = $permissionArr['admin_users'];
        $publish_post = $permissionArr['publish_post'];
        $publish_post_roles = $permissionArr['publish_post_roles'];
        $publish_post_review = $permissionArr['publish_post_review'];
        $publish_comment = $permissionArr['publish_comment'];
        $publish_comment_roles = $permissionArr['publish_comment_roles'];
        $publish_comment_review = $permissionArr['publish_comment_review'];

        $adminUserArr = [];
        if ($admin_user) {
            foreach ($admin_user as $a) {
                $array = [];
                $userInfo = FresnsUsers::find($a);
                if ($userInfo) {
                    $array['uid'] = $userInfo['uid'];
                    $array['username'] = $userInfo['username'];
                    $array['nickname'] = $userInfo['nickname'];
                    $array['nicknameColor'] = $userInfo['uid'];
                    // User Role Association Table
                    $roleRels = FresnsUserRoles::where('user_id', $userInfo['id'])->first();
                    if (! empty($roleRels)) {
                        $userRole = FresnsRoles::find($roleRels['role_id']);
                    }
                    $array['nicknameColor'] = $userRole['nickname_color'] ?? null;
                    // $array['avatar'] = $userInfo['avatar_file_url'];
                    $avatar = $userInfo['avatar_file_url'] ?? null;
                    // Empty with default avatar
                    if (empty($avatar)) {
                        $defaultIcon = ApiConfigHelper::getConfigByItemKey(ContentConfig::DEFAULT_AVATAR);
                        $avatar = $defaultIcon;
                    }
                    $avatar = ApiFileHelper::getImageAvatarUrl($avatar);
                    $array['avatar'] = $avatar;
                    $adminUserArr[] = $array;
                }
            }
        }

        return $adminUserArr;
    }

    // Other pession
    public static function othetPession($permission)
    {
        $permissionArr = json_decode($permission, true);
        $arr = [];
        if (! $permissionArr) {
            return $arr;
        }
        unset($permissionArr['admin_users']);
        unset($permissionArr['publish_post']);
        // unset($permissionArr['publish_post_roles']);
        unset($permissionArr['publish_post_review']);
        unset($permissionArr['publish_comment']);
        // unset($permissionArr['publish_comment_roles']);
        unset($permissionArr['publish_comment_review']);

        return $permissionArr;
    }
}
