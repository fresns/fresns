<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Plugin;

class PluginHelper
{
    /**
     * Get the plugin access url.
     *
     * @param  string  $unikey
     * @return string
     */
    public static function fresnsPluginUrlByUnikey(?string $unikey = null)
    {
        if (empty($unikey)) {
            return null;
        }

        $cacheKey = "fresns_plugin_url_{$unikey}";
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $pluginUrl = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($pluginUrl)) {
            $plugin = Plugin::where('unikey', $unikey)->first();

            $link = null;
            if ($plugin) {
                $system_url = ConfigHelper::fresnsConfigByItemKey('system_url');

                $url = empty($plugin->plugin_host) ? $system_url : $plugin->plugin_host;

                $link = StrHelper::qualifyUrl($plugin->access_path, $url);
            }

            $pluginUrl = $link;

            CacheHelper::put($pluginUrl, $cacheKey, $cacheTag);
        }

        return $pluginUrl ?? null;
    }

    /**
     * Get the url of the plugin that has replaced the custom parameters.
     *
     * @param  string  $unikey
     * @param  string  $parameter
     * @return mixed|string
     */
    public static function fresnsPluginUsageUrl(string $unikey, ?string $parameter = null)
    {
        $url = PluginHelper::fresnsPluginUrlByUnikey($unikey);

        if (empty($parameter) || empty($url)) {
            return $url;
        }

        return str_replace('{parameter}', $parameter, $url);
    }

    public static function fresnsPluginVersionByUnikey(string $unikey)
    {
        $version = Plugin::where('unikey', $unikey)->value('version');

        if (empty($version)) {
            return null;
        }

        return $version;
    }

    public static function fresnsPluginUpgradeCodeByUnikey(string $unikey)
    {
        $upgradeCode = Plugin::where('unikey', $unikey)->value('upgrade_code');

        if (empty($upgradeCode)) {
            return null;
        }

        return $upgradeCode;
    }

    public static function pluginDataRatingHandle(string $key, ?array $dataSources = null, ?string $langTag = null)
    {
        if (empty($dataSources)) {
            return null;
        }

        $pluginRatingArr = $dataSources[$key]['pluginRating'] ?? [];

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $pluginRating = null;
        foreach ($pluginRatingArr as $arr) {
            $item['id'] = $arr['id'];
            $item['title'] = collect($arr['intro'])->where('langTag', $langTag)->first()['title'] ?? null;
            $item['description'] = collect($arr['intro'])->where('langTag', $langTag)->first()['description'] ?? null;
            $pluginRating[] = $item;
        }

        return $pluginRating;
    }

    public static function fresnsPluginSubscribeItems(?int $type = null)
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
