<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use App\Models\Plugin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'connects',
            'account_center_service',
            'account_center_path',
            'account_login_path',
            'account_register_path',
            'account_register_status',
            'account_register_service',
            'account_email_register',
            'account_phone_register',
            'account_email_login',
            'account_phone_login',
            'account_login_with_code',
            'account_login_or_register',
            'password_length',
            'password_strength',
            'account_connect_services',
            'account_kyc_service',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        $params = [];
        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $plugins = Plugin::all();
        $accountCenterPlugins = $plugins->filter(function ($plugin) {
            return in_array('accountCenter', $plugin->panel_usages);
        });
        $accountRegisterPlugins = $plugins->filter(function ($plugin) {
            return in_array('accountRegister', $plugin->panel_usages);
        });
        $accountConnectPlugins = $plugins->filter(function ($plugin) {
            return in_array('accountConnect', $plugin->panel_usages);
        });
        $accountKycPlugins = $plugins->filter(function ($plugin) {
            return in_array('accountKyc', $plugin->panel_usages);
        });

        return view('FsView::systems.account', compact('params', 'accountCenterPlugins', 'accountRegisterPlugins', 'accountConnectPlugins', 'accountKycPlugins'));
    }

    public function update(Request $request)
    {
        $configKeys = [
            'account_center_service',
            'account_center_path',
            'account_login_path',
            'account_register_path',
            'account_register_status',
            'account_register_service',
            'account_email_register',
            'account_phone_register',
            'account_email_login',
            'account_phone_login',
            'account_login_with_code',
            'account_login_or_register',
            'account_connect_services',
            'account_kyc_service',
            'password_length',
            'password_strength',
            'account_kyc_service',
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

            $value = $request->configKey;

            if (in_array($configKey, [
                'account_center_path',
                'account_login_path',
                'account_register_path',
            ])) {
                $value = Str::of($value)->trim();
                $value = Str::of($value)->trim('/');
            }

            $config->item_value = $value;
            $config->save();
        }

        $services = [];
        if ($request->connectId) {
            foreach ($request->connectId as $key => $id) {
                if (array_key_exists($key, $services)) {
                    continue;
                }
                $services[$id] = [
                    'order' => $request->connectOrder[$key] ?? 9,
                    'code' => $id,
                    'fskey' => $request->connectPlugin[$key] ?? '',
                ];
            }

            usort($services, function ($a, $b) {
                if ($a['order'] == 1) {
                    return -1;
                } elseif ($b['order'] == 1) {
                    return 1;
                } else {
                    return $a['order'] - $b['order'];
                }
            });
        }
        $connectConfig = Config::where('item_key', 'account_connect_services')->first();
        $connectConfig->item_value = $services;
        $connectConfig->save();

        return $this->updateSuccess();
    }
}
