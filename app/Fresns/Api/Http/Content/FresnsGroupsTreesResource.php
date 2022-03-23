<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Content;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Fresns\Api\Helpers\ApiLanguageHelper;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroups;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroupsConfig;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroupsService;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollowsConfig;
use App\Fresns\Api\FsDb\FresnsUserLikes\FresnsUserLikesConfig;
use App\Fresns\Api\FsDb\FresnsUserBlocks\FresnsUserBlocksConfig;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPluginsService;
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
        $uid = GlobalService::getGlobalKey('user_id');
        $groupSize = $request->input('groupSize');
        $gid = $this->gid;
        $type = $this->type;
        $parentId = $this->parent_id;
        $gname = ApiLanguageHelper::getLanguagesByTableId(FresnsGroupsConfig::CFG_TABLE, 'name', $this->id);
        $description = ApiLanguageHelper::getLanguagesByTableId(FresnsGroupsConfig::CFG_TABLE, 'description', $this->id);
        $cover = ApiFileHelper::getImageSignUrlByFileIdUrl($this->cover_file_id, $this->cover_file_url);
        $banner = ApiFileHelper::getImageSignUrlByFileIdUrl($this->banner_file_id, $this->banner_file_url);
        $groups = [];
        // type_find = 2 (Hidden: Only users can find this group.)
        $FresnsGroups = FresnsGroups::where('type_find', 2)->pluck('id')->toArray();
        // $FresnsGroups = FresnsGroups::where('type_mode', 2)->where('type_find', 2)->pluck('id')->toArray();
        $groupUser = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 2)->pluck('follow_id')->toArray();
        $noGroupArr = array_diff($FresnsGroups, $groupUser);
        $TreesGroups = FresnsGroups::where('parent_id', $this->id)->where('is_enable', 1)->whereNotIn('id', $noGroupArr)->limit($groupSize)->orderby('rank_num', 'asc')->get()->toArray();
        $groupCount = count($TreesGroups);

        if ($TreesGroups) {
            $arr = [];
            foreach ($TreesGroups as $c) {
                $arr['gid'] = $c['gid'];
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
                $arr['likeStatus'] = DB::table(FresnsUserLikesConfig::CFG_TABLE)->where('user_id', $uid)->where('like_type', 2)->where('like_id', $c['id'])->where('deleted_at', null)->count();
                $arr['followStatus'] = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 2)->where('follow_id', $c['id'])->where('deleted_at', null)->count();
                $arr['blockStatus'] = DB::table(FresnsUserBlocksConfig::CFG_TABLE)->where('user_id', $uid)->where('block_type', 2)->where('block_id', $c['id'])->where('deleted_at', null)->count();
                // Operation behavior settings
                $arr['likeSetting'] = ApiConfigHelper::getConfigByItemKey(FsConfig::LIKE_GROUP_SETTING);
                $arr['followSetting'] = ApiConfigHelper::getConfigByItemKey(FsConfig::FOLLOW_GROUP_SETTING);
                $arr['blockSetting'] = ApiConfigHelper::getConfigByItemKey(FsConfig::SHIELD_GROUP_SETTING);
                // Operation behavior naming
                $arr['likeName'] = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::LIKE_GROUP_NAME) ?? 'Like';
                $arr['followName'] = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::FOLLOW_GROUP_NAME) ?? 'Join';
                $arr['blockName'] = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::SHIELD_GROUP_NAME) ?? 'Block';
                // Content Naming
                $arr['groupName'] = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::GROUP_NAME) ?? 'Group';

                $arr['viewCount'] = $c['view_count'];
                $arr['likeCount'] = $c['like_count'];
                $arr['followCount'] = $c['follow_count'];
                $arr['blockCount'] = $c['block_count'];
                $arr['postCount'] = $c['post_count'];
                $arr['digestCount'] = $c['digest_count'];

                // Group Administrator List
                $arr['admins'] = FresnsGroupsService::adminData($c['permission']);

                // Whether the user currently requesting the interface has permission to post to the group
                $arr['publishRule'] = FresnsGroupsService::publishRule($uid, $c['permission'], $this->id);

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
