<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsGroups;

use App\Base\Services\BaseAdminService;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsService;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguages;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsPlugins\FresnsPlugins;

class FsService extends BaseAdminService
{
    public function __construct()
    {
        $this->config = new FsConfig();
        $this->model = new FsModel();
        $this->resource = FsResource::class;
        $this->resourceDetail = FsResourceDetail::class;
    }

    // Group Common
    public function common()
    {
        $common = parent::common();
        $common['recommendOption'] = FsConfig::RECOMMEND_OPTION;
        $common['typeModel'] = FsConfig::TYPE_MODE;
        $common['typeFind'] = FsConfig::TYPE_FIND;
        $common['typeFollow'] = FsConfig::TYPE_FOLLOW;
        $common['publishPostOption'] = FsConfig::PUBLISH_POST;
        // Group Administrator
        $common['memberOption'] = FresnsMembers::buildSelectTreeDataByNoRankNum('id', 'name', ['is_enable' => 1]);
        // Role Permissions
        $common['roleOption'] = FresnsMemberRoles::buildSelectTreeData('id', 'name', []);
        // Language
        $languageArr = FresnsConfigsService::getLanguageStatus();
        $common['language_status'] = $languageArr['language_status'];
        $common['default_language'] = $languageArr['default_language'];
        $common['multilingualoption'] = $languageArr['languagesOption'];
        // Group Categories
        $common['groupOption'] = FresnsGroups::buildSelectTreeData('id', 'name', ['is_enable' => 1]);
        $common['oneGroupOption'] = FresnsGroups::staticBuildSelectOptions('id', 'name', ['parent_id'=>null]);
        // Plugin
        $common['plugOption'] = FresnsPlugins::staticBuildSelectOptions2('unikey', 'name', []);

        return $common;
    }

    public function hookUpdateAfter($id)
    {
        $this->model->hookUpdateAfter($id);
    }

    // Generate data
    public function buildTreeData(&$itemArr, &$categoryArr)
    {
        foreach ($itemArr as &$item) {
            // Language (name and description)
            $nameArr = self::getLangaugeArr(FsConfig::CFG_TABLE, FsConfig::FORM_FIELDS_MAP['name'], $item);
            $descriptionArr = self::getLangaugeArr(FsConfig::CFG_TABLE, FsConfig::FORM_FIELDS_MAP['description'],
                $item);
            $item['nameArr'] = $nameArr;
            $item['descriptionArr'] = $descriptionArr;
            $children = $item->children;
            // admin_members
            $admin_members_arr = explode(',', $item->admin_members);
            $allow_view_arr = $item->allow_view != null ? explode(',', $item->allow_view) : [];
            $allow_post_arr = $item->allow_post != null ? explode(',', $item->allow_post) : [];
            $allow_comment_arr = $item->allow_post != null ? explode(',', $item->allow_comment) : [];
            $item['admin_members_arr'] = $admin_members_arr;
            $item['allow_view_arr'] = $allow_view_arr;
            $item['allow_post_arr'] = $allow_post_arr;
            $item['allow_comment_arr'] = $allow_comment_arr;
            // get children
            $directChildren = [];
            foreach ($children as $child) {
                if ($child->parent_id == $item->id) {
                    $directChildren[] = $child;
                }
            }

            $children = $directChildren;

            $c = [];
            $c['key'] = $item->id;
            $c['value'] = $item->id;
            $c['name'] = $item->name;
            $c['title'] = $item->name;

            if ($children && count($children) > 0) {
                $this->buildTreeData($children, $c['children']);
            }

            $categoryArr[] = $c;
        }
    }

    // Get Langauge
    public static function getLangaugeArr($table, $table_field, $item)
    {
        $languageArr = FresnsConfigsService::getLanguageStatus();
        $multilingual = $languageArr['languagesOption'];
        $nameArr = [];
        foreach ($multilingual as $v) {
            $input = [
                'table_name' => $table,
                'table_field' => $table_field,
                'table_id' => $item->id,
                'lang_tag' => $v['key'],
            ];
            $name = FresnsLanguages::where($input)->first();
            $v['lang_content'] = $name['lang_content'] ?? '';
            $nameArr[] = $v;
        }

        return $nameArr;
    }
}
