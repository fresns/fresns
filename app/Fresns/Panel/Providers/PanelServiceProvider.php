<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Providers;

use App\Fresns\Panel\Http\Exceptions\Handler;
use App\Fresns\Panel\Http\Middleware\Authenticate;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class PanelServiceProvider extends ServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot()
    {
        Paginator::useBootstrap();

        $this->registerTranslations();
        $this->registerViews();

        \Config::set('auth.guards.panel', [
            'driver' => 'session',
            'provider' => 'panel',
        ]);

        \Config::set('auth.providers.panel', [
            'driver' => 'eloquent',
            'model' => \App\Models\Account::class,
        ]);

        \Route::aliasMiddleware('panelAuth', Authenticate::class);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerConfig();
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register views.
     *
     * @return void
     */
    protected function registerViews()
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'FsView');
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/panel.php', 'FsConfig');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    protected function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'FsLang');
    }
}
