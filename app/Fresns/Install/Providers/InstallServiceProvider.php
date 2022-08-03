<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Install\Providers;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\ServiceProvider;

class InstallServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerInstallAppKey();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerTranslations();

        $this->app->register(RouteServiceProvider::class);
    }

    public function registerInstallAppKey()
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
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'install');
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'Install');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'Install');
    }
}
