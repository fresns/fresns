<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Content;

use App\Base\Resources\BaseAdminResource;
use App\Helpers\ArrayHelper;
use App\Helpers\DateHelper;
use App\Http\Center\Common\GlobalService;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsDb\FresnsCommentAppends\FresnsCommentAppends;
use App\Http\FresnsDb\FresnsCommentAppends\FresnsCommentAppendsConfig;
use App\Http\FresnsDb\FresnsComments\FresnsComments;
use App\Http\FresnsDb\FresnsComments\FresnsCommentsConfig;
use App\Http\FresnsDb\FresnsComments\FresnsCommentsService;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Http\FresnsDb\FresnsExtendLinkeds\FresnsExtendLinkeds;
use App\Http\FresnsDb\FresnsExtendLinkeds\FresnsExtendLinkedsConfig;
use App\Http\FresnsDb\FresnsExtends\FresnsExtends;
use App\Http\FresnsDb\FresnsExtends\FresnsExtendsConfig;
use App\Http\FresnsDb\FresnsFiles\FresnsFiles;
use App\Http\FresnsDb\FresnsGroups\FresnsGroups;
use App\Http\FresnsDb\FresnsGroups\FresnsGroupsConfig;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollows;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollowsConfig;
use App\Http\FresnsDb\FresnsMemberIcons\FresnsMemberIcons;
use App\Http\FresnsDb\FresnsMemberIcons\FresnsMemberIconsConfig;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikes;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikesConfig;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRolesConfig;
use App\Http\FresnsDb\FresnsMembers\FresnsMembersConfig;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShields;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShieldsConfig;
use App\Http\FresnsDb\FresnsPlugins\FresnsPluginsService;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsagesConfig;
use App\Http\FresnsDb\FresnsPostAppends\FresnsPostAppends;
use App\Http\FresnsDb\FresnsPostAppends\FresnsPostAppendsConfig;
use App\Http\FresnsDb\FresnsPosts\FresnsPosts;
use App\Http\FresnsDb\FresnsPosts\FresnsPostsConfig;
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
        // Data Table: members
        $memberInfo = DB::table(FresnsMembersConfig::CFG_TABLE)->where('id', $this->member_id)->first();
        // Data Table: member_role_rels
        $roleRels = FresnsMemberRoleRels::where('member_id', $this->member_id)->where('type', 2)->first();
        // Data Table: member_roles
        $memberRole = [];
        if (! empty($roleRels)) {
            $memberRole = FresnsMemberRoles::find($roleRels['role_id']);
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
        $cid = $this->uuid;
        $mid = GlobalService::getGlobalKey('member_id');

        $input = [
            'member_id' => $mid,
            'like_type' => 5,
            'like_id' => $this->id,
        ];

        $count = DB::table(FresnsMemberLikesConfig::CFG_TABLE)->where($input)->count();
        $isLike = $count == 0 ? false : true;

        // Whether to block comments
        $shieldsCount = DB::table(FresnsMemberShieldsConfig::CFG_TABLE)->where('member_id', $mid)->where('shield_type', 5)->where('shield_id', $this->id)->count();
        $isShield = $shieldsCount == 0 ? false : true;

        $content = FresnsPostsResource::getContentView($this->content, $this->id, 2);
        $brief = $this->is_brief;
        $sticky = $this->is_sticky;
        $likeCount = $this->like_count;
        $commentCount = $this->comment_count;
        $commentLikeCount = $this->comment_like_count;

        // Operation behavior status
        $likeStatus = DB::table(FresnsMemberLikesConfig::CFG_TABLE)->where('member_id', $mid)->where('like_type', 5)->where('like_id', $this->id)->where('deleted_at', null)->count();
        $followStatus = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 5)->where('follow_id', $this->id)->where('deleted_at', null)->count();
        $shieldStatus = DB::table(FresnsMemberShieldsConfig::CFG_TABLE)->where('member_id', $mid)->where('shield_type', 5)->where('shield_id', $this->id)->where('deleted_at', null)->count();
        // Operation behavior settings
        $likeSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::LIKE_COMMENT_SETTING);
        $followSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::FOLLOW_COMMENT_SETTING);
        $shieldSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::SHIELD_COMMENT_SETTING);
        // Operation behavior naming
        $likeName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::LIKE_COMMENT_NAME) ?? 'Like';
        $followName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::FOLLOW_COMMENT_NAME) ?? 'Save comment';
        $shieldName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::SHIELD_COMMENT_NAME) ?? 'Hide comment';
        // Content Naming
        $commentName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::COMMENT_NAME) ?? 'Comment';

        // member_shields: query the table to confirm if the object is blocked
        $shieldMemberStatus = DB::table(FresnsMemberShieldsConfig::CFG_TABLE)->where('member_id', $mid)->where('shield_type', 1)->where('shield_id', $this->member_id)->count();

        $postAuthorLikeStatus = FresnsMemberLikes::where('member_id', $posts['member_id'])->where('like_type', 5)->where('like_id', $this->id)->count();
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
        $member = [];
        $member['anonymous'] = $this->is_anonymous;
        $member['deactivate'] = false; //Not deactivated = false, Deactivated = true
        $member['isAuthor'] = false;
        $member['mid'] = '';
        $member['mname'] = '';
        $member['nickname'] = '';
        $member['rid'] = '';
        $member['nicknameColor'] = '';
        $member['roleName'] = '';
        $member['roleNameDisplay'] = '';
        $member['roleIcon'] = '';
        $member['roleIconDisplay'] = '';
        $member['avatar'] = $memberInfo->avatar_file_url ?? '';
        // Default Avatar
        if (empty($member['avatar'])) {
            $defaultIcon = ApiConfigHelper::getConfigByItemKey(FsConfig::DEFAULT_AVATAR);
            $member['avatar'] = $defaultIcon;
        }
        // Anonymous Avatar
        if ($this->is_anonymous == 1) {
            $anonymousAvatar = ApiConfigHelper::getConfigByItemKey(FsConfig::ANONYMOUS_AVATAR);
            $member['avatar'] = $anonymousAvatar;
        }
        // Deactivate Avatar
        if ($memberInfo) {
            if ($memberInfo->deleted_at != null) {
                $deactivateAvatar = ApiConfigHelper::getConfigByItemKey(FsConfig::DEACTIVATE_AVATAR);
                $member['avatar'] = $deactivateAvatar;
                $member['deactivate'] = true;
            }
        } else {
            $deactivateAvatar = ApiConfigHelper::getConfigByItemKey(FsConfig::DEACTIVATE_AVATAR);
            $member['avatar'] = $deactivateAvatar;
        }
        $member['avatar'] = ApiFileHelper::getImageAvatarUrl($member['avatar']);

        $member['gender'] = 0;
        $member['bio'] = '';
        $member['verifiedStatus'] = 1;
        $member['verifiedIcon'] = '';
        $member['verifiedDesc'] = '';
        $member['icons'] = [];
        if ($this->is_anonymous == 0) {
            if ($memberInfo) {
                if ($memberInfo->deleted_at == null && $memberInfo) {
                    $member['anonymous'] = $this->is_anonymous;
                    $member['deactivate'] = false;
                    $member['isAuthor'] = ($this->member_id == $posts['member_id']) ? true : false;
                    $member['mid'] = $memberInfo->uuid ?? '';
                    $member['mname'] = $memberInfo->name ?? '';
                    $member['nickname'] = $memberInfo->nickname ?? '';
                    $member['rid'] = $memberRole['id'] ?? '';
                    $member['nicknameColor'] = $memberRole['nickname_color'] ?? '';
                    $member['roleName'] = ApiLanguageHelper::getLanguagesByTableId(FresnsMemberRolesConfig::CFG_TABLE, 'name', $memberRole['id']);
                    $member['roleNameDisplay'] = $memberRole['is_display_name'] ?? 0;
                    $member['roleIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($memberRole['icon_file_id'], $memberRole['icon_file_url']);
                    $member['roleIconDisplay'] = $memberRole['is_display_icon'] ?? 0;
                    $member['decorate'] = ApiFileHelper::getImageSignUrlByFileIdUrl($memberInfo->decorate_file_id, $memberInfo->decorate_file_url);
                    $member['gender'] = $memberInfo->gender ?? 0;
                    $member['bio'] = $memberInfo->bio ?? '';
                    $member['verifiedStatus'] = $memberInfo->verified_status ?? 1;
                    $member['verifiedIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($memberInfo->verified_file_id, $memberInfo->verified_file_url);
                    $member['verifiedDesc'] = $memberInfo->verified_desc ?? '';

                    $memberIconsArr = FresnsMemberIcons::where('member_id', $memberInfo->id)->get()->toArray();
                    $iconsArr = [];
                    foreach ($memberIconsArr as $v) {
                        $item = [];
                        $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['icon_file_id'], $v['icon_file_url']);
                        $item['name'] = ApiLanguageHelper::getLanguagesByTableId(FresnsMemberIconsConfig::CFG_TABLE, 'name', $v['id']);
                        $item['type'] = $v['type'];
                        $item['url'] = FresnsPluginsService::getPluginUrlByUnikey($v['plugin_unikey']);
                        $iconsArr[] = $item;
                    }
                    $member['icons'] = $iconsArr;
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
                $commentList = $fresnsCommentsService->getCommentPreviewList($this->id, $previewStatus, $mid);
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
            $commentCid = FresnsComments::where('uuid', $searchCid)->first();
            $parentComment = FresnsComments::where('parent_id', $this->id)->first();
            $fresnsCommentsService = new FresnsCommentsService();
            $replyTo = $fresnsCommentsService->getReplyToPreviewList($this->id, $mid);
        }

        // Location
        $location = [];
        $location['isLbs'] = $this->is_lbs;
        $location['mapId'] = $append->map_id ?? '';
        $location['latitude'] = $append->map_latitude ?? '';
        $location['longitude'] = $append->map_longitude ?? '';
        $location['scale'] = $append->map_scale ?? '';
        $location['poi'] = $append->map_poi ?? '';
        $location['poiId'] = $append->map_poi_id ?? '';
        $location['distance'] = '';
        $longitude = request()->input('longitude', '');
        $latitude = request()->input('latitude', '');
        $map_latitude = $location['latitude'] ?? '';
        $map_longitude = $location['longitude'] ?? '';
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
                $arr['eid'] = $e['uuid'] ?? '';
                $arr['plugin'] = $e['plugin_unikey'] ?? '';
                $arr['frame'] = $e['frame'] ?? '';
                $arr['position'] = $e['position'] ?? '';
                $arr['content'] = $e['text_content'] ?? '';
                if ($arr['frame'] == 1) {
                    $arr['files'] = $e['text_files'];
                }
                $arr['cover'] = $e['cover_file_url'] ?? '';
                if ($arr['cover']) {
                    $arr['cover'] = ApiFileHelper::getImageSignUrlByFileIdUrl($e['cover_file_id'], $e['cover_file_url']);
                }
                $arr['title'] = '';
                if (! empty($e)) {
                    $arr['title'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'title', $e['id']);
                }
                $arr['titleColor'] = $e['title_color'] ?? '';
                $arr['descPrimary'] = '';
                if (! empty($e)) {
                    $arr['descPrimary'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'desc_primary', $e['id']);
                }
                $arr['descPrimaryColor'] = $e['desc_primary_color'] ?? '';
                $arr['descSecondary'] = '';
                if (! empty($e)) {
                    $arr['descSecondary'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'desc_secondary', $e['id']);
                }
                $arr['descSecondaryColor'] = $e['desc_secondary_color'] ?? '';
                $arr['descPrimaryColor'] = $e['desc_primary_color'] ?? '';
                $arr['btnName'] = '';
                if (! empty($e)) {
                    $arr['btnName'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'btn_name', $e['id']);
                }
                $arr['btnColor'] = $e['btn_color'] ?? '';
                $arr['type'] = $e['extend_type'] ?? '';
                $arr['target'] = $e['extend_target'] ?? '';
                $arr['value'] = $e['extend_value'] ?? '';
                $arr['support'] = $e['extend_support'] ?? '';
                $arr['moreJson'] = ApiFileHelper::getMoreJsonSignUrl($e['moreJson']) ?? '';
                $extends[] = $arr;
            }
        }

        // Attached Quantity
        $attachCount = [];
        // comments > more_json > files
        $attachCount['images'] = 0;
        $attachCount['videos'] = 0;
        $attachCount['audios'] = 0;
        $attachCount['docs'] = 0;
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
                        $attachCount['docs']++;
                    }
                }
            }
        }
        // commentBtn
        $commentBtn = [];
        $commentBtn['status'] = $postAppends['comment_btn_status'];
        if ($mid == $this->member_id) {
            $commentBtn['name'] = ApiLanguageHelper::getLanguagesByTableId(FresnsPostsConfig::CFG_TABLE, 'comment_btn_name', $posts['id']);
            $commentBtn['url'] = FresnsPluginsService::getPluginUrlByUnikey($postAppends['comment_btn_plugin_unikey']);
        }

        // If searchPid is empty, output
        // means that the comment is output independently from the post, so it needs to be accompanied by the post parameter and the information of the post to which the comment belongs
        $searchPid = request()->input('searchPid');
        $post = [];
        if (! $searchPid) {
            $post['pid'] = $posts['uuid'] ?? '';
            $post['title'] = $posts['title'] ?? '';
            $post['content'] = $posts['content'] ?? '';
            $post['status'] = $posts['is_enable'] ?? '';
            $post['gname'] = '';
            $post['gid'] = '';
            $post['cover'] = '';
            if ($groupInfo) {
                $post['gname'] = ApiLanguageHelper::getLanguagesByTableId(FresnsGroupsConfig::CFG_TABLE, 'name', $groupInfo['id']);
                $post['gid'] = $groupInfo['uuid'];
                $post['cover'] = ApiFileHelper::getImageSignUrlByFileIdUrl($groupInfo['cover_file_id'], $groupInfo['cover_file_url']);
            }
            $post['anonymous'] = $posts['is_anonymous'] ?? 0;
            $post['deactivate'] = false; //Not deactivated = false, Deactivated = true
            $postMemberInfo = DB::table(FresnsMembersConfig::CFG_TABLE)->where('id', $posts['member_id'])->first();
            $post['mid'] = $postMemberInfo->uuid ?? '';
            $post['mname'] = $postMemberInfo->name ?? '';
            $post['nickname'] = $postMemberInfo->nickname ?? '';
            $post['avatar'] = $postMemberInfo->avatar_file_url ?? '';
            // Default Avatar
            if (empty($post['avatar'])) {
                $defaultIcon = ApiConfigHelper::getConfigByItemKey(FsConfig::DEFAULT_AVATAR);
                $post['avatar'] = $defaultIcon;
            }
            // Anonymous Avatar
            if ($posts['is_anonymous'] == 1) {
                $post['anonymous'] = 1;
                $post['mid'] = '';
                $post['mname'] = '';
                $post['nickname'] = '';
                $anonymousAvatar = ApiConfigHelper::getConfigByItemKey(FsConfig::ANONYMOUS_AVATAR);
                $post['avatar'] = $anonymousAvatar;
            }
            // Deactivate Avatar
            if ($memberInfo->deleted_at != null) {
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
                    // Query whether the current member is a group administrator
                    if (! $posts['group_id']) {
                        $manages = [];
                    } else {
                        $groupInfo = FresnsGroups::find($posts['group_id']);
                        if (! $groupInfo) {
                            $manages = [];
                        } else {
                            $permission = json_decode($groupInfo['permission'], true);
                            if (isset($permission['admin_members'])) {
                                if (! is_array($permission['admin_members'])) {
                                    $manages = [];
                                } else {
                                    if (! in_array($mid, $permission['admin_members'])) {
                                        $manages = [];
                                    }
                                }
                            } else {
                                $manages = [];
                            }
                        }
                    }
                }
                // Determine if the primary role of the current member is an administrator
                if ($FsPluginUsages['member_roles']) {
                    $mroleRels = FresnsMemberRoleRels::where('member_id', $mid)->first();
                    if ($mroleRels) {
                        $pluMemberRoleArr = explode(',', $FsPluginUsages['member_roles']);
                        if (! in_array($mroleRels['role_id'], $pluMemberRoleArr)) {
                            $manages = [];
                        }
                    }
                }
                $managesArr[] = $manages;
            }
        }

        // Edit Status
        $editStatus = [];
        // Is the current member an author
        $editStatus['isMe'] = $this->member_id == $mid ? true : false;
        // Comment editing privileges
        $commentEdit = ApiConfigHelper::getConfigByItemKey(FsConfig::COMMENT_EDIT) ?? false;
        $editTimeRole = ApiConfigHelper::getConfigByItemKey(FsConfig::COMMENT_EDIT_TIMELIMIT) ?? 5;
        $editSticky = ApiConfigHelper::getConfigByItemKey(FsConfig::COMMENT_EDIT_STICKY) ?? false;
        if ($commentEdit) {
            // How long you can edit
            if (strtotime($this->created_at) + ($editTimeRole * 60) > time()) {
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
            'pid' => $posts['uuid'],
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
            'shieldSetting' => $shieldSetting,
            'shieldName' => $shieldName,
            'shieldStatus' => $shieldStatus,
            'shieldMemberStatus' => $shieldMemberStatus,
            'likeCount' => $likeCount,
            'followCount' => $this->follow_count,
            'shieldCount' => $this->shield_count,
            'commentCount' => $commentCount,
            'commentLikeCount' => $commentLikeCount,
            'time' => $time,
            'timeFormat' => $timeFormat,
            'editTime' => $editTime,
            'editTimeFormat' => $editTimeFormat,
            'member' => $member,
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
