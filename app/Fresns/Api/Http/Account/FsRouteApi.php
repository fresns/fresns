<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

// Fresns Account API
Route::group(['prefix' => 'account', 'namespace' => '\App\Fresns\Api\Http\Account'], function () {
    Route::post('/register', 'FsControllerApi@register')->name('api.account.register');
    Route::post('/login', 'FsControllerApi@login')->name('api.account.login');
    Route::post('/logout', 'FsControllerApi@logout')->name('api.account.logout');
    Route::post('/delete', 'FsControllerApi@delete')->name('api.account.delete');
    Route::post('/restore', 'FsControllerApi@restore')->name('api.account.restore');
    Route::post('/reset', 'FsControllerApi@reset')->name('api.account.reset');
    Route::post('/verification', 'FsControllerApi@verification')->name('api.account.verification');
    Route::post('/detail', 'FsControllerApi@detail')->name('api.account.detail');
    Route::post('/edit', 'FsControllerApi@edit')->name('api.account.edit');
    Route::post('/walletLogs', 'FsControllerApi@walletLogs')->name('api.account.walletLogs');
});
