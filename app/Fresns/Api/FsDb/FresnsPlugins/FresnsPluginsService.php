<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsPlugins;

use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsages;

class FresnsPluginsService extends FsService
{
    // Get plugin url via unikey
    public static function getPluginUrlByUnikey($unikey)
    {
        $plugin = FresnsPlugins::where('unikey', $unikey)->first();
        if (empty($plugin)) {
            return '';
        }

        $uri = $plugin['access_path'];
        if (empty($plugin['plugin_domain'])) {
            $domain = ApiConfigHelper::getConfigByItemKey('backend_domain');
        } else {
            $domain = $plugin['plugin_domain'];
        }
        $url = $domain.$uri;

        return $url;
    }

    /**
     * The full URL address of the plugin, stitched together from the domain name field plugin_domain plus the path field access_path.
     * When plugin_domain is empty, it is spliced with the backend address (configuration table key name backend_domain) to form the full URL address.
     * If the address has a {parameter} variable name, use the record plugin_usages > parameter to replace the field value.
     */
    public static function getPluginUsagesUrl($pluginUnikey, $pluginUsagesid)
    {
        $bucketDomain = ApiConfigHelper::getConfigByItemKey('backend_domain');
        $pluginUsages = FresnsPluginUsages::find($pluginUsagesid);
        $plugin = FresnsPlugins::where('unikey', $pluginUnikey)->first();
        $url = '';
        if (! $plugin || ! $pluginUsages) {
            return $url;
        }
        $access_path = $plugin['access_path'];
        $str = strstr($access_path, '{parameter}');
        if ($str) {
            $uri = str_replace('{parameter}', $pluginUsages['parameter'], $access_path);
        } else {
            $uri = $access_path;
        }
        if (empty($plugin['plugin_url'])) {
            $url = $bucketDomain.$uri;
        } else {
            $url = $plugin['plugin_domain'].$uri;
        }

        return $url;
    }
}
