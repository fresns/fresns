<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        // Routing is disabled when data cannot be queried from the database
        try {
            if (! fs_db_config('FresnsEngine')) {
                return;
            }
        } catch (\Throwable $e) {
            return;
        }

        $url = config('app.url');
        $host = str_replace(['http://', 'https://'], '', rtrim($url, '/'));
        $currentAccessHost = \request()->httpHost();

        $host = parse_url('//'.$host, PHP_URL_HOST);
        $currentAccessHost = parse_url('//'.$currentAccessHost, PHP_URL_HOST);

        // Avoid home page conflicts when customizing domain names with plugins
        if ($host != $currentAccessHost) {
            $host = null;
        }

        Route::group([
            'domain' => $host,
        ], function () {
            $this->mapApiRoutes();
            $this->mapWebRoutes();
        });
    }

    protected function mapApiRoutes()
    {
        Route::prefix('api')->name('fresns.api.')->group(__DIR__.'/../Routes/api.php');
    }

    protected function mapWebRoutes()
    {
        Route::name('fresns.')->group(__DIR__.'/../Routes/web.php');
    }
}
