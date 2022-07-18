<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $configKeys = [
            'default_homepage',
            'menu_portal_name',
            'menu_portal_title',
            'menu_portal_keywords',
            'menu_portal_description',
            'menu_portal_status',
            'menu_user_name',
            'menu_user_title',
            'menu_user_keywords',
            'menu_user_description',
            'menu_user_status',
            'menu_user_query_state',
            'menu_user_query_config',
            'menu_group_name',
            'menu_group_title',
            'menu_group_keywords',
            'menu_group_description',
            'menu_group_type',
            'menu_group_status',
            'menu_group_query_state',
            'menu_group_query_config',
            'menu_hashtag_name',
            'menu_hashtag_title',
            'menu_hashtag_keywords',
            'menu_hashtag_description',
            'menu_hashtag_status',
            'menu_hashtag_query_state',
            'menu_hashtag_query_config',
            'menu_post_name',
            'menu_post_title',
            'menu_post_keywords',
            'menu_post_description',
            'menu_post_status',
            'menu_post_query_state',
            'menu_post_query_config',
            'menu_comment_name',
            'menu_comment_title',
            'menu_comment_keywords',
            'menu_comment_description',
            'menu_comment_status',
            'menu_comment_query_state',
            'menu_comment_query_config',
            'menu_user_list_name',
            'menu_user_list_title',
            'menu_user_list_keywords',
            'menu_user_list_description',
            'menu_user_list_status',
            'menu_user_list_query_state',
            'menu_user_list_query_config',
            'menu_group_list_name',
            'menu_group_list_title',
            'menu_group_list_keywords',
            'menu_group_list_description',
            'menu_group_list_status',
            'menu_group_list_query_state',
            'menu_group_list_query_config',
            'menu_hashtag_list_name',
            'menu_hashtag_list_title',
            'menu_hashtag_list_keywords',
            'menu_hashtag_list_description',
            'menu_hashtag_list_status',
            'menu_hashtag_list_query_state',
            'menu_hashtag_list_query_config',
            'menu_post_list_name',
            'menu_post_list_title',
            'menu_post_list_keywords',
            'menu_post_list_description',
            'menu_post_list_status',
            'menu_post_list_query_state',
            'menu_post_list_query_config',
            'menu_comment_list_name',
            'menu_comment_list_title',
            'menu_comment_list_keywords',
            'menu_comment_list_description',
            'menu_comment_list_status',
            'menu_comment_list_query_state',
            'menu_comment_list_query_config',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->with('languages')->get();

        $configs = $configs->mapWithKeys(function ($config) {
            return [$config->item_key => $config];
        });

        $pathKeys = [
            'website_portal_path',
            'website_user_path',
            'website_group_path',
            'website_hashtag_path',
            'website_post_path',
            'website_comment_path',
        ];
        $paths = Config::whereIn('item_key', $pathKeys)->pluck('item_value', 'item_key')->toArray();

        $menus = [
            'portal' => [
                'name' => __('FsLang::panel.portal'),
                'controller' => 'portal',
                'path' => $paths['website_portal_path'],
                'select' => true,
            ],
            'user' => [
                'name' => __('FsLang::panel.user'),
                'controller' => 'user',
                'path' => $paths['website_user_path'],
                'select' => true,
            ],
            'group' => [
                'name' => __('FsLang::panel.group'),
                'controller' => 'group',
                'path' => $paths['website_group_path'],
                'select' => true,
            ],
            'hashtag' => [
                'name' => __('FsLang::panel.hashtag'),
                'controller' => 'hashtag',
                'path' => $paths['website_hashtag_path'],
                'select' => true,
            ],
            'post' => [
                'name' => __('FsLang::panel.post'),
                'controller' => 'post',
                'path' => $paths['website_post_path'],
                'select' => true,
            ],
            'comment' => [
                'name' => __('FsLang::panel.comment'),
                'controller' => 'comment',
                'path' => $paths['website_comment_path'],
                'select' => true,
            ],
            'user_list' => [
                'name' => __('FsLang::panel.menu_user_list'),
                'path' => $paths['website_user_path'].'/list',
                'select' => false,
            ],
            'group_list' => [
                'name' => __('FsLang::panel.menu_group_list'),
                'path' => $paths['website_group_path'].'/list',
                'select' => false,
            ],
            'hashtag_list' => [
                'name' => __('FsLang::panel.menu_hashtag_list'),
                'path' => $paths['website_hashtag_path'].'/list',
                'select' => false,
            ],
            'post_list' => [
                'name' => __('FsLang::panel.menu_post_list'),
                'path' => $paths['website_post_path'].'/list',
                'select' => false,
            ],
            'comment_list' => [
                'name' => __('FsLang::panel.menu_comment_list'),
                'path' => $paths['website_comment_path'].'/list',
                'select' => false,
            ],
        ];

        return view('FsView::clients.menus', compact('menus', 'configs'));
    }

    public function update($key, Request $request)
    {
        $enableKey = 'menu_'.$key.'_status';
        $typeKey = 'menu_'.$key.'_type';
        $queryStateKey = 'menu_'.$key.'_query_state';
        $queryConfigKey = 'menu_'.$key.'_query_config';

        if ($request->has('is_enable')) {
            $status = Config::where('item_key', $enableKey)->first();
            if (! $status) {
                $status = new Config;
                $status->item_key = $enableKey;
                $status->item_type = 'boolean';
                $status->item_tag = 'menus';
            }
            $status->item_value = $request->is_enable;
            $status->save();
        }

        if ($key == 'group' && $request->has('index_type')) {
            $indexType = Config::where('item_key', $typeKey)->first();
            if (! $indexType) {
                $indexType = new Config;
                $indexType->item_key = $typeKey;
                $indexType->item_type = 'string';
                $indexType->item_tag = 'menus';
            }

            $indexType->item_value = $request->index_type;
            $indexType->save();
        }

        if ($key != 'portal' && $request->has('query_state')) {
            $queryState = Config::where('item_key', $queryStateKey)->first();
            if (! $queryState) {
                $queryState = new Config;
                $queryState->item_key = $queryStateKey;
                $queryState->item_type = 'number';
                $queryState->item_tag = 'menus';
            }

            $queryState->item_value = $request->query_state;
            $queryState->save();
        }

        if ($key != 'portal' && $request->has('query_config')) {
            $queryConfig = Config::where('item_key', $queryConfigKey)->first();
            if (! $queryConfig) {
                $queryConfig = new Config;
                $queryConfig->item_key = $queryConfigKey;
                $queryConfig->item_type = 'string';
                $queryConfig->item_tag = 'menus';
            }

            $queryConfig->item_value = $request->query_config;
            $queryConfig->save();
        }

        return $this->updateSuccess();
    }
}
