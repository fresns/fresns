<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateUserConfigRequest;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Config;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Language;
use App\Models\Plugin;
use App\Models\Role;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'account_connect_services',
            'account_real_name_service',
            'multi_user_status',
            'multi_user_service',
            'multi_user_roles',
            'default_role',
            'default_avatar',
            'anonymous_avatar',
            'deactivate_avatar',
            'user_identifier',
            'user_uid_digit',
            'password_length',
            'password_strength',
            'username_min',
            'username_max',
            'username_edit',
            'nickname_min',
            'nickname_max',
            'nickname_edit',
            'nickname_unique',
            'bio_length',
            'bio_support_mention',
            'bio_support_link',
            'bio_support_hashtag',
            'connects',
            'extcredits1_state',
            'extcredits2_state',
            'extcredits3_state',
            'extcredits4_state',
            'extcredits5_state',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        $params = [];
        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $pluginScenes = [
            'connect',
            'realName',
            'multiUser',
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

        $langKeys = [
            'extcredits1_name',
            'extcredits1_unit',
            'extcredits2_name',
            'extcredits2_unit',
            'extcredits3_name',
            'extcredits3_unit',
            'extcredits4_name',
            'extcredits4_unit',
            'extcredits5_name',
            'extcredits5_unit',
        ];

        $langConfigs = Config::whereIn('item_key', $langKeys)
            ->with('languages')
            ->get();
        $langConfigs = $langConfigs->mapWithKeys(function ($config) {
            return [$config->item_key => $config];
        });

        $defaultLangParams = Language::ofConfig()
            ->whereIn('table_key', $langKeys)
            ->where('lang_tag', $this->defaultLanguage)
            ->pluck('lang_content', 'table_key');

        return view('FsView::systems.user', compact('params', 'pluginParams', 'configImageInfo', 'roles', 'langConfigs', 'defaultLangParams'));
    }

    public function update(UpdateUserConfigRequest $request)
    {
        if ($request->file('default_avatar_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'configs',
                'tableColumn' => 'item_value',
                'tableKey' => 'default_avatar',
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('default_avatar_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));
            $request->request->set('default_avatar', $fileId);
        } elseif ($request->get('default_avatar_url')) {
            $request->request->set('default_avatar', $request->get('default_avatar_url'));
        }

        if ($request->file('anonymous_avatar_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'configs',
                'tableColumn' => 'item_value',
                'tableKey' => 'anonymous_avatar',
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('anonymous_avatar_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));
            $request->request->set('anonymous_avatar', $fileId);
        } elseif ($request->get('anonymous_avatar_url')) {
            $request->request->set('anonymous_avatar', $request->get('anonymous_avatar_url'));
        }

        if ($request->file('deactivate_avatar_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'configs',
                'tableColumn' => 'item_value',
                'tableKey' => 'deactivate_avatar',
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('deactivate_avatar_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));
            $request->request->set('deactivate_avatar', $fileId);
        } elseif ($request->get('deactivate_avatar_url')) {
            $request->request->set('deactivate_avatar', $request->get('deactivate_avatar_url'));
        }

        $configKeys = [
            'account_real_name_service',
            'multi_user_status',
            'multi_user_service',
            'multi_user_roles',
            'default_role',
            'default_avatar',
            'anonymous_avatar',
            'deactivate_avatar',
            'user_identifier',
            'user_uid_digit',
            'password_length',
            'password_strength',
            'username_min',
            'username_max',
            'username_edit',
            'nickname_min',
            'nickname_max',
            'nickname_edit',
            'nickname_unique',
            'bio_length',
            'bio_support_mention',
            'bio_support_link',
            'bio_support_hashtag',
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

            $value = $request->$configKey;

            if ($configKey == 'multi_user_roles') {
                if (in_array(0, $request->$configKey)) {
                    $value = [];
                }
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
        $config = Config::where('item_key', 'account_connect_services')->first();
        $config->item_value = $services;
        $config->save();

        CacheHelper::forgetFresnsConfigs([
            'default_avatar',
            'anonymous_avatar',
            'deactivate_avatar',
        ]);

        return $this->updateSuccess();
    }

    public function updateExtcredits(Request $request)
    {
        $extcreditsId = $request->extcreditsId;
        if (empty($extcreditsId)) {
            return back()->with('failure', __('FsLang::tips.upgradeFailure'));
        }

        $inputState = "extcredits{$extcreditsId}_state";

        if ($request->has('extcredits_state')) {
            $state = Config::where('item_key', $inputState)->first();
            if (! $state) {
                $state = new Config;
                $state->item_key = $inputState;
                $state->item_value = $request->extcredits_state;
                $state->item_type = 'number';
                $state->is_multilingual = 0;
                $state->is_custom = 0;
                $state->is_api = 1;
            }
            $state->item_value = $request->extcredits_state;
            $state->save();
        }

        return $this->updateSuccess();
    }
}
