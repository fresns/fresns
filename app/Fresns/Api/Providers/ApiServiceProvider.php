<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Providers;

use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

        $this->mergeConfigFrom(__DIR__.'/../Config/blacklist.php', 'FsApiBlacklist');
        $this->mergeConfigFrom(__DIR__.'/../Config/whitelist.php', 'FsApiWhitelist');
    }
}
