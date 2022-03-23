<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FresnsApiServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Route::prefix('api/v1/')->group(function () {
            $this->registerRoutes();
        });
    }

    private function registerRoutes()
    {
        $routePaths = [
            'Info',
            'Account',
            'User',
            'Message',
            'Content',
            'Editor',
        ];

        foreach ($routePaths as $path) {
            $this->loadRoutesFrom(__DIR__.'/../Http/'.$path.'/FsRouteApi.php');
        }
    }
}
