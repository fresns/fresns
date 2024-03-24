<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use App\Fresns\Install\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebController::class, 'index'])->name('index');

Route::post('config-database', [WebController::class, 'configDatabase'])->name('config-database');
Route::post('data-artisan', [WebController::class, 'dataArtisan'])->name('data-artisan');
Route::post('add-admin', [WebController::class, 'addAdmin'])->name('add-admin');

Route::get('check-server', [WebController::class, 'checkServer'])->name('check-server');
