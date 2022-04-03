<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Fresns\Install\Http\Controllers as ApiController;
use App\Fresns\Install\Http\Middleware\AppKeyMiddleware;
use App\Fresns\Install\Http\Middleware\ChangeLanguage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('api')->middleware([
    ChangeLanguage::class,
])->group(function () {
    Route::post('install', [ApiController\InstallController::class, 'install']);
});
