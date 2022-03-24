<?php

namespace App\Fresns\Subscribe\Providers;

use Illuminate\Support\ServiceProvider;

class SubscribeServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->register(CmdWordServiceProvider::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerHelpers();
        $this->registerProviders();
    }

    public function registerHelpers()
    {
        require_once __DIR__ . '/../helpers.php';
    }

    protected function registerProviders()
    {
        $this->app->register(EventServiceProvider::class);
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
}
