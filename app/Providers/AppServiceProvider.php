<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Providers;

use App\Utilities\AppUtility;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // fresns http
        $this->macroAppHttp();
        $this->macroMarketHttp();

        // force scheme
        $appUrl = config('app.url');
        $parseUrl = parse_url($appUrl);
        if ($parseUrl['scheme'] == 'https') {
            URL::forceScheme('https');
        }

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

    // app http
    public static function macroAppHttp(): void
    {
        Http::macro('fresns', function () {
            $httpProxy = config('app.http_proxy');

            return Http::baseUrl(AppUtility::BASE_URL)
                ->withHeaders([
                    'accept' => 'application/json',
                ])
                ->withOptions([
                    'proxy' => [
                        'http' => $httpProxy,
                        'https' => $httpProxy,
                    ],
                ]);
        });
    }

    // market http
    public static function macroMarketHttp(): void
    {
        Http::macro('market', function () {
            $httpProxy = config('app.http_proxy');

            return Http::withHeaders(AppUtility::getMarketHeaders())
                ->baseUrl(AppUtility::MARKETPLACE_URL)
                ->withHeaders([
                    'accept' => 'application/json',
                ])
                ->withOptions([
                    'proxy' => [
                        'http' => $httpProxy,
                        'https' => $httpProxy,
                    ],
                ]);
        });
    }
}
