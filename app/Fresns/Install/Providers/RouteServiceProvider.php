<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Install\Providers;

use App\Fresns\Panel\Http\Middleware\ChangeLocale;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        Route::name('install.')->prefix('install')->middleware(['web', ChangeLocale::class])->withoutMiddleware([VerifyCsrfToken::class])->group(__DIR__.'/../Routes/web.php');
    }
}
