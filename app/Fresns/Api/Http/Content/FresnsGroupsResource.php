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
class FresnsGroupsResource extends BaseAdminResource
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
        $gid = $this->gid;
        $type = $this->type;
        $parentId = $this->parent_id;
        $parentGroupInfo = FresnsGroups::where('id', $parentId)->first();
        $parentId = $parentGroupInfo['gid'] ?? null;
        $uid = GlobalService::getGlobalKey('user_id');
        $gname = ApiLanguageHelper::getLanguagesByTableId(FresnsGroupsConfig::CFG_TABLE, 'name', $this->id);
        $description = ApiLanguageHelper::getLanguagesByTableId(FresnsGroupsConfig::CFG_TABLE, 'description', $this->id);
        $cover = ApiFileHelper::getImageSignUrlByFileIdUrl($this->cover_file_id, $this->cover_file_url);
        $banner = ApiFileHelper::getImageSignUrlByFileIdUrl($this->banner_file_id, $this->banner_file_url);
        $followUrl = FresnsPluginsService::getPluginUrlByUnikey($this->plugin_unikey);

        // Operation behavior status
        $likeStatus = DB::table(FresnsUserLikesConfig::CFG_TABLE)->where('user_id', $uid)->where('like_type', 2)->where('like_id', $this->id)->where('deleted_at', null)->count();
        $followStatus = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 2)->where('follow_id', $this->id)->where('deleted_at', null)->count();
        $blockStatus = DB::table(FresnsUserBlocksConfig::CFG_TABLE)->where('user_id', $uid)->where('block_type', 2)->where('block_id', $this->id)->where('deleted_at', null)->count();
        // Operation behavior settings
        $likeSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::LIKE_GROUP_SETTING);
        $followSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::FOLLOW_GROUP_SETTING);
        $blockSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::SHIELD_GROUP_SETTING);
        // Operation behavior naming
        $likeName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::LIKE_GROUP_NAME) ?? 'Like';
        $followName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::FOLLOW_GROUP_NAME) ?? 'Join';
        $blockName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::SHIELD_GROUP_NAME) ?? 'Block';
        // Content Naming
        $groupName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::GROUP_NAME) ?? 'Group';

        $parentInfo = [];
        $parentGroup = FresnsGroups::find($this->parent_id);
        if ($parentGroup) {
            $parentInfo['gid'] = $parentGroup['gid'] ?? null;
            $parentInfo['gname'] = ApiLanguageHelper::getLanguagesByTableId(FresnsGroupsConfig::CFG_TABLE, 'name', $this->parent_id);
            $parentInfo['cover'] = ApiFileHelper::getImageSignUrlByFileIdUrl($parentGroup['cover_file_id'], $parentGroup['cover_file_url']);
        }
        $admins = [];
        if ($type != 1) {
            $admins = FresnsGroupsService::adminData($this->permission);
        }
        $publishRule = [];
        if ($type != 1) {
            $publishRule = FresnsGroupsService::publishRule($uid, $this->permission, $this->id);
        }
        $permission = [];
        if ($type != 1) {
            $permission = FresnsGroupsService::othetPession($this->permission);
        }

        // Default Field
        $default = [
            'gid' => $gid,
            'gname' => $gname,
            'type' => $type,
            'description' => $description,
            'cover' => $cover,
            'banner' => $banner,
            'recommend' => $this->is_recommend,
            'mode' => $this->type_mode,
            'find' => $this->type_find,
            'followSetting' => $followSetting,
            'followName' => $followName,
            'followStatus' => $followStatus,
            'followType' => $this->type_follow,
            'followUrl' => $followUrl,
            'likeSetting' => $likeSetting,
            'likeName' => $likeName,
            'likeStatus' => $likeStatus,
            'blockSetting' => $blockSetting,
            'blockName' => $blockName,
            'blockStatus' => $blockStatus,
            'groupName' => $groupName,
            'viewCount' => $this->view_count,
            'likeCount' => $this->like_count,
            'followCount' => $this->follow_count,
            'blockCount' => $this->block_count,
            'postCount' => $this->post_count,
            'digestCount' => $this->digest_count,
            'parentInfo' => $parentInfo,
            'admins' => $admins,
            'publishRule' => $publishRule,
            'permission' => $permission,
        ];

        // Merger
        $arr = $default;

        return $arr;
    }
}
