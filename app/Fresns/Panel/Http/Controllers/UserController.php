<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateUserConfigRequest;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Config;
use App\Models\Plugin;
use App\Models\Role;

class UserController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'account_connect_services',
            'account_prove_service',
            'user_multiple',
            'multi_user_service',
            'multi_user_roles',
            'default_role',
            'default_avatar',
            'anonymous_avatar',
            'deactivate_avatar',
            'password_length',
            'password_strength',
            'username_min',
            'username_max',
            'username_edit',
            'nickname_edit',
            'connects',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        $params = [];
        foreach ($configs as $config) {
            $value = $config->item_value;
            if ($config->item_key == 'password_strength') {
                $value = explode(',', $value);
            }
            $params[$config->item_key] = $value;
        }

        $pluginScenes = [
            'connect',
            'prove',
            'multiple',
        ];

        $plugins = Plugin::all();

        $pluginParams = [];
        foreach ($pluginScenes as $scene) {
            $pluginParams[$scene] = $plugins->filter(function ($plugin) use ($scene) {
                return in_array($scene, $plugin->scene);
            });
        }

        $configImageInfo['defaultAvatarUrl'] = ConfigHelper::fresnsConfigFileUrlByItemKey('default_avatar');
        $configImageInfo['defaultAvatarType'] = ConfigHelper::fresnsConfigFileValueTypeByItemKey('default_avatar');
        $configImageInfo['anonymousAvatarUrl'] = ConfigHelper::fresnsConfigFileUrlByItemKey('anonymous_avatar');
        $configImageInfo['anonymousAvatarType'] = ConfigHelper::fresnsConfigFileValueTypeByItemKey('anonymous_avatar');
        $configImageInfo['deactivateAvatarUrl'] = ConfigHelper::fresnsConfigFileUrlByItemKey('deactivate_avatar');
        $configImageInfo['deactivateAvatarType'] = ConfigHelper::fresnsConfigFileValueTypeByItemKey('deactivate_avatar');
        $configImageInfo[] = $configImageInfo;

        $roles = Role::all();

        return view('FsView::systems.user', compact('params', 'pluginParams', 'configImageInfo', 'roles'));
    }

    public function update(UpdateUserConfigRequest $request)
    {
        if ($request->file('default_avatar_file')) {
            $wordBody = [
                'platform' => 4,
                'type' => 1,
                'tableType' => 2,
                'tableName' => 'configs',
                'tableColumn' => 'item_value',
                'tableKey' => 'default_avatar',
                'file' => $request->file('default_avatar_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->errorResponse();
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));
            $request->request->set('default_avatar', $fileId);
        } elseif ($request->get('default_avatar_url')) {
            $request->request->set('default_avatar', $request->get('default_avatar_url'));
        }

        if ($request->file('anonymous_avatar_file')) {
            $wordBody = [
                'platform' => 4,
                'type' => 1,
                'tableType' => 2,
                'tableName' => 'configs',
                'tableColumn' => 'item_value',
                'tableKey' => 'anonymous_avatar',
                'file' => $request->file('anonymous_avatar_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->errorResponse();
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));
            $request->request->set('anonymous_avatar', $fileId);
        } elseif ($request->get('anonymous_avatar_url')) {
            $request->request->set('anonymous_avatar', $request->get('anonymous_avatar_url'));
        }

        if ($request->file('deactivate_avatar_file')) {
            $wordBody = [
                'platform' => 4,
                'type' => 1,
                'tableType' => 2,
                'tableName' => 'configs',
                'tableColumn' => 'item_value',
                'tableKey' => 'deactivate_avatar',
                'file' => $request->file('deactivate_avatar_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->errorResponse();
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));
            $request->request->set('deactivate_avatar', $fileId);
        } elseif ($request->get('deactivate_avatar_url')) {
            $request->request->set('deactivate_avatar', $request->get('deactivate_avatar_url'));
        }

        $configKeys = [
            'account_prove_service',
            'user_multiple',
            'multi_user_service',
            'multi_user_roles',
            'default_role',
            'default_avatar',
            'anonymous_avatar',
            'deactivate_avatar',
            'password_length',
            'password_strength',
            'username_min',
            'username_max',
            'username_edit',
            'nickname_edit',
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

            $value = $request->$configKey;

            if ($configKey == 'password_strength') {
                $value = join(',', $request->$configKey);
            }

            if ($configKey == 'multi_user_roles') {
                if (in_array(0, $request->$configKey)) {
                    $value = [];
                }
            }

            $config->item_value = $value;
            $config->save();
        }

        $services = [];
        if ($request->connects) {
            $services = [];
            foreach ($request->connects as $key => $connect) {
                if (array_key_exists($key, $services)) {
                    continue;
                }
                $services[$connect] = [
                    'code' => $connect,
                    'unikey' => $request->connect_plugins[$key] ?? '',
                ];
            }

            $services = array_values($services);
        }
        $config = Config::where('item_key', 'account_connect_services')->first();
        $config->item_value = $services;
        $config->save();

        return $this->updateSuccess();
    }
}
