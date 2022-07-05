<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdatePolicyRequest;
use App\Models\Config;
use App\Models\Language;

class PolicyController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'account_terms_status',
            'account_privacy_status',
            'account_cookie_status',
            'account_ip_location_status',
            'account_delete_status',
            'delete_account_type',
            'delete_account_todo',
        ];

        // language keys
        $langKeys = [
            'account_terms',
            'account_privacy',
            'account_cookie',
            'account_delete',
        ];
        $configs = Config::whereIn('item_key', $configKeys)->get();

        $languages = Language::ofConfig()->whereIn('table_key', $langKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $langParams = [];
        foreach ($langKeys as $langKey) {
            $langParams[$langKey] = $languages->where('table_key', $langKey)->pluck('lang_content', 'lang_tag')->toArray();
        }

        return view('FsView::systems.policy', compact('params', 'langParams'));
    }

    public function update(UpdatePolicyRequest $request)
    {
        $configKeys = [
            'account_terms_status',
            'account_privacy_status',
            'account_cookie_status',
            'account_ip_location_status',
            'account_delete_status',
            'delete_account_type',
            'delete_account_todo',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
            }

            $config->item_value = $request->$configKey;
            $config->save();
        }

        return $this->updateSuccess();
    }
}
