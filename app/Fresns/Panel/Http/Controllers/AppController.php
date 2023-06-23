<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\ConfigHelper;
use App\Models\Config;
use App\Models\Plugin;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function index()
    {
        // config keys
        $configKeys = [
            'ios_notifications_service',
            'android_notifications_service',
            'wechat_notifications_service',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $pluginScenes = [
            'appNotifications',
        ];

        $plugins = Plugin::all();

        $pluginParams = [];
        foreach ($pluginScenes as $scene) {
            $pluginParams[$scene] = $plugins->filter(function ($plugin) use ($scene) {
                return in_array($scene, $plugin->scene);
            });
        }

        return view('FsView::clients.app', compact('params', 'pluginParams'));
    }

    public function update(Request $request)
    {
        $configKeys = [
            'ios_notifications_service',
            'android_notifications_service',
            'wechat_notifications_service',
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

    public function statusIndex()
    {
        $statusJson = [
            'name' => 'Fresns',
            'activate' => true,
            'deactivateDescription' => [
                'default' => '',
            ],
        ];

        $statusJsonFile = public_path('status.json');

        if (file_exists($statusJsonFile)) {
            $statusJson = json_decode(file_get_contents($statusJsonFile), true);
        }

        return view('FsView::clients.status', compact('statusJson'));
    }

    public function statusUpdate(Request $request)
    {
        $activate = (bool) $request->activate;

        $descriptionArr = [];
        if ($request->descriptionLangTag) {
            foreach ($request->descriptionLangTag as $key => $langTag) {
                $descriptionArr[$langTag] = $request->descriptionLangContent[$key] ?? '';
            }
        }

        $defaultLangTag = ConfigHelper::fresnsConfigDefaultLangTag();
        $descriptionArr['default'] = $descriptionArr[$defaultLangTag] ?? array_values($descriptionArr)[0] ?? '';

        $statusJson = [
            'name' => 'Fresns',
            'activate' => $activate,
            'deactivateDescription' => $descriptionArr,
        ];

        $statusJsonFile = public_path('status.json');

        $editContent = json_encode($statusJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        file_put_contents($statusJsonFile, $editContent);

        return $this->updateSuccess();
    }
}
