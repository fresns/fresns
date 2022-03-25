<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Plugin;
use App\Models\PluginUsage;
use Illuminate\Support\Arr;

class PluginHelper
{
    /**
     * Get the plugin access url.
     *
     * @param  string  $unikey
     * @return string
     */
    public static function fresnsPluginUrlByUnikey(string $unikey)
    {
        $plugin = Plugin::select(['plugin_domain', 'access_path'])->where('unikey', '=', $unikey)->first();
        if (empty($plugin)) {
            return '';
        }
        $backend_domain = ConfigHelper::fresnsConfigByItemKey('backend_domain');
        $plugin_domain = empty($plugin->plugin_domain) ? $backend_domain : $plugin->plugin_domain;
        $url = $plugin_domain.$plugin->access_path ?? '';

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
        $pluginUsage = PluginUsage::find($pluginUsagesId);
        $url = self::fresnsPluginUrlByUnikey($unikey);
        if (empty($pluginUsage) || empty($url) || $pluginUsage->plugin_unikey != $unikey) {
            return '';
        }
        $replaceUrl = str_replace('{parameter}', Arr::get($pluginUsage, 'parameter', ''), $url);

        return $replaceUrl;
    }
}
