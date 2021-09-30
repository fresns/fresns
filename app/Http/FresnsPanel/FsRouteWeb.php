<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Http\Center\Common\GlobalService;
use App\Http\FresnsApi\Base\FresnsBaseApiController;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsConfig;

$appName = env('APP_NAME');

if ($appName == 'Fresns') {
    GlobalService::loadGlobalData();
    $adminPath = ApiConfigHelper::getConfigByItemKey(FresnsConfigsConfig::BACKEND_PATH) ?? 'admin';
    $adminPath = '/fresns'."/$adminPath";

    Route::group(['prefix' => "$adminPath", 'namespace' => '\App\Http\FresnsPanel'], function () {
        // Login Page
        Route::get('/', 'FsControllerWeb@index')->name('admin.fresnsConsole.index');
    });

    // Login Request (No login status required)
    Route::group(['prefix' => 'fresns', 'namespace' => '\App\Http\FresnsPanel'], function () {
        Route::post('/loginAcc', 'FsControllerWeb@loginAcc')->name('admin.fresnsConsole.loginAcc');
        Route::post('/checkLogin', 'FsControllerWeb@checkLogin')->name('admin.fresnsConsole.checkLogin');
        Route::get('/login', 'FsControllerWeb@loginIndex')->name('admin.fresnsConsole.loginIndex');
    });

    // Console Page (login status required)
    Route::group(['prefix' => 'fresns', 'middleware' => ['web', 'auth'], 'namespace' => '\App\Http\FresnsPanel'], function () {
        // Function Operation Page
        Route::get('/dashboard', 'FsControllerWeb@dashboard')->name('admin.fresnsConsole.dashboard');
        Route::get('/settings', 'FsControllerWeb@settings')->name('admin.fresnsConsole.settings');
        Route::get('/keys', 'FsControllerWeb@keys')->name('admin.fresnsConsole.keys');
        Route::get('/admins', 'FsControllerWeb@admins')->name('admin.fresnsConsole.admins');
        Route::get('/websites', 'FsControllerWeb@websites')->name('admin.fresnsConsole.websites');
        Route::get('/apps', 'FsControllerWeb@apps')->name('admin.fresnsConsole.apps');
        Route::get('/plugins', 'FsControllerWeb@plugins')->name('admin.fresnsConsole.plugins');
        Route::get('/iframe', 'FsControllerWeb@iframe')->name('admin.fresnsConsole.iframe');
        // Logout Console
        Route::get('/logout', 'FsControllerWeb@logout')->name('admin.fresnsConsole.logout');
        // Setting Language
        Route::post('/setLanguage', 'FsControllerWeb@setLanguage')->name('admin.fresnsConsole.setLanguage');
        // Console Settings
        Route::post('/updateSetting', 'FsControllerWeb@updateSetting')->name('admin.fresnsConsole.updateSetting');
        // Administrator Settings
        Route::post('/addAdmin', 'FsControllerWeb@addAdmin')->name('admin.fresnsConsole.addAdmin');
        Route::post('/delAdmin', 'FsControllerWeb@delAdmin')->name('admin.fresnsConsole.delAdmin');
        // Key Management
        Route::post('/submitKey', 'FsControllerWeb@submitKey')->name('admin.fresnsConsole.submitKey');
        Route::post('/updateKey', 'FsControllerWeb@updateKey')->name('admin.fresnsConsole.updateKey');
        Route::post('/resetKey', 'FsControllerWeb@resetKey')->name('admin.fresnsConsole.resetKey');
        Route::post('/delKey', 'FsControllerWeb@delKey')->name('admin.fresnsConsole.delKey');
        // Extensions Related
        Route::post('/install', 'FsControllerWeb@install')->name('admin.fresnsConsole.install');
        Route::post('/uninstall', 'FsControllerWeb@uninstall')->name('admin.fresnsConsole.uninstall');
        Route::post('/updateUnikey', 'FsControllerWeb@updateUnikey')->name('admin.fresnsConsole.updateUnikey');
        Route::post('/localInstall', 'FsControllerWeb@localInstall')->name('admin.fresnsConsole.localInstall');
        Route::post('/enableUnikeyStatus', 'FsControllerWeb@enableUnikeyStatus')->name('admin.fresnsConsole.install');
        Route::post('/websiteLinkSubject', 'FsControllerWeb@websiteLinkSubject')->name('admin.fresnsConsole.websiteLinkSubject');
    });
}
