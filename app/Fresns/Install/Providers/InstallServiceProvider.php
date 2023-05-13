<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Install\Providers;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\ServiceProvider;

class InstallServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerInstallAppKey();
        $this->registerReverseProxySchema();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerTranslations();

        $this->app->register(RouteServiceProvider::class);
    }

    public function registerReverseProxySchema(): void
    {
        // No more registered installation routes after they have been installed
        if (! file_exists(base_path('install.lock'))) {
            config([
                'trustedproxy.proxies' => '*',
            ]);
        }

        if (config('app.trusted_proxies')) {
            $customProxies = config('app.trusted_proxies', '');

            config([
                'trustedproxy.proxies' => explode(',', $customProxies),
            ]);
        }

        $handler = resolve(\Illuminate\Contracts\Http\Kernel::class);

        $handler->pushMiddleware(\App\Fresns\Install\Http\Middleware\DetectionRequestProtocol::class);
    }

    public function registerInstallAppKey(): void
    {
        if (! file_exists(base_path('.env'))) {
            $appKey = Encrypter::generateKey(config('app.cipher'));
            $parseUrl = parse_url(\request()->getUri());
            $appUrl = $parseUrl['scheme'].'://'.$parseUrl['host'];
            $envContent = sprintf("APP_DEBUG=true\nAPP_KEY=base64:%s\nAPP_URL=%s",
                base64_encode($appKey),
                $appUrl
            );
            file_put_contents(base_path('.env'), $envContent);

            config(['app.key' => $appKey]);
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'install');
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'Install');
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'Install');
    }
}
