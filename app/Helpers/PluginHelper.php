<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Plugin;
use App\Models\PluginUsage;

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

        $plugin = Plugin::where('unikey', $unikey)->first(['plugin_domain', 'access_path']);
        if (empty($plugin)) {
            return null;
        }

        $backend_domain = ConfigHelper::fresnsConfigByItemKey('backend_domain');

        $domain = empty($plugin->plugin_domain) ? $backend_domain : $plugin->plugin_domain;

        $url = StrHelper::qualifyUrl($plugin->access_path, $domain);

        return $url;
    }

    /**
     * Get the url of the plugin that has replaced the custom parameters.
     *
     * @param  string  $unikey
     * @param  int  $pluginUsagesId
     * @return mixed|string
     */
    public static function fresnsPluginUsageUrl(string $unikey, int $pluginUsagesId)
    {
        $plugin = Plugin::where('unikey', $unikey)->first(['plugin_domain', 'access_path']);
        if (empty($plugin)) {
            return null;
        }

        $url = self::fresnsPluginUrlByUnikey($unikey);

        $parameter = PluginUsage::where('id', $pluginUsagesId)->value('parameter');
        if (empty($parameter)) {
            return $url;
        }

        $replaceUrl = str_replace('{parameter}', $parameter, $url);

        return $replaceUrl;
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

    public static function pluginRatingHandle(string $key, ?array $dataSources = null, ?string $langTag = null)
    {
        if (empty($dataSources)) {
            return null;
        }

        $pluginRatingArr = $dataSources[$key]['pluginRating'];

        $langTag = $langTag ?: ConfigHelper::fresnsConfigByItemKey('default_language');

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
