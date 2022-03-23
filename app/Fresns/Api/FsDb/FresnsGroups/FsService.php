<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsGroups;

use App\Fresns\Api\Base\Services\BaseAdminService;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigsService;
use App\Fresns\Api\FsDb\FresnsLanguages\FresnsLanguages;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPlugins;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRoles;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;

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
        $common['userOption'] = FresnsUsers::buildSelectTreeDataByNoRankNum('id', 'name', ['is_enable' => 1]);
        // Role Permissions
        $common['roleOption'] = FresnsRoles::buildSelectTreeData('id', 'name', []);
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
            // admin_users
            $admin_users_arr = explode(',', $item->admin_users);
            $allow_view_arr = $item->allow_view != null ? explode(',', $item->allow_view) : [];
            $allow_post_arr = $item->allow_post != null ? explode(',', $item->allow_post) : [];
            $allow_comment_arr = $item->allow_post != null ? explode(',', $item->allow_comment) : [];
            $item['admin_users_arr'] = $admin_users_arr;
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
    public static function getLangaugeArr($table, $table_column, $item)
    {
        $languageArr = FresnsConfigsService::getLanguageStatus();
        $multilingual = $languageArr['languagesOption'];
        $nameArr = [];
        foreach ($multilingual as $v) {
            $input = [
                'table_name' => $table,
                'table_column' => $table_column,
                'table_id' => $item->id,
                'lang_tag' => $v['key'],
            ];
            $name = FresnsLanguages::where($input)->first();
            $v['lang_content'] = $name['lang_content'] ?? null;
            $nameArr[] = $v;
        }

        return $nameArr;
    }
}
