<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class PanelServiceProvider extends ServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        $this->registerTranslations();
        $this->registerViews();

        Config::set('auth.guards.panel', [
            'driver' => 'session',
            'provider' => 'panel',
        ]);

        Config::set('auth.providers.panel', [
            'driver' => 'eloquent',
            'model' => \App\Models\Account::class,
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerConfig();
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'FsView');
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/panel.php', 'FsConfig');
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'FsLang');
    }
}
