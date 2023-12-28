<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'account_terms_status',
            'account_privacy_status',
            'account_cookie_status',
            'account_delete_status',
            'delete_account_type',
            'delete_account_todo',
            'account_terms_policy',
            'account_privacy_policy',
            'account_cookie_policy',
            'account_delete_policy',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        $params = [];
        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        return view('FsView::systems.policy', compact('params'));
    }

    public function update(Request $request)
    {
        $configKeys = [
            'account_terms_status',
            'account_privacy_status',
            'account_cookie_status',
            'account_delete_status',
            'delete_account_type',
            'delete_account_todo',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
                continue;
            }

            $config->item_value = $request->$configKey;
            $config->save();
        }

        return $this->updateSuccess();
    }
}
