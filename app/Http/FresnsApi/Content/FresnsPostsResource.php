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
use App\Http\Center\Common\LogService;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsDb\FresnsComments\FresnsComments;
use App\Http\FresnsDb\FresnsComments\FresnsCommentsConfig;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Http\FresnsDb\FresnsDomainLinks\FresnsDomainLinksConfig;
use App\Http\FresnsDb\FresnsEmojis\FresnsEmojis;
use App\Http\FresnsDb\FresnsExtendLinkeds\FresnsExtendLinkeds;
use App\Http\FresnsDb\FresnsExtendLinkeds\FresnsExtendLinkedsConfig;
use App\Http\FresnsDb\FresnsExtends\FresnsExtends;
use App\Http\FresnsDb\FresnsExtends\FresnsExtendsConfig;
use App\Http\FresnsDb\FresnsFiles\FresnsFiles;
use App\Http\FresnsDb\FresnsGroups\FresnsGroups;
use App\Http\FresnsDb\FresnsGroups\FresnsGroupsConfig;
use App\Http\FresnsDb\FresnsHashtagLinkeds\FresnsHashtagLinkeds;
use App\Http\FresnsDb\FresnsHashtags\FresnsHashtags;
use App\Http\FresnsDb\FresnsImplants\FresnsImplants;
use App\Http\FresnsDb\FresnsImplants\FresnsImplantsConfig;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollows;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollowsConfig;
use App\Http\FresnsDb\FresnsMemberIcons\FresnsMemberIcons;
use App\Http\FresnsDb\FresnsMemberIcons\FresnsMemberIconsConfig;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikes;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikesConfig;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRolesConfig;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsMembers\FresnsMembersConfig;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShields;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShieldsConfig;
use App\Http\FresnsDb\FresnsPlugins\FresnsPluginsService;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsagesConfig;
use App\Http\FresnsDb\FresnsPostAllows\FresnsPostAllowsConfig;
use App\Http\FresnsDb\FresnsPostAppends\FresnsPostAppends;
use App\Http\FresnsDb\FresnsPostAppends\FresnsPostAppendsConfig;
use App\Http\FresnsDb\FresnsPostMembers\FresnsPostMembers;
use App\Http\FresnsDb\FresnsPosts\FresnsPosts;
use App\Http\FresnsDb\FresnsPosts\FresnsPostsConfig;
use App\Http\FresnsDb\FresnsPosts\FresnsPostsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * List resource config handle.
 */
class FresnsPostsResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Field

        // Data Table: post_appends
        $append = DB::table(FresnsPostAppendsConfig::CFG_TABLE)->where('post_id', $this->id)->first();
        if ($append) {
            $append = get_object_vars($append);
        }
        // Data Table: members
        $memberInfo = DB::table(FresnsMembersConfig::CFG_TABLE)->where('id', $this->member_id)->first();
        // Data Table: member_role_rels
        $roleRels = FresnsMemberRoleRels::where('member_id', $this->member_id)->where('type', 2)->first();
        // Data Table: member_roles
        $memberRole = [];
        if (! empty($roleRels)) {
            $memberRole = FresnsMemberRoles::find($roleRels['role_id']);
        }
        // Data Table: comments
        $comments = DB::table('comments as c')->select('c.*')
            ->leftJoin('members as m', 'c.member_id', '=', 'm.id')
            ->where('c.post_id', $this->id)
            ->where('m.deleted_at', null)
            ->where('c.deleted_at', null)
            ->orderby('like_count', 'Desc')
            ->first();
        // Data Table: groups
        $groupInfo = FresnsGroups::find($this->group_id);

        // Post Info
        $pid = $this->uuid;
        $mid = GlobalService::getGlobalKey('member_id');
        $input = [
            'member_id' => $mid,
            'like_type' => 4,
            'like_id' => $this->id,
        ];
        // $count = FresnsMemberLikes::where($input)->count();
        $count = DB::table(FresnsMemberLikesConfig::CFG_TABLE)->where($input)->count();
        $isLike = $count == 0 ? false : true;
        $title = $this->title;
        $content = self::getContentView(($this->content), ($this->id), 1);
        // Read permission required or not
        $allowStatus = $this->is_allow;
        $allowProportion = 10;
        $noAllow = 0;
        if ($allowStatus == 1) {
            $memberCount = DB::table(FresnsPostAllowsConfig::CFG_TABLE)->where('post_id', $this->id)->where('type', 1)->where('object_id', $mid)->count();
            $memberoleCount = 0;
            if (! empty($roleRels)) {
                $memberoleCount = DB::table(FresnsPostAllowsConfig::CFG_TABLE)->where('post_id', $this->id)->where('type', 2)->where('object_id', $roleRels['role_id'])->count();
            }
            // Read access
            if ($memberCount > 0 || $memberoleCount > 0) {
                $allowStatus = 1;
                $allowProportion = 100;
                $noAllow = 1;
            } else {
                $allowProportion = $append['allow_proportion'];
                $FresnsPostsService = new FresnsPostsService();
                // Prevent @, hashtags, emojis, links and other messages from being truncated
                $contentInfo = $FresnsPostsService->truncatedContentInfo($this->content);
                $content = self::getContentView(($contentInfo['truncated_content']), ($this->id), 1);

                $allowStatus = 0;
            }
        } else {
            $noAllow = 1;
        }
        $brief = $this->is_brief;
        $sticky = $this->sticky_state;
        $essence = $this->essence_state;

        // Operation behavior status
        $likeStatus = DB::table(FresnsMemberLikesConfig::CFG_TABLE)->where('member_id', $mid)->where('like_type', 4)->where('like_id', $this->id)->where('deleted_at', null)->count();
        $followStatus = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 4)->where('follow_id', $this->id)->where('deleted_at', null)->count();
        $shieldStatus = DB::table(FresnsMemberShieldsConfig::CFG_TABLE)->where('member_id', $mid)->where('shield_type', 4)->where('shield_id', $this->id)->where('deleted_at', null)->count();
        // Operation behavior settings
        $likeSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::LIKE_POST_SETTING);
        $followSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::FOLLOW_POST_SETTING);
        $shieldSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::SHIELD_POST_SETTING);
        // Operation behavior naming
        $likeName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::LIKE_POST_NAME) ?? 'Like';
        $followName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::FOLLOW_POST_NAME) ?? 'Save post';
        $shieldName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::SHIELD_POST_NAME) ?? 'Hide post';
        // Content Naming
        $PostName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::POST_NAME) ?? 'Post';

        $viewCount = $this->view_count;
        $likeCount = $this->like_count;
        $followCount = $this->follow_count;
        $shieldCount = $this->shield_count;
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

        $allowStatus = $this->is_allow;
        $allowBtnName = ApiLanguageHelper::getLanguagesByTableId(FresnsPostsConfig::CFG_TABLE, 'allow_btn_name', $this->id);
        $allowBtnUrl = FresnsPluginsService::getPluginUrlByUnikey($append['allow_plugin_unikey']);

        $memberListName = ApiLanguageHelper::getLanguagesByTableId(FresnsPostsConfig::CFG_TABLE, 'member_list_name', $this->id);
        $memberListCount = Db::table('post_members')->where('post_id', $this->id)->count();
        $memberListUrl = FresnsPluginsService::getPluginUrlByUnikey($append['member_list_plugin_unikey']);

        $member = [];
        $member['anonymous'] = $this->is_anonymous;
        $member['deactivate'] = false; //Not deactivated = false, Deactivated = true
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

        $member['decorate'] = '';
        $member['gender'] = '';
        $member['bio'] = '';
        $member['verifiedStatus'] = '';
        $member['verifiedIcon'] = '';
        $member['verifiedDesc'] = '';
        $member['icons'] = [];
        if ($this->is_anonymous == 0) {
            if ($memberInfo->deleted_at == null && $memberInfo) {
                $member['anonymous'] = $this->is_anonymous;
                $member['deactivate'] = false;
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
                LogService::info('decorate_file_id', $memberInfo);
                $member['gender'] = $memberInfo->gender ?? 0;
                $member['bio'] = $memberInfo->bio ?? '';
                $member['verifiedStatus'] = $memberInfo->verified_status ?? 1;
                $member['verifiedIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($memberInfo->verified_file_id, $memberInfo->verified_file_url);
                $member['verifiedDesc'] = $memberInfo->verified_desc ?? '';

                $memberIconsArr = FresnsMemberIcons::where('member_id', $this->member_id)->get()->toArray();
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

        // Post Hot
        $postHotStatus = ApiConfigHelper::getConfigByItemKey(FsConfig::POST_HOT);
        $postHotStatus = $postHotStatus == null ? 0 : $postHotStatus;
        $comment = [];
        $comment['status'] = false;
        if ($postHotStatus != 0 && ! empty($comments)) {
            // Check commenter information
            $commentMemberInfo = FresnsMembers::find($comments->member_id);
            $comment['status'] = true;
            $comment['anonymous'] = $comments->is_anonymous ?? '';
            // Is the author of the comment the author of the post himself
            $commentStatus = $this->member_id == $comments->member_id ? true : false;
            if ($comments->is_anonymous == 0) {
                $comment['isAuthor'] = $commentStatus;
                $comment['mid'] = $commentMemberInfo['uuid'] ?? '';
                $comment['mname'] = $commentMemberInfo['name'] ?? '';
                $comment['nickname'] = $commentMemberInfo['nickname'] ?? '';
            }

            // Default Avatar
            if (empty($commentStatus['avatar'])) {
                $defaultIcon = ApiConfigHelper::getConfigByItemKey(FsConfig::DEFAULT_AVATAR);
                $comment['avatar'] = $defaultIcon;
            }
            // Anonymous Avatar
            if ($comments->is_anonymous == 1) {
                $anonymousAvatar = ApiConfigHelper::getConfigByItemKey(FsConfig::ANONYMOUS_AVATAR);
                $comment['avatar'] = $anonymousAvatar;
            }
            $comment['avatar'] = ApiFileHelper::getImageAvatarUrl($comment['avatar']);

            $comment['cid'] = $comments->uuid ?? '';
            $comment['content'] = self::getContentView(($comments->content), ($comments->id), 2);
            $comment['likeCount'] = $comments->like_count ?? '';

            // Attached Quantity
            $attachCount = [];
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
            $comment['attachCount'] = $attachCount;
            $images = [];

            $fileInfo = FresnsFiles::where('file_type', 1)->where('table_name', FresnsCommentsConfig::CFG_TABLE)->where('table_id', $comments->id)->get();
            $comment['images'] = ApiFileHelper::antiTheftFile($fileInfo);

            if (($this->comment_like_count) < $postHotStatus) {
                $comment = [];
                $comment['status'] = false;
            }
        }

        $location = [];
        $location['isLbs'] = $this->is_lbs;
        $location['mapId'] = $this->map_id;
        $location['latitude'] = $this->map_latitude;
        $location['longitude'] = $this->map_longitude;
        $location['scale'] = $append['map_scale'] ?? '';
        $location['poi'] = $append['map_poi'] ?? '';
        $location['poiId'] = $append['map_poi_id'] ?? '';
        $location['distance'] = '';
        $longitude = request()->input('longitude', '');
        $latitude = request()->input('latitude', '');
        if ($longitude && $latitude && $this->map_latitude && $this->map_longitude) {
            // Get location units
            $langTag = $request->header('langTag');
            $distanceUnits = $request->input('lengthUnits');
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

        // Files
        $files = [];

        // Extends
        $extends = [];
        $extendsLinks = DB::table('extend_linkeds')->where('linked_type', 1)->where('linked_id', $this->id)->pluck('extend_id')->toArray();
        $extendsInfo = [];
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
        }

        // Group
        $group = [];
        if ($groupInfo) {
            $group['gid'] = $groupInfo['uuid'] ?? '';
            $group['gname'] = ApiLanguageHelper::getLanguagesByTableId(FresnsGroupsConfig::CFG_TABLE, 'name', $this->group_id);
            $group['cover'] = ApiFileHelper::getImageSignUrlByFileIdUrl($groupInfo['cover_file_id'], $groupInfo['cover_file_url']);
            $group['allow'] = true;
            // Whether the current member has the right to comment in the group
            $permission = $groupInfo['permission'] ?? '';
            $permissionArr = json_decode($permission, true);
            if ($permissionArr) {
                $publish_comment = $permissionArr['publish_comment'];
                $publish_comment_roles = $permissionArr['publish_comment_roles'];
                $group['allow'] = false;
                // 1.All Members
                if ($publish_comment == 1) {
                    $group['allow'] = true;
                }
                // 2.Anyone in the group
                if ($publish_comment == 2) {
                    $followCount = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)
                            ->where('member_id', $mid)
                            ->where('follow_type', 2)
                            ->where('follow_id', $groupInfo['id'])
                            ->where('deleted_at', null)
                            ->count();
                    if ($followCount > 0) {
                        $group['allow'] = true;
                    }
                }
                // 3.Specified role members only
                if ($publish_comment == 3) {
                    $memberRoleArr = FresnsMemberRoleRels::where('member_id', $mid)->pluck('role_id')->toArray();
                    $arrIntersect = array_intersect($memberRoleArr, $publish_comment_roles);
                    if ($arrIntersect) {
                        $group['allow'] = true;
                    }
                }
            }
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
                    // Query whether the current member is a group administrator
                    if (! $this->group_id) {
                        $manages = [];
                    } else {
                        $groupInfo = FresnsGroups::find($this->group_id);
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
        // Edit Status
        $postEdit = ApiConfigHelper::getConfigByItemKey(FsConfig::POST_EDIT) ?? false;
        $editTimeRole = ApiConfigHelper::getConfigByItemKey(FsConfig::POST_EDIT_TIMELIMIT) ?? 5;
        $editSticky = ApiConfigHelper::getConfigByItemKey(FsConfig::POST_EDIT_STICKY) ?? false;
        $editEssence = ApiConfigHelper::getConfigByItemKey(FsConfig::POST_EDIT_ESSENCE) ?? false;
        if ($postEdit) {
            // How long you can edit
            if (strtotime($this->created_at) + ($editTimeRole * 60) < time()) {
                $postEdit = false;
            }
            // Post top edit permission
            if ($this->sticky_state != 1) {
                if (! $editSticky) {
                    $postEdit = false;
                }
            }
            // Post editing privileges after adding essence
            if ($this->essence_state != 1) {
                if (! $editEssence) {
                    $postEdit = false;
                }
            }
        }
        $editStatus['canEdit'] = $postEdit;
        // Delete Status
        $editStatus['canDelete'] = $append['can_delete'] == 1 ? true : false;

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
            'brief' => $brief,
            'sticky' => $sticky,
            'essence' => $essence,
            'postName' => $PostName,
            'likeSetting' => $likeSetting,
            'likeName' => $likeName,
            'likeStatus' => $likeStatus,
            'followSetting' => $followSetting,
            'followName' => $followName,
            'followStatus' => $followStatus,
            'shieldSetting' => $shieldSetting,
            'shieldName' => $shieldName,
            'shieldStatus' => $shieldStatus,
            'memberListStatus' => $append['member_list_status'],
            'memberListName' => $memberListName,
            'memberListCount' => $memberListCount,
            'memberListUrl' => $memberListUrl,
            'viewCount' => $viewCount,
            'likeCount' => $likeCount,
            'followCount' => $followCount,
            'shieldCount' => $shieldCount,
            'commentCount' => $commentCount,
            'commentLikeCount' => $commentLikeCount,
            'time' => $time,
            'timeFormat' => $timeFormat,
            'editTime' => $editTime,
            'editTimeFormat' => $editTimeFormat,
            'allowStatus' => $allowStatus,
            'allowProportion' => $allowProportion,
            'allowBtnName' => $allowBtnName,
            'allowBtnUrl' => $allowBtnUrl,
            'member' => $member,
            'icons' => $icons,
            'commentSetting' => $comment,
            'location' => $location,
            'attachCount' => $attachCount,
            'files' => $files,
            'extends' => $extends,
            'group' => (object) $group,
            'manages' => $managesArr,
            'editStatus' => $editStatus,
        ];

        // Get posts from object to follow
        $uri = Request::getRequestUri();
        if ($uri == '/api/fresns/post/follows') {
            $followType = $this->contentByType($this->id);
            $default = [
                'followType' => $followType,
                'pid' => $pid,
                'title' => $title,
                'content' => $content,
                'brief' => $brief,
                'sticky' => $sticky,
                'essence' => $essence,
                'postName' => $PostName,
                'likeSetting' => $likeSetting,
                'likeName' => $likeName,
                'likeStatus' => $likeStatus,
                'followSetting' => $followSetting,
                'followName' => $followName,
                'followStatus' => $followStatus,
                'shieldSetting' => $shieldSetting,
                'shieldName' => $shieldName,
                'shieldStatus' => $shieldStatus,
                'memberListStatus' => $append['member_list_status'],
                'memberListName' => $memberListName,
                'memberListCount' => $memberListCount,
                'memberListUrl' => $memberListUrl,
                'viewCount' => $viewCount,
                'likeCount' => $likeCount,
                'followCount' => $followCount,
                'shieldCount' => $shieldCount,
                'commentCount' => $commentCount,
                'commentLikeCount' => $commentLikeCount,
                'time' => $time,
                'timeFormat' => $timeFormat,
                'editTime' => $editTime,
                'editTimeFormat' => $editTimeFormat,
                'allowStatus' => $allowStatus,
                'allowProportion' => $allowProportion,
                'allowBtnName' => $allowBtnName,
                'allowBtnUrl' => $allowBtnUrl,
                'member' => $member,
                'icons' => $icons,
                'commentSetting' => $comment,
                'location' => $location,
                'attachCount' => $attachCount,
                'files' => $files,
                'extends' => $extends,
                'group' => (object) $group,
                'hashtag' => (object) [],
                'manages' => $managesArr,
                'editStatus' => $editStatus,
            ];
            if ($followType == 'hashtag') {
                $hashtagId = FresnsHashtagLinkeds::where('linked_type', 1)->where('linked_id', $this->id)->first();
                $hashTagInfo = '';
                if ($hashtagId) {
                    $hashTagInfo = FresnsHashtags::find($hashtagId['hashtag_id']);
                }
                $hashtag = [];
                $hashtag['huri'] = $hashTagInfo['slug'] ?? '';
                $hashtag['hname'] = $hashTagInfo['name'] ?? '';
                $hashtag['cover'] = $hashTagInfo['cover_file_url'] ?? '';
                $default = [
                    'followType' => $followType,
                    'pid' => $pid,
                    'title' => $title,
                    'content' => $content,
                    'brief' => $brief,
                    'sticky' => $sticky,
                    'essence' => $essence,
                    'postName' => $PostName,
                    'likeSetting' => $likeSetting,
                    'likeName' => $likeName,
                    'likeStatus' => $likeStatus,
                    'followSetting' => $followSetting,
                    'followName' => $followName,
                    'followStatus' => $followStatus,
                    'shieldSetting' => $shieldSetting,
                    'shieldName' => $shieldName,
                    'shieldStatus' => $shieldStatus,
                    'memberListStatus' => $append['member_list_status'],
                    'memberListName' => $memberListName,
                    'memberListCount' => $memberListCount,
                    'memberListUrl' => $memberListUrl,
                    'viewCount' => $viewCount,
                    'likeCount' => $likeCount,
                    'followCount' => $followCount,
                    'shieldCount' => $shieldCount,
                    'commentCount' => $commentCount,
                    'commentLikeCount' => $commentLikeCount,
                    'time' => $time,
                    'timeFormat' => $timeFormat,
                    'editTime' => $editTime,
                    'editTimeFormat' => $editTimeFormat,
                    'allowStatus' => $allowStatus,
                    'allowProportion' => $allowProportion,
                    'allowBtnName' => $allowBtnName,
                    'allowBtnUrl' => $allowBtnUrl,
                    'member' => $member,
                    'icons' => $icons,
                    'commentSetting' => $comment,
                    'location' => $location,
                    'attachCount' => $attachCount,
                    'files' => $files,
                    'extends' => $extends,
                    'group' => (object) $group,
                    'hashtag' => (object) $hashtag,
                    'manages' => $managesArr,
                    'editStatus' => $editStatus,
                ];
            }
        }
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
            $s = round($s * 1000 * 0.62);
        } else {
            $s = round($s * 1000);
        }
        $s = round($s / 1000) == 0 ? 1 : round($s / 1000);

        return $s.$distanceUnits;
    }

    private function rad($d)
    {
        return $d * M_PI / 180.0;
    }

    // Content Data Export Operations
    public static function getContentView($content, $postId, $postType, $content_markdown = 0)
    {
        $request = request();
        if (! $content_markdown) {
            // Link
            preg_match_all("/http[s]{0,1}:\/\/.*?\s/", $content, $hrefMatches);
            if ($hrefMatches[0]) {
                foreach ($hrefMatches[0] as &$h) {
                    $h = trim($h);
                    // Does the link association table exist title
                    $domainLinked = DB::table(FresnsDomainLinksConfig::CFG_TABLE)->where('link_url',
                        $h)->where('linked_type', $postType)->where('linked_id', $postId)->first();
                    $title = $h;
                    if (! empty($domainLinked)) {
                        if ($domainLinked->link_title) {
                            $title = $domainLinked->link_title;
                        }
                    }
                    $content = str_replace($h, "<a href='$h' target='_blank' class='fresns_content_link'>$title</a>", $content);
                }
            }
        }

        // Emoji
        preg_match_all("/\[.*?\]/", $content, $emojis);
        if ($emojis[0]) {
            foreach ($emojis[0] as $e) {
                $emojiName = str_replace('[', '', $e);
                $emoji = str_replace(']', '', $emojiName);
                $emojiInfo = FresnsEmojis::where('code', $emoji)->where('is_enable', 1)->first();
                if ($emojiInfo) {
                    $url = $emojiInfo['image_file_url'];
                    $content = str_replace($e, "<img src='$url' class='fresns_content_emoji' />", $content);
                }
            }
        }

        // mention @
        preg_match_all("/@.*?\s/", $content, $member);
        if ($member[0]) {
            foreach ($member[0] as $m) {
                $mname = trim(str_replace('@', '', $m));
                $trimName = trim($m);
                $memberInfo = FresnsMembers::where('name', $mname)->first();
                if ($memberInfo) {
                    $jumpUrl = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_DOMAIN)."/m/$mname";
                    $content = str_replace($m, "<a href='{$jumpUrl}' class='fresns_content_mention'>@{$memberInfo['nickname']}</a> ", $content);
                }
            }
        }

        // Hashtag
        $hashtagShow = ApiConfigHelper::getConfigByItemKey('hashtag_show') ?? 2;
        // Find if a post has a related hashtag
        $postHash = FresnsHashtagLinkeds::where('linked_type', $postType)->where('linked_id', $postId)->pluck('hashtag_id')->toArray();
        if ($postHash) {
            foreach ($postHash as $p) {
                // Get hashtag information
                $hashTagInfo = FresnsHashtags::find($p);
                if ($hashTagInfo) {
                    $onehashName = '#'.$hashTagInfo['name'];
                    $twohashName = '#'.$hashTagInfo['name'].'#';
                    $findCount = strpos($content, $twohashName);
                    $jumpUrl = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_DOMAIN)."/hashtag/{$hashTagInfo['slug']}";
                    if ($hashtagShow == 1) {
                        if ($findCount !== false) {
                            $content = str_replace($twohashName, "<a href='{$jumpUrl}' class='fresns_content_hashtag'>$onehashName</a>", $content);
                        } else {
                            $content = str_replace($onehashName, "<a href='{$jumpUrl}' class='fresns_content_hashtag'>$onehashName</a>", $content);
                        }
                    } else {
                        if ($findCount !== false) {
                            $content = str_replace($twohashName, "<a href='{$jumpUrl}' class='fresns_content_hashtag'>$twohashName</a>", $content);
                        } else {
                            $onehashNameNotrim = '#'.$hashTagInfo['name'].' ';
                            $content = str_replace($onehashNameNotrim, "<a href='{$jumpUrl}' class='fresns_content_hashtag'>$twohashName</a>", $content);
                        }
                    }
                }
            }
        }

        return $content;
    }

    // Determine which follow object the current content comes from
    public function contentByType($id)
    {
        $request = request();
        $followType = '';
        $followType = $request->input('followType');
        $mid = GlobalService::getGlobalKey('member_id');
        if (! $followType) {
            // Posts by following hashtags
            $folloHashtagArr = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 3)->where('deleted_at', null)->pluck('follow_id')->toArray();
            $postIdArr = FresnsHashtagLinkeds::where('linked_type', 1)->whereIn('hashtag_id', $folloHashtagArr)->pluck('linked_id')->toArray();
            $postHashtagIdArr = FresnsPosts::whereIn('id', $postIdArr)->where('essence_state', '!=', 1)->pluck('id')->toArray();
            if (in_array($id, $postHashtagIdArr)) {
                $followType = 'hashtag';
            }
            // Posts by following groups
            $folloGroupArr = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 2)->where('deleted_at', null)->pluck('follow_id')->toArray();
            $postGroupIdArr = FresnsPosts::whereIn('group_id', $folloGroupArr)->where('essence_state', '!=', 1)->pluck('id')->toArray();
            if (in_array($id, $postGroupIdArr)) {
                $followType = 'group';
            }
            // Only posts that have been added to the essence are exported under groups and hashtags
            // Posts set as secondary essence, forced output
            $essenceIdArr = FresnsPosts::where('essence_state', 3)->pluck('id')->toArray();
            if (in_array($id, $essenceIdArr)) {
                $followType = 'group';
            }
            // My posts
            $mePostsArr = FresnsPosts::where('member_id', $mid)->pluck('id')->toArray();
            if (in_array($id, $mePostsArr)) {
                $followType = 'member';
            }
            // Posts by following members
            $followMemberArr = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 1)->pluck('follow_id')->toArray();
            $postMemberIdArr = FresnsPosts::whereIn('member_id', $followMemberArr)->pluck('id')->toArray();
            if (in_array($id, $postMemberIdArr)) {
                $followType = 'member';
            }
        }

        return $followType;
    }
}
