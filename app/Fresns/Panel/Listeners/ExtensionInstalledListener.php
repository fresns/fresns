<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Listeners;

use App\Helpers\CacheHelper;

class ExtensionInstalledListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     */
    public function handle($event): void
    {
        $fskey = $event['fskey'] ?? null;
        if (empty($fskey)) {
            return;
        }

        $cacheTag = 'fresnsConfigs';
        CacheHelper::forgetFresnsKey("fresns_plugin_version_{$fskey}", $cacheTag);
    }
}
