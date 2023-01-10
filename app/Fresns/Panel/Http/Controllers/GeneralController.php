<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateGeneralRequest;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Config;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Language;
use App\Models\Plugin;
use Illuminate\Support\Str;

class GeneralController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'site_url',
            'site_name',
            'site_desc',
            'site_icon',
            'site_logo',
            'site_copyright',
            'site_copyright_years',
            'default_timezone',
            'site_mode',
            'site_public_status',
            'site_public_service',
            'site_register_email',
            'site_register_phone',
            'site_login_or_register',
            'site_private_status',
            'site_private_service',
            'site_private_end_after',
            'site_email',
            'utc',
        ];

        // language keys
        $langKeys = [
            'site_name',
            'site_desc',
        ];
        $configs = Config::whereIn('item_key', $configKeys)->get();

        $languages = Language::ofConfig()->whereIn('table_key', $langKeys)->get();

        $params = [];
        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $langParams = [];
        $defaultLangParams = [];
        foreach ($langKeys as $langKey) {
            $langParams[$langKey] = $languages->where('table_key', $langKey)->pluck('lang_content', 'lang_tag')->toArray();
            $defaultLangParams[$langKey] = $languages->where('table_key', $langKey)->where('lang_tag', $this->defaultLanguage)->first()['lang_content'] ?? '';
        }

        $plugins = Plugin::all();
        $registerPlugins = $plugins->filter(function ($plugin) {
            return in_array('register', $plugin->scene);
        });
        $joinPlugins = $plugins->filter(function ($plugin) {
            return in_array('join', $plugin->scene);
        });

        $configImageInfo['iconUrl'] = ConfigHelper::fresnsConfigFileUrlByItemKey('site_icon');
        $configImageInfo['iconType'] = ConfigHelper::fresnsConfigFileValueTypeByItemKey('site_icon');
        $configImageInfo['logoUrl'] = ConfigHelper::fresnsConfigFileUrlByItemKey('site_logo');
        $configImageInfo['logoType'] = ConfigHelper::fresnsConfigFileValueTypeByItemKey('site_logo');
        $configImageInfo[] = $configImageInfo;

        return view('FsView::systems.general', compact('params', 'configImageInfo', 'langParams', 'defaultLangParams', 'registerPlugins', 'joinPlugins'));
    }

    public function update(UpdateGeneralRequest $request)
    {
        if ($request->file('site_icon_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'configs',
                'tableColumn' => 'item_value',
                'tableKey' => 'site_icon',
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('site_icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));
            $request->request->set('site_icon', $fileId);
        } elseif ($request->get('site_icon_url')) {
            $request->request->set('site_icon', $request->get('site_icon_url'));
        }

        if ($request->file('site_logo_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'configs',
                'tableColumn' => 'item_value',
                'tableKey' => 'site_logo',
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('site_logo_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));
            $request->request->set('site_logo', $fileId);
        } elseif ($request->get('site_logo_url')) {
            $request->request->set('site_logo', $request->get('site_logo_url'));
        }

        $configKeys = [
            'site_url',
            'site_copyright',
            'site_icon',
            'site_logo',
            'site_copyright_years',
            'default_timezone',
            'site_mode',
            'site_public_status',
            'site_public_service',
            'site_register_email',
            'site_register_phone',
            'site_login_or_register',
            'site_private_status',
            'site_private_service',
            'site_private_end_after',
            'site_email',
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

            if ($configKey == 'site_url' && $request->get('site_url')) {
                $url = Str::of($request->get('site_url'))->trim();
                $url = Str::of($url)->rtrim('/');

                $request->$configKey = $url;
            }

            $config->item_value = $request->$configKey;
            $config->save();
        }

        CacheHelper::forgetFresnsMultilingual('fresns_config_site_icon');
        CacheHelper::forgetFresnsMultilingual('fresns_config_site_logo');
        CacheHelper::forgetFresnsKey('fresns_default_timezone');

        return $this->updateSuccess();
    }
}
