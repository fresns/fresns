<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Fresns\Web\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::middleware([
        AccountAuthorize::class,
        UserAuthorize::class,
    ])
    ->group(function () {

        Route::name('user.')->prefix('user')->group(function () {
            Route::post('mark', [ApiController::class, 'mark'])->name('mark');
            Route::put('mark-note', [ApiController::class, 'markNote'])->name('markNote');
        });
    });
