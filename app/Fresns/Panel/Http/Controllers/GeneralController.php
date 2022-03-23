<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateGeneralRequest;
use App\Models\Config;
use App\Models\Language;
use App\Models\Plugin;

class GeneralController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'site_domain',
            'site_name',
            'site_desc',
            'site_icon',
            'site_logo',
            'site_copyright',
            'site_copyright_years',
            'default_timezone',
            'site_mode',
            'site_public_close',
            'site_public_service',
            'site_register_email',
            'site_register_phone',
            'site_private_close',
            'site_private_service',
            'site_private_end',
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

        return view('FsView::systems.general', compact('params', 'langParams', 'defaultLangParams', 'registerPlugins', 'joinPlugins'));
    }

    public function update(UpdateGeneralRequest $request)
    {
        $configKeys = [
            'site_domain',
            'site_copyright',
            'site_icon',
            'site_logo',
            'site_copyright_years',
            'default_timezone',
            'site_mode',
            'site_public_close',
            'site_public_service',
            'site_register_email',
            'site_register_phone',
            'site_private_close',
            'site_private_service',
            'site_private_end',
            'site_email',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
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
