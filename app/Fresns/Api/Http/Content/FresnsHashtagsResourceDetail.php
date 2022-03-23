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
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroupsConfig;
use App\Fresns\Api\FsDb\FresnsHashtags\FresnsHashtags;
use App\Fresns\Api\FsDb\FresnsHashtags\FresnsHashtagsConfig;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollowsConfig;
use App\Fresns\Api\FsDb\FresnsUserLikes\FresnsUserLikesConfig;
use App\Fresns\Api\FsDb\FresnsUserBlocks\FresnsUserBlocksConfig;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsagesService;
use Illuminate\Support\Facades\DB;

/**
 * Detail resource config handle.
 */
class FresnsHashtagsResourceDetail extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Field
        $formMap = FresnsGroupsConfig::FORM_FIELDS_MAP;
        $formMapFieldsArr = [];
        foreach ($formMap as $k => $dbField) {
            $formMapFieldsArr[$dbField] = $this->$dbField;
        }

        // Hashtag Info
        $uid = GlobalService::getGlobalKey('user_id');
        $langTag = $request->header('langTag');
        $description = ApiLanguageHelper::getLanguagesByTableId(FresnsHashtagsConfig::CFG_TABLE, 'description', $this->id);
        $cover = ApiFileHelper::getImageSignUrlByFileIdUrl($this->cover_file_id, $this->cover_file_url);

        // Operation behavior status
        $likeStatus = DB::table(FresnsUserLikesConfig::CFG_TABLE)->where('user_id', $uid)->where('like_type', 3)->where('like_id', $this->id)->where('deleted_at', null)->count();
        $followStatus = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 3)->where('follow_id', $this->id)->where('deleted_at', null)->count();
        $blockStatus = DB::table(FresnsUserBlocksConfig::CFG_TABLE)->where('user_id', $uid)->where('block_type', 3)->where('block_id', $this->id)->where('deleted_at', null)->count();
        // Operation behavior settings
        $likeSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::LIKE_HASHTAG_SETTING);
        $blockSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::SHIELD_HASHTAG_SETTING);
        $followSetting = ApiConfigHelper::getConfigByItemKey(FsConfig::FOLLOW_HASHTAG_SETTING);
        // Operation behavior naming
        $likeName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::LIKE_HASHTAG_NAME) ?? 'Like';
        $followName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::FOLLOW_HASHTAG_NAME) ?? 'Watching';
        $blockName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::SHIELD_HASHTAG_NAME) ?? 'Block';
        // Content Naming
        $hashtagName = ApiLanguageHelper::getLanguagesByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', FsConfig::HASHTAG_NAME) ?? 'Hashtag';

        // user_blocks: query the table to confirm if the object is blocked
        $blockUserStatus = DB::table(FresnsUserBlocksConfig::CFG_TABLE)->where('user_id', $uid)->where('block_type', 1)->where('block_id', $this->user_id)->count();

        if (! $langTag) {
            $langTag = FresnsPluginUsagesService::getDefaultLanguage();
        }
        $seo = DB::table('seo')->where('linked_type', 2)->where('linked_id', $this->id)->where('lang_tag', $langTag)->where('deleted_at', null)->first();
        $seoInfo = [];
        if ($seo) {
            $seoInfo['title'] = $seo->title;
            $seoInfo['keywords'] = $seo->keywords;
            $seoInfo['description'] = $seo->description;
        }
        FresnsHashtags::where('id', $this->id)->increment('view_count');

        // Default Field
        $default = [
            'huri' => $this->slug,
            'hname' => $this->name,
            'cover' => $cover,
            'description' => $description,
            'hashtagName' => $hashtagName,
            'likeSetting' => $likeSetting,
            'likeName' => $likeName,
            'likeStatus' => $likeStatus,
            'followSetting' => $followSetting,
            'followName' => $followName,
            'followStatus' => $followStatus,
            'blockSetting' => $blockSetting,
            'blockName' => $blockName,
            'blockStatus' => $blockStatus,
            'viewCount' => $this->view_count,
            'likeCount' => $this->like_count,
            'followCount' => $this->follow_count,
            'blockCount' => $this->block_count,
            'postCount' => $this->post_count,
            'digestCount' => $this->digest_count,
            // 'seoInfo' => $seoInfo
            // 'followName' => $followName,
            // 'likeName' => $likeName,
            // 'blockName' => $blockName,
        ];

        // Merger
        $arr = $default;

        return $arr;
    }
}
