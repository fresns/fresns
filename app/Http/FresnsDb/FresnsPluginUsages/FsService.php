<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsPluginUsages;

use App\Base\Services\BaseAdminService;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsService;
use App\Http\FresnsDb\FresnsGroups\FresnsGroups;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsPlugins\FresnsPlugins;

class FsService extends BaseAdminService
{
    protected $needCommon = false;

    public function __construct()
    {
        $this->model = new FsModel();
        $this->resource = FsResource::class;
        $this->resourceDetail = FsResourceDetail::class;
    }

    public function common()
    {
        $common = parent::common();
        $common['TableName'] = FsConfig::CFG_TABLE;

        // Scenes
        $common['sceneOption'] = FsConfig::SCONE_OPTION;
        $common['typeOption'] = FsConfig::TYPE_OPTION;

        // Group
        $common['groupOption'] = FresnsGroups::buildSelectTreeData('id', 'name', ['is_enable' => 1]);
        $common['isGroupAdminOption'] = FsCOnfig::IS_GROUP_ADMIN_OPTION;

        // Languages
        $languageArr = FresnsConfigsService::getLanguageStatus();
        $common['language_status'] = $languageArr['language_status'];
        $common['default_language'] = $languageArr['default_language'];
        $common['multilingualoption'] = $languageArr['languagesOption'];

        // Tips
        $common['roleMembersTips'] = FsConfig::ROLE_MEMBERS_TIPS;
        $common['editerNumberTips'] = FsConfig::EDITER_NUMBER_TIPS;
        $common['isAdminTips'] = FsConfig::IS_ADMIN_TIPS;

        // Plugin
        $common['plugOption'] = FresnsPlugins::staticBuildSelectOptions2('unikey', 'name', []);

        // Role
        $common['roleOption'] = FresnsMemberRoles::buildSelectTreeData('id', 'name', []);

        // Data Service Provider Plugin
        $common['restfulPlugin'] = FresnsPlugins::where('scene', 'like', '%restful%')->get([
            'unikey as key',
            'name as text',
        ]);

        return $common;
    }

    // Get default language tag
    public static function getDefaultLanguage()
    {
        $languageArr = FresnsConfigsService::getLanguageStatus();
        $code = $languageArr['default_language'];

        return $code;
    }

    public function hookUpdateAfter($id)
    {
        $this->model->hookUpdateAfter($id);
    }
}
