<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Plugin;
use Illuminate\Support\Facades\Cache;

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

        $cacheKey = "fresns_plugin_{$unikey}_url";

        $pluginUrl = Cache::remember($cacheKey, now()->addDays(7), function () use ($unikey) {
            $plugin = Plugin::where('unikey', $unikey)->first(['plugin_host', 'access_path']);
            if (empty($plugin)) {
                return null;
            }

            $system_url = ConfigHelper::fresnsConfigByItemKey('system_url');

            $url = empty($plugin->plugin_host) ? $system_url : $plugin->plugin_host;

            $link = StrHelper::qualifyUrl($plugin->access_path, $url);

            return $link ?? null;
        });

        return $pluginUrl;
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
        $parameterKey = ! empty($parameter) ? StrHelper::slug($parameter) : 'usage';
        $cacheKey = "fresns_plugin_{$unikey}_{$parameterKey}_url";

        $pluginUrl = Cache::remember($cacheKey, now()->addDays(7), function () use ($unikey, $parameter) {
            $url = PluginHelper::fresnsPluginUrlByUnikey($unikey);

            if (empty($parameter) || empty($url)) {
                return $url;
            }

            $replaceUrl = str_replace('{parameter}', $parameter, $url);

            return $replaceUrl;
        });

        return $pluginUrl;
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

        $pluginRatingArr = $dataSources[$key]['pluginRating'];

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
}
