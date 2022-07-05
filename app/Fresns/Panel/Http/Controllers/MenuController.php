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
            'menu_user_config',
            'menu_user_status',
            'menu_group_name',
            'menu_group_title',
            'menu_group_keywords',
            'menu_group_description',
            'menu_group_config',
            'menu_group_status',
            'menu_hashtag_name',
            'menu_hashtag_title',
            'menu_hashtag_keywords',
            'menu_hashtag_description',
            'menu_hashtag_config',
            'menu_hashtag_status',
            'menu_post_name',
            'menu_post_title',
            'menu_post_keywords',
            'menu_post_description',
            'menu_post_config',
            'menu_post_status',
            'menu_comment_name',
            'menu_comment_title',
            'menu_comment_keywords',
            'menu_comment_description',
            'menu_comment_config',
            'menu_comment_status',
            'menu_user_list_name',
            'menu_user_list_title',
            'menu_user_list_keywords',
            'menu_user_list_description',
            'menu_user_list_config',
            'menu_user_list_status',
            'menu_group_list_name',
            'menu_group_list_title',
            'menu_group_list_keywords',
            'menu_group_list_description',
            'menu_group_list_config',
            'menu_group_list_status',
            'menu_hashtag_list_name',
            'menu_hashtag_list_title',
            'menu_hashtag_list_keywords',
            'menu_hashtag_list_description',
            'menu_hashtag_list_config',
            'menu_hashtag_list_status',
            'menu_post_list_name',
            'menu_post_list_title',
            'menu_post_list_keywords',
            'menu_post_list_description',
            'menu_post_list_config',
            'menu_post_list_status',
            'menu_comment_list_name',
            'menu_comment_list_title',
            'menu_comment_list_keywords',
            'menu_comment_list_description',
            'menu_comment_list_config',
            'menu_comment_list_status',
        ];

        $langKeys = $configKeys;

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
        $configKey = 'menu_'.$key.'_config';
        $enableKey = 'menu_'.$key.'_status';

        if ($key != 'portal' && $request->has('config')) {
            $config = Config::where('item_key', $configKey)->first();
            if (! $config) {
                $config = new Config;
                $config->item_key = $enableKey;
                $config->item_type = 'string';
                $config->item_tag = 'menus';
            }

            $config->item_value = $request->config;
            $config->save();
        }

        if ($request->has('is_enable')) {
            $config = Config::where('item_key', $enableKey)->first();
            if (! $config) {
                $config = new Config;
                $config->item_key = $enableKey;
                $config->item_type = 'boolean';
                $config->item_tag = 'menus';
            }
            $config->item_value = $request->is_enable;
            $config->save();
        }

        return $this->updateSuccess();
    }
}
