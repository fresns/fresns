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
        // already installed
        if (file_exists(base_path('install.lock'))) {
            return;
        }

        $this->envConfig();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // already installed
        if (file_exists(base_path('install.lock'))) {
            return;
        }

        $this->registerConfig();
        $this->registerViews();
        $this->registerTranslations();

        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * env config.
     */
    public function envConfig(): void
    {
        $envPath = base_path('.env');

        if (file_exists($envPath)) {
            return;
        }

        $envExamplePath = app_path('Fresns/Install/Config/.env.template');

        $envTemp = file_get_contents($envExamplePath);

        $generateKey = Encrypter::generateKey(config('app.cipher'));
        $appKey = sprintf('base64:%s', base64_encode($generateKey));

        $parseUrl = parse_url(\request()->getUri());
        $appUrl = $parseUrl['scheme'].'://'.$parseUrl['host'];

        // Temp write key
        $template = [
            'APP_KEY' => $appKey,
            'APP_URL' => $appUrl,
            'APP_TIMEZONE' => '',
            'DB_CONNECTION' => '',
            'DB_HOST' => '',
            'DB_PORT' => '',
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
            'DB_PREFIX' => '',
        ];

        foreach ($template as $key => $value) {
            $envTemp = str_replace('{'.$key.'}', $value, $envTemp);
        }

        file_put_contents($envPath, $envTemp);

        config(['app.key' => $appKey]);
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
