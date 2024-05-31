<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateAccountRequest;
use App\Helpers\PrimaryHelper;
use App\Models\App;
use App\Models\Config;
use App\Models\File;
use App\Models\FileUsage;

class AccountController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'connects',
            'account_center_service',
            'account_center_captcha',
            'account_register_service',
            'account_register_status',
            'account_email_register',
            'account_phone_register',
            'account_login_service',
            'account_email_login',
            'account_phone_login',
            'account_login_with_code',
            'account_login_or_register',
            'password_length',
            'password_strength',
            'account_age_verification',
            'account_age_min_required',
            'account_connect_services',
            'account_kyc_service',
            'account_users_service',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        $params = [];
        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $plugins = App::whereIn('type', [App::TYPE_PLUGIN, App::TYPE_APP_REMOTE])->get();
        $accountCenterPlugins = $plugins->filter(function ($plugin) {
            return in_array('accountCenter', $plugin->panel_usages);
        });
        $accountRegisterPlugins = $plugins->filter(function ($plugin) {
            return in_array('accountRegister', $plugin->panel_usages);
        });
        $accountLoginPlugins = $plugins->filter(function ($plugin) {
            return in_array('accountLogin', $plugin->panel_usages);
        });
        $accountConnectPlugins = $plugins->filter(function ($plugin) {
            return in_array('accountConnect', $plugin->panel_usages);
        });
        $accountKycPlugins = $plugins->filter(function ($plugin) {
            return in_array('accountKyc', $plugin->panel_usages);
        });
        $accountUsersPlugins = $plugins->filter(function ($plugin) {
            return in_array('accountUsers', $plugin->panel_usages);
        });

        return view('FsView::systems.account', compact('params', 'accountCenterPlugins', 'accountRegisterPlugins', 'accountLoginPlugins', 'accountConnectPlugins', 'accountKycPlugins', 'accountUsersPlugins'));
    }

    public function update(UpdateAccountRequest $request)
    {
        $configKeys = [
            'account_center_service',
            'account_center_captcha',
            'account_register_service',
            'account_register_status',
            'account_email_register',
            'account_phone_register',
            'account_login_service',
            'account_email_login',
            'account_phone_login',
            'account_login_with_code',
            'account_login_or_register',
            'account_kyc_service',
            'password_length',
            'password_strength',
            'account_age_verification',
            'account_age_min_required',
            'account_kyc_service',
            'account_users_service',
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

            $config->item_value = $request->$configKey;
            $config->save();
        }

        $services = [];
        if ($request->connectId) {
            foreach ($request->connectId as $key => $id) {
                try {
                    $namesString = $request->connectNames[$key];

                    $connectNameArr = json_decode($namesString, true);
                } catch (\Exception $e) {
                    $connectNameArr = [];
                }

                try {
                    $icon = null;
                    if ($request->hasFile("connectIconFile.{$key}")) {
                        $wordBody = [
                            'usageType' => FileUsage::TYPE_SYSTEM,
                            'platformId' => 4,
                            'tableName' => 'configs',
                            'tableColumn' => 'item_value',
                            'tableKey' => 'account_connect_services',
                            'type' => File::TYPE_IMAGE,
                            'file' => $request->file('connectIconFile')[$key],
                        ];

                        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);

                        $icon = PrimaryHelper::fresnsPrimaryId('file', $fresnsResp->getData('fid'));
                    } elseif ($request->connectIconUrl[$key]) {
                        $icon = $request->connectIconUrl[$key];
                    }
                } catch (\Exception $e) {
                    $icon = null;
                }

                $services[$id] = [
                    'code' => $id,
                    'icon' => $icon,
                    'name' => $connectNameArr,
                    'fskey' => $request->connectPlugin[$key] ?? '',
                    'order' => $request->connectOrder[$key] ?? 9,
                ];
            }

            usort($services, function ($a, $b) {
                $orderA = $a['order'] === '' ? 9 : (int) $a['order'];
                $orderB = $b['order'] === '' ? 9 : (int) $b['order'];

                return $orderA <=> $orderB;
            });
        }
        $connectConfig = Config::where('item_key', 'account_connect_services')->first();
        $connectConfig->item_value = $services;
        $connectConfig->save();

        return $this->updateSuccess();
    }
}
