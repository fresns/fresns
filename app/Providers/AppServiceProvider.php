<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Providers;

use App\Utilities\AppUtility;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // fresns marketplace
        AppUtility::macroMarketHeaders();

        // trusted proxies
        $customProxies = config('app.trusted_proxies', '');
        if ($customProxies) {
            config([
                'trustedproxy.proxies' => explode(',', $customProxies),
            ]);
        }
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }
}
