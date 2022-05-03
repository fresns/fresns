<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Content;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Fresns\Api\FsDb\FresnsExtendLinkeds\FresnsExtendLinkedsConfig;
use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtends;
use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtendsConfig;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroups;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroupsConfig;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPluginsService;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsagesConfig;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsagesService;
use App\Fresns\Api\FsDb\FresnsPostAllows\FresnsPostAllowsConfig;
use App\Fresns\Api\FsDb\FresnsPostAppends\FresnsPostAppendsConfig;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPostsConfig;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPostsService;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRoles;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRolesConfig;
use App\Fresns\Api\FsDb\FresnsUserBlocks\FresnsUserBlocksConfig;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollowsConfig;
use App\Fresns\Api\FsDb\FresnsUserIcons\FresnsUserIcons;
use App\Fresns\Api\FsDb\FresnsUserIcons\FresnsUserIconsConfig;
use App\Fresns\Api\FsDb\FresnsUserLikes\FresnsUserLikesConfig;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRoles;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Fresns\Api\Helpers\ApiLanguageHelper;
use App\Fresns\Api\Helpers\ArrayHelper;
use App\Fresns\Api\Helpers\DateHelper;
use App\Helpers\ConfigHelper;
use Illuminate\Support\Facades\DB;

/**
 * Detail resource config handle.
 */
