<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\App;
use App\Models\AppCallback;

class PluginHelper
{
    // Get the plugin host.
    public static function fresnsPluginHostByFskey(string $fskey): ?string
    {
        $cacheKey = "fresns_plugin_host_{$fskey}";
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $pluginHost = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($pluginHost)) {
            $pluginHost = App::where('fskey', $fskey)->value('app_host');

            CacheHelper::put($pluginHost, $cacheKey, $cacheTag);
        }

        return $pluginHost;
    }

    // Get the plugin access url
    public static function fresnsPluginUrlByFskey(?string $fskey = null): ?string
    {
        if (empty($fskey)) {
            return null;
        }

        $cacheKey = "fresns_plugin_url_{$fskey}";
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $pluginUrl = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($pluginUrl)) {
            $plugin = App::where('fskey', $fskey)->first();

            $link = null;
            if ($plugin) {
                $host = empty($plugin->plugin_host) ? config('app.url') : $plugin->plugin_host;

                $link = StrHelper::qualifyUrl($plugin->access_path, $host);
            }

            $pluginUrl = $link;

            CacheHelper::put($pluginUrl, $cacheKey, $cacheTag);
        }

        return $pluginUrl ?? null;
    }

    // Get the url of the plugin that has replaced the custom parameters
    public static function fresnsPluginUsageUrl(string $fskey, ?string $parameter = null): ?string
    {
        $url = PluginHelper::fresnsPluginUrlByFskey($fskey);

        if (empty($parameter) || empty($url)) {
            return $url;
        }

        return str_replace('{parameter}', $parameter, $url);
    }

    // get plugin callback
    public static function fresnsPluginCallback(string $ulid, ?string $fskey = null): array
    {
        $callbackArr = [
            'code' => 0,
            'data' => [],
        ];

        if ($fskey) {
            $plugin = App::where('fskey', $fskey)->first();

            if (empty($plugin)) {
                $callbackArr['code'] = 32101;

                return $callbackArr;
            }

            if (! $plugin->is_enabled) {
                $callbackArr['code'] = 32102;

                return $callbackArr;
            }
        }

        $callback = AppCallback::where('ulid', $ulid)->first();

        if (empty($callback)) {
            $callbackArr['code'] = 32303;

            return $callbackArr;
        }

        if ($callback->is_used) {
            $callbackArr['code'] = 32204;

            return $callbackArr;
        }

        if (empty($callback->content)) {
            $callbackArr['code'] = 32206;

            return $callbackArr;
        }

        $timeDifference = time() - strtotime($callback->created_at);
        // 30 minutes
        if ($timeDifference > 1800) {
            $callbackArr['code'] = 32203;

            return $callbackArr;
        }

        $callback->is_used = 1;
        $callback->used_app_fskey = $fskey;
        $callback->save();

        $callbackArr['data'] = $callback->content;

        return $callbackArr;
    }

    // get plugin version
    public static function fresnsPluginVersionByFskey(?string $fskey = null): ?string
    {
        if (empty($fskey)) {
            return null;
        }

        $cacheKey = "fresns_plugin_version_{$fskey}";
        $cacheTag = 'fresnsConfigs';

        $version = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($version)) {
            $version = App::where('fskey', $fskey)->value('version');

            if (empty($version)) {
                return null;
            }

            CacheHelper::put($version, $cacheKey, $cacheTag);
        }

        return $version;
    }

    // get plugin upgrade code
    public static function fresnsPluginUpgradeCodeByFskey(string $fskey): ?string
    {
        $upgradeCode = App::where('fskey', $fskey)->value('upgrade_code');

        if (empty($upgradeCode)) {
            return null;
        }

        return $upgradeCode;
    }
}
