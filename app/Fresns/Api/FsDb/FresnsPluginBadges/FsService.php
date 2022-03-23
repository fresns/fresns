<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsPluginBadges;

use App\Fresns\Api\Base\Services\BaseAdminService;
use App\Fresns\Api\FsDb\FresnsLanguages\FresnsLanguagesService;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPluginsService;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsagesConfig;

class FsService extends BaseAdminService
{
    public function __construct()
    {
        $this->model = new FsModel();
        $this->resource = FsResource::class;
        $this->resourceDetail = FsResourceDetail::class;
    }

    public function common()
    {
        $common = parent::common();

        return $common;
    }

    // Get Plugin
    public static function getWalletPluginExpands($user_id, $type, $langTag)
    {
        $unikeyArr = FresnsPluginBadges::where('user_id', $user_id)->pluck('plugin_unikey')->toArray();
        $payArr = FresnsPluginUsages::whereIn('plugin_unikey', $unikeyArr)->where('type', $type)->get()->toArray();
        $expandsArr = [];
        foreach ($payArr as $v) {
            $item = [];
            $item['plugin'] = $v['plugin_unikey'];
            $item['name'] = FresnsLanguagesService::getLanguageByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $v['id'], $langTag);
            $item['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['icon_file_id'], $v['icon_file_url']);
            $item['url'] = FresnsPluginsService::getPluginUsagesUrl($pluginUsages['plugin_unikey'], $v['id']);
            $badges = FresnsPluginBadges::where('user_id', $user_id)->where('plugin_unikey', $v['plugin_unikey'])->first();
            $item['badgesType'] = $badges['display_type'];
            $item['badgesValue'] = $badges['value_text'];
            $expandsArr[] = $item;
        }

        return $expandsArr;
    }
}
