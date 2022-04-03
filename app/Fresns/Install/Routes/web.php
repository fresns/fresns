<?php

use Illuminate\Support\Facades\Route;
use App\Fresns\Install\Http\Middleware\ChangeLanguage;
use App\Fresns\Install\Http\Middleware\AppKeyMiddleware;
use App\Fresns\Install\Http\Controllers as ApiController;

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

Route::middleware([
    ChangeLanguage::class,
])->group(function () {
    Route::get('install', [ApiController\InstallController::class, 'showInstallForm']);
});