<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Plugin;

class PluginHelper
{
    // Get the plugin host.
    public static function fresnsPluginHostByFskey(string $fskey): ?string
    {
        $cacheKey = "fresns_plugin_host_{$fskey}";
        $cacheTags = ['fresnsExtensions', 'fresnsConfigs'];

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $pluginHost = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($pluginHost)) {
            $pluginHost = Plugin::where('fskey', $fskey)->value('plugin_host');

            CacheHelper::put($pluginHost, $cacheKey, $cacheTags);
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
        $cacheTags = ['fresnsExtensions', 'fresnsConfigs'];

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $pluginUrl = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($pluginUrl)) {
            $plugin = Plugin::where('fskey', $fskey)->first();

            $link = null;
            if ($plugin) {
                $url = empty($plugin->plugin_host) ? config('app.url') : $plugin->plugin_host;

                $link = StrHelper::qualifyUrl($plugin->access_path, $url);
            }

            $pluginUrl = $link;

            CacheHelper::put($pluginUrl, $cacheKey, $cacheTags);
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

    // get plugin version
    public static function fresnsPluginVersionByFskey(string $fskey): ?string
    {
        $cacheKey = "fresns_plugin_version_{$fskey}";
        $cacheTags = ['fresnsExtensions', 'fresnsConfigs'];

        $version = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($version)) {
            $version = Plugin::where('fskey', $fskey)->value('version');

            if (empty($version)) {
                return null;
            }

            CacheHelper::put($version, $cacheKey, $cacheTags);
        }

        return $version;
    }

    // get plugin upgrade code
    public static function fresnsPluginUpgradeCodeByFskey(string $fskey): ?string
    {
        $upgradeCode = Plugin::where('fskey', $fskey)->value('upgrade_code');

        if (empty($upgradeCode)) {
            return null;
        }

        return $upgradeCode;
    }

    // handle plugin data rating
    public static function pluginDataRatingHandle(string $key, ?array $dataSources = null, ?string $langTag = null): array
    {
        if (empty($dataSources)) {
            return [];
        }

        $pluginRatingArr = $dataSources[$key]['pluginRating'] ?? [];

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $pluginRating = [];
        foreach ($pluginRatingArr as $arr) {
            $item['id'] = $arr['id'];
            $item['title'] = collect($arr['intro'])->where('langTag', $langTag)->first()['title'] ?? null;
            $item['description'] = collect($arr['intro'])->where('langTag', $langTag)->first()['description'] ?? null;

            $pluginRating[] = $item;
        }

        return $pluginRating;
    }

    // get subscribe items
    public static function fresnsPluginSubscribeItems(?int $type = null): array
    {
        $subscribeItems = ConfigHelper::fresnsConfigByItemKey('subscribe_items') ?? [];

        if (empty($subscribeItems)) {
            return [];
        }

        if (empty($type)) {
            return $subscribeItems;
        }

        $filtered = array_filter($subscribeItems, function ($item) use ($type) {
            return $item['type'] == $type;
        });

        return array_values($filtered);
    }
}
