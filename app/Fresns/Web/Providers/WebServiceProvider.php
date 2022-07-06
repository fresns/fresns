<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Providers;

use App\Fresns\Web\Auth\AccountGuard;
use App\Fresns\Web\Auth\UserGuard;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class WebServiceProvider extends ServiceProvider
{
    public function boot()
    {
        config()->set('laravellocalization.useAcceptLanguageHeader', false);

        config()->set('laravellocalization.hideDefaultLocaleInURL', true);

        config()->set('laravellocalization.supportedLocales', Cache::get('supportedLocales') ?: [
            fs_db_config('default_language') => ['name' => fs_db_config('default_language')],
        ]);

        config()->set('app.locale', fs_db_config('default_language'));

        $this->app->register(RouteServiceProvider::class);

        Paginator::useBootstrap();
    }

    public function register()
    {
        $this->registerAuthenticator();
        $this->registerViews();
        $this->registerTranslations();
    }

    protected function registerAuthenticator(): void
    {
        app()->singleton('fresns.account', function ($app) {
            return new AccountGuard($app);
        });

        app()->singleton('fresns.user', function ($app) {
            return new UserGuard($app);
        });
    }

    protected function registerViews()
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'FsWeb');
    }

    protected function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'FsWeb');
    }
}
