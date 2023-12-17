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
use App\Helpers\StrHelper;
use App\Models\Config;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Plugin;
use App\Models\Role;

class UserController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'user_name',
            'user_uid_name',
            'user_username_name',
            'user_nickname_name',
            'user_role_name',
            'user_bio_name',
            'default_role',
            'default_avatar',
            'anonymous_avatar',
            'deactivate_avatar',
            'user_identifier',
            'user_uid_digit',
            'profile_default_homepage',
            'username_min',
            'username_max',
            'username_edit',
            'nickname_min',
            'nickname_max',
            'nickname_edit',
            'nickname_unique',
            'user_ban_names',
            'bio_length',
            'bio_support_mention',
            'bio_support_link',
            'bio_support_hashtag',
            'extcredits1_name',
            'extcredits1_unit',
            'extcredits1_state',
            'extcredits2_name',
            'extcredits2_unit',
            'extcredits2_state',
            'extcredits3_name',
            'extcredits3_unit',
            'extcredits3_state',
            'extcredits4_name',
            'extcredits4_unit',
            'extcredits4_state',
            'extcredits5_name',
            'extcredits5_unit',
            'extcredits5_state',
            'conversation_status',
            'conversation_files',
            'conversation_file_upload_type',
            'image_service',
            'video_service',
            'audio_service',
            'document_service',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        $params = [];
        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $params['user_ban_names'] = join(PHP_EOL, $params['user_ban_names']);

        $configImageInfo['defaultAvatarUrl'] = ConfigHelper::fresnsConfigFileUrlByItemKey('default_avatar');
        $configImageInfo['defaultAvatarType'] = ConfigHelper::fresnsConfigFileValueTypeByItemKey('default_avatar');
        $configImageInfo['anonymousAvatarUrl'] = ConfigHelper::fresnsConfigFileUrlByItemKey('anonymous_avatar');
        $configImageInfo['anonymousAvatarType'] = ConfigHelper::fresnsConfigFileValueTypeByItemKey('anonymous_avatar');
        $configImageInfo['deactivateAvatarUrl'] = ConfigHelper::fresnsConfigFileUrlByItemKey('deactivate_avatar');
        $configImageInfo['deactivateAvatarType'] = ConfigHelper::fresnsConfigFileValueTypeByItemKey('deactivate_avatar');
        $configImageInfo[] = $configImageInfo;

        $roles = Role::all();

        // language keys
        $langKeys = [
            'user_name',
            'user_uid_name',
            'user_username_name',
            'user_nickname_name',
            'user_role_name',
            'user_bio_name',
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

        $defaultLangParams = [];
        foreach ($langKeys as $langKey) {
            $defaultLangParams[$langKey] = StrHelper::languageContent($params[$langKey]);
        }

        $plugins = Plugin::all();
        $imageService = $plugins->where('fskey', $params['image_service'])->first();
        $videoService = $plugins->where('fskey', $params['video_service'])->first();
        $audioService = $plugins->where('fskey', $params['audio_service'])->first();
        $documentService = $plugins->where('fskey', $params['document_service'])->first();
        $pluginPageUpload = [
            'image' => $imageService?->access_path ? true : false,
            'video' => $videoService?->access_path ? true : false,
            'audio' => $audioService?->access_path ? true : false,
            'document' => $documentService?->access_path ? true : false,
        ];

        return view('FsView::operations.user', compact('params', 'configImageInfo', 'roles', 'defaultLangParams', 'pluginPageUpload'));
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
            'default_role',
            'default_avatar',
            'anonymous_avatar',
            'deactivate_avatar',
            'user_identifier',
            'user_uid_digit',
            'profile_default_homepage',
            'username_min',
            'username_max',
            'username_edit',
            'nickname_min',
            'nickname_max',
            'nickname_edit',
            'nickname_unique',
            'user_ban_names',
            'bio_length',
            'bio_support_mention',
            'bio_support_link',
            'bio_support_hashtag',
            'conversation_status',
            'conversation_files',
            'conversation_file_upload_type',
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
            if ($configKey == 'user_ban_names') {
                $value = explode("\r\n", $request->user_ban_names);
            }

            $config->item_value = $value;
            $config->save();
        }

        CacheHelper::forgetFresnsConfigs([
            'default_avatar',
            'anonymous_avatar',
            'deactivate_avatar',
        ]);

        return $this->updateSuccess();
    }
}
