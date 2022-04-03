<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Install\Providers;

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
            $appKey = \Illuminate\Encryption\Encrypter::generateKey(config('app.cipher'));
            $appKey = sprintf('base64:%s', base64_encode($appKey));
            file_put_contents(base_path('.env'), $appKey);
        }
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            $configPath = __DIR__.'/../Config/config.php' => config_path('install.php'),
        ], 'config');

        $this->mergeConfigFrom(
            $configPath, 'install',
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/plugins/install');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', 'install-plugin-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), 'Install');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/plugins/install');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'Install');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'Install');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/plugins/install')) {
                $paths[] = $path.'/plugins/install';
            }
        }

        return $paths;
    }
}
