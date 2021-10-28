<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Content;

use App\Base\Resources\BaseAdminResource;
use App\Http\Center\Common\GlobalService;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Http\FresnsDb\FresnsGroups\FresnsGroups;
use App\Http\FresnsDb\FresnsGroups\FresnsGroupsConfig;
use App\Http\FresnsDb\FresnsGroups\FresnsGroupsService;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollows;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollowsConfig;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikes;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikesConfig;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShields;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShieldsConfig;
use App\Http\FresnsDb\FresnsPlugins\FresnsPluginsService;
use Illuminate\Support\Facades\DB;

/**
 * List resource config handle.
 */
class FresnsGroupsTreesResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Field
        $formMap = FresnsGroupsConfig::FORM_FIELDS_MAP;
        $formMapFieldsArr = [];
        foreach ($formMap as $k => $dbField) {
            $formMapFieldsArr[$dbField] = $this->$dbField;
        }

        // Group Info
        $mid = GlobalService::getGlobalKey('member_id');
        $groupSize = $request->input('groupSize');
        $gid = $this->uuid;
        $type = $this->type;
        $parentId = $this->parent_id;
        $gname = ApiLanguageHelper::getLanguagesByTableId(FresnsGroupsConfig::CFG_TABLE, 'name', $this->id);
        $description = ApiLanguageHelper::getLanguagesByTableId(FresnsGroupsConfig::CFG_TABLE, 'description', $this->id);
        $cover = ApiFileHelper::getImageSignUrlByFileIdUrl($this->cover_file_id, $this->cover_file_url);
        $banner = ApiFileHelper::getImageSignUrlByFileIdUrl($this->banner_file_id, $this->banner_file_url);
        $groups = [];
        // type_find = 2 (Hidden: Only members can find this group.)
        $FresnsGroups = FresnsGroups::where('type_find', 2)->pluck('id')->toArray();
        // $FresnsGroups = FresnsGroups::where('type_mode', 2)->where('type_find', 2)->pluck('id')->toArray();
        $groupMember = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 2)->pluck('follow_id')->toArray();
        $noGroupArr = array_diff($FresnsGroups, $groupMember);
        $TreesGroups = FresnsGroups::where('parent_id', $this->id)->where('is_enable', 1)->whereNotIn('id', $noGroupArr)->limit($groupSize)->orderby('rank_num', 'asc')->get()->toArray();
        $groupCount = count($TreesGroups);

        if ($TreesGroups) {
            $arr = [];
            foreach ($TreesGroups as $c) {
                $arr['gid'] = $c['uuid'];
                $arr['type'] = $c['type'];
                $arr['gname'] = ApiLanguageHelper::getLanguagesByTableId(FresnsGroupsConfig::CFG_TABLE, 'name', $c['id']);
                $arr['description'] = ApiLanguageHelper::getLanguagesByTableId(FresnsGroupsConfig::CFG_TABLE, 'description', $c['id']);
                $arr['cover'] = ApiFileHelper::getImageSignUrlByFileIdUrl($c['cover_file_id'], $c['cover_file_url']);
                $arr['banner'] = ApiFileHelper::getImageSignUrlByFileIdUrl($c['banner_file_id'], $c['banner_file_url']);
                $arr['recommend'] = $c['is_recommend'];
                $arr['mode'] = $c['type_mode'];
                $arr['find'] = $c['type_find'];
                $arr['followType'] = $c['type_follow'];
                $arr['followUrl'] = FresnsPluginsService::getPluginUrlByUnikey($c['plugin_unikey']);

                // Operation behavior status
                $arr['likeStatus'] = DB::table(FresnsMemberLikesConfig::CFG_TABLE)->where('member_id', $mid)->where('like_type', 2)->where('like_id', $c['id'])->where('deleted_at', null)->count();
                $arr['followStatus'] = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 2)->where('follow_id', $c['id'])->where('deleted_at', null)->count();
                $arr['shieldStatus'] = DB::table(FresnsMemberShieldsConfig::CFG_TABLE)->where('member_id', $mid)->where('shield_type', 2)->where('shield_id', $c['id'])->where('deleted_at', null)->count();
                // Operation behavior settings
                $arr['likeSetting'] = ApiConfigHelper::getConfigByItemKey(FsConfig::LIKE_GROUP_SETTING);
                $arr['followSetting'] = ApiConfigHelper::getConfigByItemKey(FsConfig::FOLLOW_GROUP_SETTING);
                $arr['shieldSetting'] = ApiConfigHelper::getConfigByItemKey(FsConfig::SHIELD_GROUP_SETTING);
                // Operation behavior naming
                $arr['likeName'] = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::LIKE_GROUP_NAME) ?? 'Like';
                $arr['followName'] = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::FOLLOW_GROUP_NAME) ?? 'Join';
                $arr['shieldName'] = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::SHIELD_GROUP_NAME) ?? 'Block';
                // Content Naming
                $arr['groupName'] = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::GROUP_NAME) ?? 'Group';

                $arr['viewCount'] = $c['view_count'];
                $arr['likeCount'] = $c['like_count'];
                $arr['followCount'] = $c['follow_count'];
                $arr['shieldCount'] = $c['shield_count'];
                $arr['postCount'] = $c['post_count'];
                $arr['essenceCount'] = $c['essence_count'];

                // Group Administrator List
                $arr['admins'] = FresnsGroupsService::adminData($c['permission']);

                // Whether the member currently requesting the interface has permission to post to the group
                $arr['publishRule'] = FresnsGroupsService::publishRule($mid, $c['permission'], $this->id);

                // groups > permission field other content
                $arr['permission'] = FresnsGroupsService::othetPession($c['permission']);
                $groups[] = $arr;
            }
        }

        // Default Field
        $default = [
            'gid' => $gid,
            'gname' => $gname,
            'description' => $description,
            'cover' => $cover,
            'banner' => $banner,
            'groupCount' => $groupCount,
            'groups' => $groups,
        ];

        // Merger
        $arr = $default;

        return $arr;
    }
}
