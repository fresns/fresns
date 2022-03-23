<?php

namespace App\Fresns\Words\Providers;


use Illuminate\Support\ServiceProvider;

class WordConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
    }

    protected function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/cmdword.php', 'CmdWordConfig');
    }
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
