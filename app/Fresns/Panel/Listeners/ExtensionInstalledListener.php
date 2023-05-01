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
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $fskey = $event['fskey'] ?? null;
        if (empty($fskey)) {
            return;
        }

        CacheHelper::forgetFresnsKey("fresns_plugin_version_{$fskey}");
    }
}
