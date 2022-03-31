<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Content;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\FsDb\FresnsCommentAppends\FresnsCommentAppendsConfig;
use App\Fresns\Api\FsDb\FresnsComments\FresnsComments;
use App\Fresns\Api\FsDb\FresnsComments\FresnsCommentsConfig;
use App\Fresns\Api\FsDb\FresnsComments\FresnsCommentsService;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Fresns\Api\FsDb\FresnsExtendLinkeds\FresnsExtendLinkedsConfig;
use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtends;
use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtendsConfig;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroups;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroupsConfig;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPluginsService;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsagesConfig;
use App\Fresns\Api\FsDb\FresnsPostAppends\FresnsPostAppendsConfig;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPosts;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPostsConfig;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRoles;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRolesConfig;
use App\Fresns\Api\FsDb\FresnsUserBlocks\FresnsUserBlocksConfig;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollowsConfig;
use App\Fresns\Api\FsDb\FresnsUserIcons\FresnsUserIcons;
use App\Fresns\Api\FsDb\FresnsUserIcons\FresnsUserIconsConfig;
use App\Fresns\Api\FsDb\FresnsUserLikes\FresnsUserLikes;
use App\Fresns\Api\FsDb\FresnsUserLikes\FresnsUserLikesConfig;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRoles;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Fresns\Api\Helpers\ApiLanguageHelper;
use App\Fresns\Api\Helpers\ArrayHelper;
use App\Fresns\Api\Helpers\DateHelper;
use Illuminate\Support\Facades\DB;

/**
 * List resource config handle.
 */
class FresnsCommentsResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Field
        $formMap = FresnsCommentsConfig::FORM_FIELDS_MAP;
        $formMapFieldsArr = [];
        foreach ($formMap as $k => $dbField) {
            $formMapFieldsArr[$dbField] = $this->$dbField;
        }

        // Data Table: comment_appends
        $append = DB::table(FresnsCommentAppendsConfig::CFG_TABLE)->where('comment_id', $this->id)->first();
        // Data Table: users
        $userInfo = DB::table(FresnsUsersConfig::CFG_TABLE)->where('id', $this->user_id)->first();
        // Data Table: user_roles
        $roleRels = FresnsUserRoles::where('user_id', $this->user_id)->where('is_main', 1)->first();
        // Data Table: roles
        $userRole = [];
        if (! empty($roleRels)) {
            $userRole = FresnsRoles::find($roleRels['role_id']);
        }
        // Data Table: posts
        $posts = FresnsPosts::find($this->post_id);
        // Data Table: post_appends
        $postAppends = DB::table(FresnsPostAppendsConfig::CFG_TABLE)->where('post_id', $this->post_id)->first();
        if ($postAppends) {
            $postAppends = get_object_vars($postAppends);
        }
        // Data Table: groups
        $groupInfo = FresnsGroups::find($posts['group_id']);

        // Comment Info
        $cid = $this->cid;
        $uid = GlobalService::getGlobalKey('user_id');

        $input = [
            'user_id' => $uid,
            'like_type' => 5,
            'like_id' => $this->id,
        ];

        $count = DB::table(FresnsUserLikesConfig::CFG_TABLE)->where($input)->count();
        $isLike = $count == 0 ? false : true;

        // Whether to block comments
        $blocksCount = DB::table(FresnsUserBlocksConfig::CFG_TABLE)->where('user_id', $uid)->where('block_type', 5)->where('block_id', $this->id)->count();
        $isBlock = $blocksCount == 0 ? false : true;

        $content = FresnsPostsResource::getContentView($this->content, $this->id, 2);
        $brief = $this->is_brief;
        $sticky = $this->is_sticky;
        $likeCount = $this->like_count;
        $commentCount = $this->comment_count;
        $commentLikeCount = $this->comment_like_count;

        // Operation behavior status
        $likeStatus = DB::table(FresnsUserLikesConfig::CFG_TABLE)->where('user_id', $uid)->where('like_type', 5)->where('like_id', $this->id)->where('deleted_at', null)->count();
        $followStatus = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 5)->where('follow_id', $this->id)->where('deleted_at', null)->count();
        $blockStatus = DB::table(FresnsUserBlocksConfig::CFG_TABLE)->where('user_id', $uid)->where('block_type', 5)->where('block_id', $this->id)->where('deleted_at', null)->count();
        // Operation behavior settings
        $likeSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::LIKE_COMMENT_SETTING);
        $followSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::FOLLOW_COMMENT_SETTING);
        $blockSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::SHIELD_COMMENT_SETTING);
        // Operation behavior naming
        $likeName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::LIKE_COMMENT_NAME) ?? 'Like';
        $followName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::FOLLOW_COMMENT_NAME) ?? 'Save comment';
        $blockName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::SHIELD_COMMENT_NAME) ?? 'Hide comment';
        // Content Naming
        $commentName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::COMMENT_NAME) ?? 'Comment';

        // user_blocks: query the table to confirm if the object is blocked
        $blockUserStatus = DB::table(FresnsUserBlocksConfig::CFG_TABLE)->where('user_id', $uid)->where('block_type', 1)->where('block_id', $this->user_id)->count();

        $postAuthorLikeStatus = FresnsUserLikes::where('user_id', $posts['user_id'])->where('like_type', 5)->where('like_id', $this->id)->count();
        $likeCount = $this->like_count;
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
        $user = [];
        $user['anonymous'] = $this->is_anonymous;
        $user['deactivate'] = false; //Not deactivated = false, Deactivated = true
        $user['isAuthor'] = false;
        $user['uid'] = null;
        $user['username'] = null;
        $user['nickname'] = null;
        $user['rid'] = null;
        $user['nicknameColor'] = null;
        $user['roleName'] = null;
        $user['roleNameDisplay'] = null;
        $user['roleIcon'] = null;
        $user['roleIconDisplay'] = null;
        $user['avatar'] = $userInfo->avatar_file_url ?? null;
        // Default Avatar
        if (empty($user['avatar'])) {
            $defaultIcon = ApiConfigHelper::getConfigByItemKey(FsConfig::DEFAULT_AVATAR);
            $user['avatar'] = $defaultIcon;
        }
        // Anonymous Avatar
        if ($this->is_anonymous == 1) {
            $anonymousAvatar = ApiConfigHelper::getConfigByItemKey(FsConfig::ANONYMOUS_AVATAR);
            $user['avatar'] = $anonymousAvatar;
        }
        // Deactivate Avatar
        if ($userInfo) {
            if ($userInfo->deleted_at != null) {
                $deactivateAvatar = ApiConfigHelper::getConfigByItemKey(FsConfig::DEACTIVATE_AVATAR);
                $user['avatar'] = $deactivateAvatar;
                $user['deactivate'] = true;
            }
        } else {
            $deactivateAvatar = ApiConfigHelper::getConfigByItemKey(FsConfig::DEACTIVATE_AVATAR);
            $user['avatar'] = $deactivateAvatar;
        }
        $user['avatar'] = ApiFileHelper::getImageAvatarUrl($user['avatar']);

        $user['gender'] = 0;
        $user['bio'] = null;
        $user['location'] = null;
        $user['verifiedStatus'] = 1;
        $user['verifiedIcon'] = null;
        $user['verifiedDesc'] = null;
        $user['icons'] = [];
        if ($this->is_anonymous == 0) {
            if ($userInfo) {
                if ($userInfo->deleted_at == null && $userInfo) {
                    $user['anonymous'] = $this->is_anonymous;
                    $user['deactivate'] = false;
                    $user['isAuthor'] = ($this->user_id == $posts['user_id']) ? true : false;
                    $user['uid'] = $userInfo->uid ?? null;
                    $user['username'] = $userInfo->username ?? null;
                    $user['nickname'] = $userInfo->nickname ?? null;
                    $user['rid'] = $userRole['id'] ?? null;
                    $user['nicknameColor'] = $userRole['nickname_color'] ?? null;
                    $user['roleName'] = ApiLanguageHelper::getLanguagesByTableId(FresnsRolesConfig::CFG_TABLE, 'name', $userRole['id']);
                    $user['roleNameDisplay'] = $userRole['is_display_name'] ?? 0;
                    $user['roleIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($userRole['icon_file_id'], $userRole['icon_file_url']);
                    $user['roleIconDisplay'] = $userRole['is_display_icon'] ?? 0;
                    $user['decorate'] = ApiFileHelper::getImageSignUrlByFileIdUrl($userInfo->decorate_file_id, $userInfo->decorate_file_url);
                    $user['gender'] = $userInfo->gender ?? 0;
                    $user['bio'] = $userInfo->bio ?? null;
                    $user['location'] = $userInfo->location ?? null;
                    $user['verifiedStatus'] = $userInfo->verified_status ?? 1;
                    $user['verifiedIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($userInfo->verified_file_id, $userInfo->verified_file_url);
                    $user['verifiedDesc'] = $userInfo->verified_desc ?? null;

                    $userIconsArr = FresnsUserIcons::where('user_id', $userInfo->id)->get()->toArray();
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
        }

        // The commentPreviews is output when the searchCid is empty.
        $commentPreviews = [];
        $searchCid = request()->input('searchCid');
        $previewStatus = ApiConfigHelper::getConfigByItemKey(FsConfig::COMMENT_PREVIEW);
        if (! $searchCid) {
            if ($previewStatus && $previewStatus != 0) {
                $fresnsCommentsService = new FresnsCommentsService();
                $commentList = $fresnsCommentsService->getCommentPreviewList($this->id, $previewStatus, $uid);
                $commentPreviews = $commentList;
            }
        }

        /*
         * replyTo is output when searchCid has a value.
         * Represents the output of sub-level comments, and only sub-level comments have replyTo information, representing that A replied to B.
         * https://fresns.org/api/content/comment-lists.html
         * If the parent_id of the comment is the current comment (parameter searchCid) and represents a secondary comment, the following information is not output.
         * The parent_id of the comment is not the current comment (parameter searchCid), representing three or more levels, showing the interaction under the comment and outputting the following information about his parent's comment.
         */
        $replyTo = [];
        if ($searchCid) {
            // Get the comment id corresponding to searchCid
            $commentCid = FresnsComments::where('cid', $searchCid)->first();
            $parentComment = FresnsComments::where('parent_id', $this->id)->first();
            $fresnsCommentsService = new FresnsCommentsService();
            $replyTo = $fresnsCommentsService->getReplyToPreviewList($this->id, $uid);
        }

        // Location
        $location = [];
        $location['isLbs'] = $this->is_lbs;
        $location['mapId'] = $append->map_id ?? null;
        $location['latitude'] = $append->map_latitude ?? null;
        $location['longitude'] = $append->map_longitude ?? null;
        $location['scale'] = $append->map_scale ?? null;
        $location['poi'] = $append->map_poi ?? null;
        $location['poiId'] = $append->map_poi_id ?? null;
        $location['distance'] = null;
        $longitude = request()->input('longitude', null);
        $latitude = request()->input('latitude', null);
        $map_latitude = $location['latitude'] ?? null;
        $map_longitude = $location['longitude'] ?? null;
        if ($longitude && $latitude && $map_latitude && $map_longitude) {
            // Get location units
            $langTag = $request->header('langTag');
            $distanceUnits = $request->input('lengthUnits');
            if (! $distanceUnits) {
                // Distance
                $languages = ApiConfigHelper::distanceUnits($langTag);
                $distanceUnits = empty($languages) ? 'km' : $languages;
            }
            $location['distance'] = $this->GetDistance($latitude, $longitude, $map_latitude, $map_longitude, $distanceUnits);
        }
        $more_json_decode = json_decode($posts['more_json'], true);

        // Files
        $files = [];
        $more_json = json_decode($this->more_json, true);
        if ($more_json) {
            $files = ApiFileHelper::getMoreJsonSignUrl($more_json['files']);
            if ($files) {
                $files = ArrayHelper::arraySort($files, 'rank_num', SORT_ASC);
            }
        }

        // Extends
        $extends = [];
        $extendsLinks = Db::table('extend_linkeds')->where('linked_type', 2)->where('linked_id', $this->id)->pluck('extend_id')->toArray();
        $extendsInfo = [];
        if ($extendsLinks) {
            $extendsLinks = array_unique($extendsLinks);
            $extendsInfo = FresnsExtends::whereIn('id', $extendsLinks)->get();
        }
        if (! empty($extendsInfo)) {
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

        // Attached Quantity
        $attachCount = [];
        // comments > more_json > files
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
        // commentBtn
        $commentBtn = [];
        $commentBtn['status'] = $postAppends['comment_btn_status'];
        if ($uid == $this->user_id) {
            $commentBtn['name'] = ApiLanguageHelper::getLanguagesByTableId(FresnsPostsConfig::CFG_TABLE, 'comment_btn_name', $posts['id']);
            $commentBtn['url'] = FresnsPluginsService::getPluginUrlByUnikey($postAppends['comment_btn_plugin_unikey']);
        }

        // If searchPid is empty, output
        // means that the comment is output independently from the post, so it needs to be accompanied by the post parameter and the information of the post to which the comment belongs
        $searchPid = request()->input('searchPid');
        $post = [];
        if (! $searchPid) {
            $post['pid'] = $posts['pid'] ?? null;
            $post['title'] = $posts['title'] ?? null;
            $post['content'] = $posts['content'] ?? null;
            $post['status'] = $posts['is_enable'] ?? null;
            $post['gname'] = null;
            $post['gid'] = null;
            $post['cover'] = null;
            if ($groupInfo) {
                $post['gname'] = ApiLanguageHelper::getLanguagesByTableId(FresnsGroupsConfig::CFG_TABLE, 'name', $groupInfo['id']);
                $post['gid'] = $groupInfo['gid'];
                $post['cover'] = ApiFileHelper::getImageSignUrlByFileIdUrl($groupInfo['cover_file_id'], $groupInfo['cover_file_url']);
            }
            $post['anonymous'] = $posts['is_anonymous'] ?? 0;
            $post['deactivate'] = false; //Not deactivated = false, Deactivated = true
            $postUserInfo = DB::table(FresnsUsersConfig::CFG_TABLE)->where('id', $posts['user_id'])->first();
            $post['uid'] = $postUserInfo->uid ?? null;
            $post['username'] = $postUserInfo->username ?? null;
            $post['nickname'] = $postUserInfo->nickname ?? null;
            $post['avatar'] = $postUserInfo->avatar_file_url ?? null;
            // Default Avatar
            if (empty($post['avatar'])) {
                $defaultIcon = ApiConfigHelper::getConfigByItemKey(FsConfig::DEFAULT_AVATAR);
                $post['avatar'] = $defaultIcon;
            }
            // Anonymous Avatar
            if ($posts['is_anonymous'] == 1) {
                $post['anonymous'] = 1;
                $post['uid'] = null;
                $post['username'] = null;
                $post['nickname'] = null;
                $anonymousAvatar = ApiConfigHelper::getConfigByItemKey(FsConfig::ANONYMOUS_AVATAR);
                $post['avatar'] = $anonymousAvatar;
            }
            // Deactivate Avatar
            if ($userInfo->deleted_at != null) {
                $deactivateAvatar = ApiConfigHelper::getConfigByItemKey(FsConfig::DEACTIVATE_AVATAR);
                $post['avatar'] = $deactivateAvatar;
                $post['deactivate'] = true;
            }
            $post['avatar'] = ApiFileHelper::getImageAvatarUrl($post['avatar']);
        }

        // Comment Plugin Extensions
        $managesArr = [];
        $FsPluginUsagesArr = FresnsPluginUsages::where('type', 5)->where('scene', 'like', '%2%')->get();
        if ($FsPluginUsagesArr) {
            foreach ($FsPluginUsagesArr as $FsPluginUsages) {
                $manages['plugin'] = $FsPluginUsages['plugin_unikey'];
                $manages['name'] = ApiLanguageHelper::getLanguagesByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $FsPluginUsages['id']);
                $manages['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($FsPluginUsages['icon_file_id'], $FsPluginUsages['icon_file_url']);
                $manages['url'] = FresnsPluginsService::getPluginUsagesUrl($FsPluginUsages['plugin_unikey'], $FsPluginUsages['id']);
                // Is the group administrator dedicated
                if ($FsPluginUsages['is_group_admin'] != 0) {
                    // Query whether the current user is a group administrator
                    if (! $posts['group_id']) {
                        $manages = [];
                    } else {
                        $groupInfo = FresnsGroups::find($posts['group_id']);
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
        $commentEdit = ApiConfigHelper::getConfigByItemKey(FsConfig::COMMENT_EDIT) ?? false;
        $editTimeRole = ApiConfigHelper::getConfigByItemKey(FsConfig::COMMENT_EDIT_TIMELIMIT) ?? 5;
        $editSticky = ApiConfigHelper::getConfigByItemKey(FsConfig::COMMENT_EDIT_STICKY) ?? false;
        if ($commentEdit) {
            // How long you can edit
            if (strtotime($this->created_at) + ($editTimeRole * 60) < time()) {
                $commentEdit = false;
            }
            // Comment top edit permission
            if ($this->is_sticky != 0) {
                if (! $editSticky) {
                    $commentEdit = false;
                }
            }
        }
        $editStatus['canEdit'] = $commentEdit;
        // Delete Status
        if ($append) {
            $editStatus['canDelete'] = $append->can_delete == 1 ? true : false;
        } else {
            $editStatus['canDelete'] = false;
        }

        $more_json = json_decode($this->more_json, true);
        $icons = $more_json['icons'] ?? [];
        if ($more_json) {
            $icons = ApiFileHelper::getIconsSignUrl($icons);
        }

        // Default Field
        $default = [
            'pid' => $posts['pid'],
            'cid' => $cid,
            'content' => $content,
            'brief' => $brief,
            'sticky' => $sticky,
            'commentName' => $commentName,
            'likeSetting' => $likeSetting,
            'likeName' => $likeName,
            'likeStatus' => $likeStatus,
            'postAuthorLikeStatus' => $postAuthorLikeStatus,
            'followSetting' => $followSetting,
            'followName' => $followName,
            'followStatus' => $followStatus,
            'blockSetting' => $blockSetting,
            'blockName' => $blockName,
            'blockStatus' => $blockStatus,
            'blockUserStatus' => $blockUserStatus,
            'likeCount' => $likeCount,
            'followCount' => $this->follow_count,
            'blockCount' => $this->block_count,
            'commentCount' => $commentCount,
            'commentLikeCount' => $commentLikeCount,
            'time' => $time,
            'timeFormat' => $timeFormat,
            'editTime' => $editTime,
            'editTimeFormat' => $editTimeFormat,
            'user' => $user,
            'icons' => $icons,
            'commentPreviews' => $commentPreviews,
            'replyTo' => $replyTo,
            'location' => $location,
            'attachCount' => $attachCount,
            'files' => $files,
            'extends' => $extends,
            'commentBtn' => $commentBtn,
            'post' => $post,
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
