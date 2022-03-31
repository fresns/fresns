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
    public static function fresnsPluginUrlByUnikey(string $unikey)
    {
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
}