class FresnsPostsResourceDetail extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Field

        // Data Table: post_appends
        $append = DB::table(FresnsPostAppendsConfig::CFG_TABLE)->where('post_id', $this->id)->first();
        if ($append) {
            $append = get_object_vars($append);
        }
        // Data Table: users
        $userInfo = DB::table(FresnsUsersConfig::CFG_TABLE)->where('id', $this->user_id)->first();
        // Data Table: user_roles
        $roleRels = FresnsUserRoles::where('user_id', $this->user_id)->where('is_main', 1)->first();
        // Data Table: roles
        $userRole = [];
        if (! empty($roleRels)) {
            $userRole = FresnsRoles::find($roleRels['role_id']);
        }
        // Data Table: comments
        $comments = DB::table('comments as c')->select('c.*')
            ->leftJoin('users as m', 'c.user_id', '=', 'm.id')
            ->where('c.post_id', $this->id)
            ->where('m.deleted_at', null)
            ->where('c.deleted_at', null)
            ->orderby('like_count', 'Desc')
            ->first();
        // Data Table: groups
        $groupInfo = FresnsGroups::find($this->group_id);

        // Post Info
        $pid = $this->pid;
        $uid = GlobalService::getGlobalKey('user_id');
        $input = [
            'user_id' => $uid,
            'like_type' => 4,
            'like_id' => $this->id,
        ];
        // $count = FresnsUserLikes::where($input)->count();
        $count = DB::table(FresnsUserLikesConfig::CFG_TABLE)->where($input)->count();
        $isLike = $count == 0 ? false : true;
        $title = $this->title;
        $content = FresnsPostsResource::getContentView(($append['content']), ($this->id), 1, $append['is_markdown']);
        // Read permission required or not
        $allowStatus = $this->is_allow;
        $allowProportion = 10;
        $noAllow = 0;
        // dump($allowStatus);
        if ($allowStatus == 1) {
            $userCount = DB::table(FresnsPostAllowsConfig::CFG_TABLE)->where('post_id', $this->id)->where('type', 1)->where('object_id', $uid)->count();
            $userRoleCount = 0;
            if (! empty($roleRels)) {
                $userRoleCount = DB::table(FresnsPostAllowsConfig::CFG_TABLE)->where('post_id', $this->id)->where('type', 2)->where('object_id', $roleRels['role_id'])->count();
            }
            // Read access
            if ($userCount > 0 || $userRoleCount > 0) {
                $allowStatus = 1;
                $allowProportion = 100;
                $noAllow = 1;
            } else {
                $allowProportion = $append['allow_proportion'];
                $FresnsPostsService = new FresnsPostsService();
                // Prevent @, hashtags, stickers, links and other messages from being truncated
                if ($allowProportion != 0 && ! empty($allowProportion)) {
                    $contentInfo = $FresnsPostsService->truncatedContentInfo($append['content'], mb_strlen($append['content']) * $allowProportion / 100);
                    $content = FresnsPostsResource::getContentView(($contentInfo['truncated_content']), ($this->id), 1, $append['is_markdown']);
                }
                $allowStatus = 0;
            }
        } else {
            $noAllow = 1;
        }
        $brief = $this->is_brief;
        $sticky = $this->sticky_state;
        $digest = $this->digest_state;

        // Operation behavior status
        $likeStatus = DB::table(FresnsUserLikesConfig::CFG_TABLE)->where('user_id', $uid)->where('like_type', 4)->where('like_id', $this->id)->where('deleted_at', null)->count();
        $followStatus = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 4)->where('follow_id', $this->id)->where('deleted_at', null)->count();
        $blockStatus = DB::table(FresnsUserBlocksConfig::CFG_TABLE)->where('user_id', $uid)->where('block_type', 4)->where('block_id', $this->id)->where('deleted_at', null)->count();
        // Operation behavior settings
        $likeSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::LIKE_POST_SETTING);
        $followSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::FOLLOW_POST_SETTING);
        $blockSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::SHIELD_POST_SETTING);
        // Operation behavior naming
        $likeName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::LIKE_POST_NAME) ?? 'Like';
        $followName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::FOLLOW_POST_NAME) ?? 'Save post';
        $blockName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::SHIELD_POST_NAME) ?? 'Hide post';
        // Content Naming
        $PostName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::POST_NAME) ?? 'Post';

        $viewCount = $this->view_count;
        $likeCount = $this->like_count;
        $followCount = $this->follow_count;
        $blockCount = $this->block_count;
        $commentCount = $this->comment_count;
        $commentLikeCount = $this->comment_like_count;
        $time = DateHelper::fresnsOutputTimeToTimezone($this->created_at);
        // $time = $this->created_at;
        $timeFormat = DateHelper::format_date_langTag(strtotime($this->created_at));
        // $editTime = $this->latest_edit_at;
        $editTime = DateHelper::fresnsOutputTimeToTimezone($this->latest_edit_at);
        $editTimeFormat = null;
        if (! empty($editTime)) {
            $editTimeFormat = DateHelper::format_date_langTag(strtotime($this->latest_edit_at));
        }
        $canDelete = $append['can_delete'];

        $allowStatus = $this->is_allow;
        $allowBtnName = ApiLanguageHelper::getLanguagesByTableId(FresnsPostsConfig::CFG_TABLE, 'allow_btn_name', $this->id);
        $allowBtnUrl = FresnsPluginsService::getPluginUrlByUnikey($append['allow_plugin_unikey']);

        $userListName = ApiLanguageHelper::getLanguagesByTableId(FresnsPostsConfig::CFG_TABLE, 'user_list_name', $this->id);
        $userListCount = Db::table('post_users')->where('post_id', $this->id)->count();
        $userListUrl = FresnsPluginsService::getPluginUrlByUnikey($append['user_list_plugin_unikey']);

        $user = [];
        $user['anonymous'] = $this->is_anonymous;
        $user['deactivate'] = false; //Not deactivated = false, Deactivated = true
        $user['uid'] = null;
        $user['username'] = null;
        $user['nickname'] = null;
        $user['rid'] = null;
        $user['nicknameColor'] = null;
        $user['roleName'] = null;
        $user['roleNameDisplay'] = null;
        $user['roleIcon'] = null;
        $user['roleIconDisplay'] = null;
        $user['avatar'] = ConfigHelper::fresnsConfigFileUrlByItemKey('anonymous_avatar');
        $user['decorate'] = null;
        $user['gender'] = null;
        $user['bio'] = null;
        $user['location'] = null;
        $user['verifiedStatus'] = null;
        $user['verifiedIcon'] = null;
        $user['verifiedDesc'] = null;
        $user['icons'] = [];
        if ($this->is_anonymous == 0) {
            if ($userInfo->deleted_at == null && $userInfo) {
                $user['anonymous'] = $this->is_anonymous;
                $user['deactivate'] = false;
                $user['uid'] = $userInfo->uid ?? null;
                $user['username'] = $userInfo->username ?? null;
                $user['nickname'] = $userInfo->nickname ?? null;
                $user['rid'] = $userRole['id'] ?? null;
                $user['nicknameColor'] = $userRole['nickname_color'] ?? null;
                $user['roleName'] = ApiLanguageHelper::getLanguagesByTableId(FresnsRolesConfig::CFG_TABLE, 'name', $userRole['id']);
                $user['roleNameDisplay'] = $userRole['is_display_name'] ?? 0;
                $user['roleIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($userRole['icon_file_id'], $userRole['icon_file_url']);
                $user['roleIconDisplay'] = $userRole['is_display_icon'] ?? 0;
                $user['avatar'] = ApiFileHelper::getUserAvatar($userInfo->uid);
                $user['decorate'] = ApiFileHelper::getImageSignUrlByFileIdUrl($userInfo->decorate_file_id, $userInfo->decorate_file_url);
                $user['gender'] = $userInfo->gender ?? 0;
                $user['bio'] = $userInfo->bio ?? null;
                $user['location'] = $userInfo->location ?? null;
                $user['verifiedStatus'] = $userInfo->verified_status ?? 1;
                $user['verifiedIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($userInfo->verified_file_id, $userInfo->verified_file_url);
                $user['verifiedDesc'] = $userInfo->verified_desc ?? null;

                $userIconsArr = FresnsUserIcons::where('user_id', $this->user_id)->get()->toArray();
                $iconsArr = [];
                foreach ($userIconsArr as $v) {
                    $item = [];
                    $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['icon_file_id'], $v['icon_file_url']);
                    $item['name'] = ApiLanguageHelper::getLanguagesByTableId(FresnsUserIconsConfig::CFG_TABLE, 'name', $v['id']);
                    $item['type'] = $v['type'];
                    $item['url'] = FresnsPluginsService::getPluginUrlByUnikey($v['plugin_unikey']);
                    $iconsArr[] = $item;
                }
                $user['icons'] = $iconsArr;
            }
        }

        // Post Hot
        $postHotStatus = ApiConfigHelper::getConfigByItemKey(FsConfig::POST_HOT);
        $postHotStatus = $postHotStatus == null ? 0 : $postHotStatus;
        $comment = [];
        $location = [];
        $location['isLbs'] = $this->is_lbs;
        $location['mapId'] = $this->map_id;
        $location['latitude'] = $this->map_latitude;
        $location['longitude'] = $this->map_longitude;
        $location['scale'] = $append['map_scale'];
        $location['poi'] = $append['map_poi'];
        $location['poiId'] = $append['map_poi_id'];
        $location['distance'] = null;
        $longitude = request()->input('longitude', '');
        $latitude = request()->input('latitude', '');
        $langTag = request()->header('langTag', '');
        if ($longitude && $latitude && $this->map_latitude && $this->map_longitude) {
            // Get location units
            $distanceUnits = request()->input('lengthUnits');
            if (! $distanceUnits) {
                // Distance
                $languages = ApiConfigHelper::distanceUnits($langTag);
                $distanceUnits = empty($languages) ? 'km' : $languages;
            }
            $location['distance'] = $this->GetDistance($latitude, $longitude, $this->map_latitude, $this->map_longitude, $distanceUnits);
        }

        // Attached Quantity
        $attachCount = [];
        // posts > more_json > files
        $attachCount['images'] = 0;
        $attachCount['videos'] = 0;
        $attachCount['audios'] = 0;
        $attachCount['documents'] = 0;
        $attachCount['extends'] = DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('linked_type', 2)->where('linked_id', $this->id)->count();
        $more_json_decode = json_decode($this->more_json, true);
        if ($more_json_decode) {
            if (isset($more_json_decode['files'])) {
                foreach ($more_json_decode['files'] as $m) {
                    if ($m['type'] == 1) {
                        $attachCount['images']++;
                    }
                    if ($m['type'] == 2) {
                        $attachCount['videos']++;
                    }
                    if ($m['type'] == 3) {
                        $attachCount['audios']++;
                    }
                    if ($m['type'] == 4) {
                        $attachCount['documents']++;
                    }
                }
            }
        }

        // Files
        $files = [];

        // Extends
        $extends = [];
        $extendsLinks = Db::table('extend_linkeds')->where('linked_type', 1)->where('linked_id', $this->id)->first();
        $extendsLinks = [];
        if ($extendsLinks) {
            $extendsLinks = array_unique($extendsLinks);
            $extendsInfo = FresnsExtends::whereIn('id', $extendsLinks)->get();
        }
        if ($noAllow != 0) {
            $more_json = json_decode($this->more_json, true);
            if ($more_json) {
                $files = ApiFileHelper::getMoreJsonSignUrl($more_json['files']);
                if ($files) {
                    $files = ArrayHelper::arraySort($files, 'rank_num', SORT_ASC);
                }
            }
            if (! empty($extendsInfo)) {
                $extends = [];
                foreach ($extendsInfo as $e) {
                    $arr = [];
                    $arr['eid'] = $e['eid'] ?? null;
                    $arr['plugin'] = $e['plugin_unikey'] ?? null;
                    $arr['frame'] = $e['frame'] ?? null;
                    $arr['position'] = $e['position'] ?? null;
                    $arr['content'] = $e['text_content'] ?? null;
                    if ($arr['frame'] == 1) {
                        $arr['files'] = $e['text_files'];
                    }
                    $arr['cover'] = $e['cover_file_url'] ?? null;
                    if ($arr['cover']) {
                        $arr['cover'] = ApiFileHelper::getImageSignUrlByFileIdUrl($e['cover_file_id'], $e['cover_file_url']);
                    }
                    $arr['title'] = null;
                    if (! empty($e)) {
                        $arr['title'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'title', $e['id']);
                    }
                    $arr['titleColor'] = $e['title_color'] ?? null;
                    $arr['descPrimary'] = null;
                    if (! empty($e)) {
                        $arr['descPrimary'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'desc_primary', $e['id']);
                    }
                    $arr['descPrimaryColor'] = $e['desc_primary_color'] ?? null;
                    $arr['descSecondary'] = null;
                    if (! empty($e)) {
                        $arr['descSecondary'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'desc_secondary', $e['id']);
                    }
                    $arr['descSecondaryColor'] = $e['desc_secondary_color'] ?? null;
                    $arr['descPrimaryColor'] = $e['desc_primary_color'] ?? null;
                    $arr['btnName'] = null;
                    if (! empty($e)) {
                        $arr['btnName'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'btn_name', $e['id']);
                    }
                    $arr['btnColor'] = $e['btn_color'] ?? null;
                    $arr['type'] = $e['extend_type'] ?? null;
                    $arr['target'] = $e['extend_target'] ?? null;
                    $arr['value'] = $e['extend_value'] ?? null;
                    $arr['support'] = $e['extend_support'] ?? null;
                    $arr['moreJson'] = ApiFileHelper::getMoreJsonSignUrl($e['moreJson']) ?? null;
                    $extends[] = $arr;
                }
            }
        }

        // Group
        $group = [];
        if ($groupInfo) {
            $group['gid'] = $groupInfo['gid'] ?? null;
            $group['gname'] = ApiLanguageHelper::getLanguagesByTableId(FresnsGroupsConfig::CFG_TABLE, 'name', $this->group_id);
            $group['description'] = ApiLanguageHelper::getLanguagesByTableId(FresnsGroupsConfig::CFG_TABLE, 'description', $this->group_id);
            $group['cover'] = $groupInfo['cover_file_url'] ?? null;
            // Whether the current user has the right to comment in the group
            $permission = $groupInfo['permission'] ?? null;
            $permissionArr = json_decode($permission, true);
            $group['allow'] = true;
            if ($permissionArr) {
                $publish_comment = $permissionArr['publish_comment'];
                $publish_post = $permissionArr['publish_post'];
                $publish_comment_roles = $permissionArr['publish_comment_roles'];
                $group['allow'] = false;
                // 1.All Users
                if ($publish_comment == 1) {
                    $group['allow'] = true;
                }
                // 2.Anyone in the group
                if ($publish_comment == 2) {
                    $followCount = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id',
                        $uid)->where('follow_type', 2)->where('follow_id', $this->group_id)->count();
                    if ($followCount > 0) {
                        $group['allow'] = true;
                    }
                }
                // 3.Specified role users only
                if ($publish_post == 3) {
                    $userRoleArr = FresnsUserRoles::where('user_id', $uid)->pluck('role_id')->toArray();
                    $arrIntersect = array_intersect($userRoleArr, $publish_comment_roles);
                    if ($arrIntersect) {
                        $group['allow'] = true;
                    }
                }
            }
            $group['viewCount'] = $groupInfo['view_count'] ?? null;
            $group['likeCount'] = $groupInfo['like_count'] ?? null;
            $group['followCount'] = $groupInfo['follow_count'] ?? null;
            $group['blockCount'] = $groupInfo['block_count'] ?? null;
            $group['postCount'] = $groupInfo['post_count'] ?? null;
            $group['digestCount'] = $groupInfo['digest_count'] ?? null;
        }

        // Post Plugin Extensions
        $managesArr = [];
        $FsPluginUsagesArr = FresnsPluginUsages::where('type', 5)->where('scene', 'like', '%1%')->get();
        if ($FsPluginUsagesArr) {
            foreach ($FsPluginUsagesArr as $FsPluginUsages) {
                $manages = [];
                $manages['plugin'] = $FsPluginUsages['plugin_unikey'];
                $manages['name'] = ApiLanguageHelper::getLanguagesByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $FsPluginUsages['id']);
                $manages['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($FsPluginUsages['icon_file_id'], $FsPluginUsages['icon_file_url']);
                $manages['url'] = FresnsPluginsService::getPluginUsagesUrl($FsPluginUsages['plugin_unikey'], $FsPluginUsages['id']);
                // Is the group administrator dedicated
                if ($FsPluginUsages['is_group_admin'] != 0) {
                    // Query whether the current user is a group administrator
                    if (! $this->group_id) {
                        $manages = [];
                    } else {
                        $groupInfo = FresnsGroups::find($this->group_id);
                        if (! $groupInfo) {
                            $manages = [];
                        } else {
                            $permission = json_decode($groupInfo['permission'], true);
                            if (isset($permission['admin_users'])) {
                                if (! is_array($permission['admin_users'])) {
                                    $manages = [];
                                } else {
                                    if (! in_array($uid, $permission['admin_users'])) {
                                        $manages = [];
                                    }
                                }
                            } else {
                                $manages = [];
                            }
                        }
                    }
                }
                // Determine if the primary role of the current user is an administrator
                if ($FsPluginUsages['roles']) {
                    $userRole = FresnsUserRoles::where('user_id', $uid)->first();
                    if ($userRole) {
                        $pluUserRoleArr = explode(',', $FsPluginUsages['roles']);
                        if (! in_array($userRole['role_id'], $pluUserRoleArr)) {
                            $manages = [];
                        }
                    }
                }
                $managesArr[] = $manages;
            }
        }

        // Edit Status
        $editStatus = [];
        // Is the current user an author
        $editStatus['isMe'] = $this->user_id == $uid ? true : false;
        // Edit Status
        $postEdit = ApiConfigHelper::getConfigByItemKey(FsConfig::POST_EDIT) ?? false;
        $editTimes = ApiConfigHelper::getConfigByItemKey(FsConfig::POST_EDIT_TIMELIMIT) ?? 5;
        $editSticky = ApiConfigHelper::getConfigByItemKey(FsConfig::POST_EDIT_STICKY) ?? false;
        $editDigest = ApiConfigHelper::getConfigByItemKey(FsConfig::POST_EDIT_ESSENCE) ?? false;
        if ($postEdit) {
            // How long you can edit
            if (strtotime($this->created_at) + ($editTimes * 60) < time()) {
                $postEdit = false;
            }
            // Post top edit permission
            if ($this->sticky_state != 1) {
                if (! $editSticky) {
                    $postEdit = false;
                }
            }
            // Post editing privileges after adding digest
            if ($this->digest_state != 1) {
                if (! $editDigest) {
                    $postEdit = false;
                }
            }
        }
        $editStatus['canEdit'] = $postEdit;
        // Delete Status
        $editStatus['canDelete'] = $append['can_delete'] == 1 ? true : false;

        if (! $langTag) {
            $langTag = FresnsPluginUsagesService::getDefaultLanguage();
        }

        // SEO Info
        $seo = DB::table('seo')->where('linked_type', 4)->where('linked_id', $this->id)->where('lang_tag', $langTag)->where('deleted_at', null)->first();
        $seoInfo = [];
        if ($seo) {
            $seoInfo['title'] = $seo->title;
            $seoInfo['keywords'] = $seo->keywords;
            $seoInfo['description'] = $seo->description;
        }

        // more_json
        $more_json = json_decode($this->more_json, true);
        $icons = $more_json['icons'] ?? [];
        if ($more_json) {
            $icons = ApiFileHelper::getIconsSignUrl($icons);
        }

        // Default Field
        $default = [
            'pid' => $pid,
            'title' => $title,
            'content' => $content,
            'isMarkdown' => $append['is_markdown'] ?? 0,
            'sticky' => $sticky,
            'digest' => $digest,
            'postName' => $PostName,
            'likeSetting' => $likeSetting,
            'likeName' => $likeName,
            'likeStatus' => $likeStatus,
            'followSetting' => $followSetting,
            'followName' => $followName,
            'followStatus' => $followStatus,
            'blockSetting' => $blockSetting,
            'blockName' => $blockName,
            'blockStatus' => $blockStatus,
            'userListStatus' => $append['user_list_status'],
            'userListName' => $userListName,
            'userListCount' => $userListCount,
            'userListUrl' => $userListUrl,
            'viewCount' => $viewCount,
            'likeCount' => $likeCount,
            'followCount' => $followCount,
            'blockCount' => $blockCount,
            'commentCount' => $commentCount,
            'commentLikeCount' => $commentLikeCount,
            'time' => $time,
            'timeFormat' => $timeFormat,
            'editTime' => $editTime,
            'editTimeFormat' => $editTimeFormat,
            'editCount' => $append['edit_count'],
            'allowStatus' => $allowStatus,
            'allowProportion' => $allowProportion,
            'allowBtnName' => $allowBtnName,
            'allowBtnUrl' => $allowBtnUrl,
            'user' => $user,
            'icons' => $icons,
            'location' => $location,
            'attachCount' => $attachCount,
            'files' => $files,
            'extends' => $extends,
            'group' => $group,
            'manages' => $managesArr,
            'editStatus' => $editStatus,
        ];

        // Merger
        $arr = $default;

        return $arr;
    }

    // Distance Conversion
    public function GetDistance($lat1, $lng1, $lat2, $lng2, $distanceUnits)
    {
        $EARTH_RADIUS = 6378.137;

        $radLat1 = $this->rad($lat1);
        $radLat2 = $this->rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = $this->rad($lng1) - $this->rad($lng2);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * $EARTH_RADIUS;
        // 1 km=0.621371192237 mi
        if ($distanceUnits == 'mi') {
            $s = round($s * 10000 * 0.62);
        } else {
            $s = round($s * 10000);
        }
        $s = round($s / 10000) == 0 ? 1 : round($s / 10000);

        return $s.$distanceUnits;
    }

    private function rad($d)
    {
        return $d * M_PI / 180.0;
    }
}
