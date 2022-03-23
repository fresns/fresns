<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use App\Models\Plugin;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function imageShow()
    {
        // config keys
        $configKeys = [
            'image_service',
            'image_secret_id',
            'image_secret_key',
            'image_bucket_name',
            'image_bucket_area',
            'image_bucket_domain',
            'image_ext',
            'image_max_size',
            'image_url_status',
            'image_url_key',
            'image_url_expire',
            'image_thumb_config',
            'image_thumb_avatar',
            'image_thumb_ratio',
            'image_thumb_square',
            'image_thumb_big',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $pluginScenes = [
            'storage',
        ];

        $plugins = Plugin::all();

        $pluginParams = [];
        foreach ($pluginScenes as $scene) {
            $pluginParams[$scene] = $plugins->filter(function ($plugin) use ($scene) {
                return in_array($scene, $plugin->scene);
            });
        }

        return view('FsView::systems.storage-image', compact('params', 'pluginParams'));
    }

    public function imageUpdate(Request $request)
    {
        $configKeys = [
            'image_service',
            'image_secret_id',
            'image_secret_key',
            'image_bucket_name',
            'image_bucket_area',
            'image_bucket_domain',
            'image_ext',
            'image_max_size',
            'image_url_status',
            'image_url_key',
            'image_url_expire',
            'image_thumb_config',
            'image_thumb_avatar',
            'image_thumb_ratio',
            'image_thumb_square',
            'image_thumb_big',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
                continue;
            }

            $value = $request->$configKey;
            $config->item_value = $value;
            $config->save();
        }

        return $this->updateSuccess();
    }

    public function videoShow()
    {
        // config keys
        $configKeys = [
            'video_service',
            'video_secret_id',
            'video_secret_key',
            'video_bucket_name',
            'video_bucket_area',
            'video_bucket_domain',
            'video_ext',
            'video_max_size',
            'video_max_time',
            'video_url_status',
            'video_url_key',
            'video_url_expire',
            'video_transcode',
            'video_watermark',
            'video_screenshot',
            'video_gift',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $pluginScenes = [
            'storage',
        ];

        $plugins = Plugin::all();

        $pluginParams = [];
        foreach ($pluginScenes as $scene) {
            $pluginParams[$scene] = $plugins->filter(function ($plugin) use ($scene) {
                return in_array($scene, $plugin->scene);
            });
        }

        return view('FsView::systems.storage-video', compact('params', 'pluginParams'));
    }

    public function videoUpdate(Request $request)
    {
        $configKeys = [
            'video_service',
            'video_secret_id',
            'video_secret_key',
            'video_bucket_name',
            'video_bucket_area',
            'video_bucket_domain',
            'video_ext',
            'video_max_size',
            'video_max_time',
            'video_url_status',
            'video_url_key',
            'video_url_expire',
            'video_transcode',
            'video_watermark',
            'video_screenshot',
            'video_gift',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
            }

            $value = $request->$configKey;
            $config->item_value = $value;
            $config->save();
        }

        return $this->updateSuccess();
    }

    public function audioShow()
    {
        // config keys
        $configKeys = [
            'audio_service',
            'audio_secret_id',
            'audio_secret_key',
            'audio_bucket_name',
            'audio_bucket_area',
            'audio_bucket_domain',
            'audio_ext',
            'audio_max_size',
            'audio_max_time',
            'audio_url_status',
            'audio_url_key',
            'audio_url_expire',
            'audio_transcode',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $pluginScenes = [
            'storage',
        ];

        $plugins = Plugin::all();

        $pluginParams = [];
        foreach ($pluginScenes as $scene) {
            $pluginParams[$scene] = $plugins->filter(function ($plugin) use ($scene) {
                return in_array($scene, $plugin->scene);
            });
        }

        return view('FsView::systems.storage-audio', compact('params', 'pluginParams'));
    }

    public function audioUpdate(Request $request)
    {
        $configKeys = [
            'audio_service',
            'audio_secret_id',
            'audio_secret_key',
            'audio_bucket_name',
            'audio_bucket_area',
            'audio_bucket_domain',
            'audio_ext',
            'audio_max_size',
            'audio_max_time',
            'audio_url_status',
            'audio_url_key',
            'audio_url_expire',
            'audio_transcode',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
                $config = new Config();
                $config->item_key = $configKey;
                $config->item_type = 'number';
                $config->item_tag = 'storageAudios';
                $config->is_enable = 1;
                $config->is_restful = 1;
            }

            $value = $request->$configKey;
            $config->item_value = $value;
            $config->save();
        }

        return $this->updateSuccess();
    }

    public function docShow()
    {
        // config keys
        $configKeys = [
            'document_service',
            'document_secret_id',
            'document_secret_key',
            'document_bucket_name',
            'document_bucket_area',
            'document_bucket_domain',
            'document_ext',
            'document_max_size',
            'document_url_status',
            'document_url_key',
            'document_url_expire',
            'document_online_preview',
            'document_preview_ext',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();
        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }
        $pluginScenes = [
            'storage',
        ];

        $plugins = Plugin::all();

        $pluginParams = [];
        foreach ($pluginScenes as $scene) {
            $pluginParams[$scene] = $plugins->filter(function ($plugin) use ($scene) {
                return in_array($scene, $plugin->scene);
            });
        }

        return view('FsView::systems.storage-document', compact('params', 'pluginParams'));
    }

    public function docUpdate(Request $request)
    {
        $configKeys = [
            'document_service',
            'document_secret_id',
            'document_secret_key',
            'document_bucket_name',
            'document_bucket_area',
            'document_bucket_domain',
            'document_ext',
            'document_max_size',
            'document_url_status',
            'document_url_key',
            'document_url_expire',
            'document_online_preview',
            'document_preview_ext',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
            }

            $value = $request->$configKey;
            $config->item_value = $value;
            $config->save();
        }

        return $this->updateSuccess();
    }

    public function substitutionShow()
    {
        // config keys
        $configKeys = [
            'substitution_image',
            'substitution_video',
            'substitution_audio',
            'substitution_document',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        return view('FsView::systems.storage-substitution', compact('params'));
    }

    public function substitutionUpdate(Request $request)
    {
        $configKeys = [
            'substitution_image',
            'substitution_video',
            'substitution_audio',
            'substitution_document',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
            }

            $value = $request->$configKey;
            $config->item_value = $value;
            $config->save();
        }

        return $this->updateSuccess();
    }
}
