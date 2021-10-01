<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsPlugins;

use App\Http\FresnsApi\Helpers\ApiConfigHelper;

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

}
