<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use App\Models\SessionKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebsiteController extends Controller
{
    public function index()
    {
        // config keys
        $configKeys = [
            'engine_cookie_prefix',
            'engine_api_type',
            'engine_key_id',
            'engine_api_host',
            'engine_api_app_id',
            'engine_api_app_secret',
            'website_stat_code',
            'website_stat_position',
            'website_status',
            'website_number',
            'website_proportion',
            'site_china_mode',
            'china_icp_filing',
            'china_icp_license',
            'china_psb_filing',
            'china_broadcasting_license',
        ];
        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        // $pluginScenes = [
        //     'engine',
        // ];
        // $plugins = Plugin::all();
        // $pluginParams = [];
        // foreach ($pluginScenes as $scene) {
        //     $pluginParams[$scene] = $plugins->filter(function ($plugin) use ($scene) {
        //         return in_array($scene, $plugin->scene);
        //     });
        // }

        $keyData = SessionKey::where('type', 1)->whereIn('platform_id', [2, 3, 4])->isEnable()->get();
        $keys = [];
        foreach ($keyData as $key) {
            $item['id'] = $key->id;
            $item['name'] = $key->name;
            $item['appId'] = $key->app_id;

            $keys[] = $item;
        }

        // $engineSettingsPath = Plugin::where('unikey', $params['engine_service'])->value('settings_path');

        // $FresnsEngine = Config::where('item_key', 'FresnsEngine')->first()?->item_value;

        // $themeUnikey['desktop'] = Config::where('item_key', $params['engine_service'].'_Desktop')->value('item_value');
        // $themeUnikey['mobile'] = Config::where('item_key', $params['engine_service'].'_Mobile')->value('item_value');

        // $themeName['desktop'] = Plugin::where('unikey', $themeUnikey['desktop'])->value('name');
        // $themeName['mobile'] = Plugin::where('unikey', $themeUnikey['mobile'])->value('name');

        return view('FsView::clients.website', compact('keys', 'params'));
    }

    public function update(Request $request)
    {
        // config keys
        $configKeys = [
            'engine_cookie_prefix',
            'engine_api_type',
            'engine_key_id',
            'engine_api_host',
            'engine_api_app_id',
            'engine_api_app_secret',
            'website_stat_code',
            'website_stat_position',
            'website_status',
            'website_number',
            'website_proportion',
            'site_china_mode',
            'china_icp_filing',
            'china_icp_license',
            'china_psb_filing',
            'china_broadcasting_license',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
                continue;
            }

            if (! $request->has($configKey)) {
                $config->setDefaultValue();
                $config->save();
                continue;
            }

            if ($configKey == 'engine_api_host' && $request->get('engine_api_host')) {
                $url = Str::of($request->get('engine_api_host'))->trim();
                $url = Str::of($url)->rtrim('/');

                $request->$configKey = $url;
            }

            $config->item_value = $request->$configKey;
            $config->save();
        }

        return $this->updateSuccess();
    }

    // path index
    public function pathIndex()
    {
        // config keys
        $configKeys = [
            'site_url',
            'website_portal_path',
            'website_user_path',
            'website_group_path',
            'website_hashtag_path',
            'website_post_path',
            'website_comment_path',
            'website_user_detail_path',
            'website_group_detail_path',
            'website_hashtag_detail_path',
            'website_post_detail_path',
            'website_comment_detail_path',
        ];
        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $siteUrl = $params['site_url'];
        $siteUrl = rtrim($siteUrl, '/');

        return view('FsView::clients.paths', compact('params', 'siteUrl'));
    }

    // path update
    public function pathUpdate(Request $request)
    {
        // config keys
        $configKeys = [
            'website_portal_path',
            'website_user_path',
            'website_group_path',
            'website_hashtag_path',
            'website_post_path',
            'website_comment_path',
            'website_user_detail_path',
            'website_group_detail_path',
            'website_hashtag_detail_path',
            'website_post_detail_path',
            'website_comment_detail_path',
        ];

        // system reserved
        $pathKeys = [
            'fresns',
            'location',
            'notifications',
            'conversations',
            'messages',
            'drafts',
        ];

        $rules = [];
        $messages = [];
        foreach ($configKeys as $key) {
            $rules[$key] = ['required', 'regex:/^[a-z]+$/i'];
            $messages["$key.required"] = __('FsLang::tips.website_path_empty_error');
            $messages["$key.regex"] = __('FsLang::tips.website_path_format_error');

            if (in_array($request->{$key}, $pathKeys)) {
                return back()->with('failure', sprintf(__('FsLang::tips.website_path_reserved_error').' -> ', $key));
            }
        }

        $data = $request->only($configKeys);

        $validate = validator($data, $rules, $messages);

        if (! $validate->passes()) {
            return back()->with('failure', $validate->errors()->first());
        }

        $data = array_unique($data);

        if (count($configKeys) !== count($data)) {
            return back()->with('failure', __('FsLang::tips.website_path_unique_error'));
        }

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
                continue;
            }

            if (! $request->has($configKey)) {
                $config->setDefaultValue();
                $config->save();
                continue;
            }

            $config->item_value = $request->$configKey;
            $config->save();
        }

        return $this->updateSuccess();
    }
}
