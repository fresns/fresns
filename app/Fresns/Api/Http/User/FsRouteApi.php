<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

// Fresns User API
Route::group(['prefix' => 'user', 'namespace' => '\App\Fresns\Api\Http\User'], function () {
    // User Login
    Route::post('/auth', 'FsControllerApi@auth')->name('api.user.auth');
    Route::post('/detail', 'FsControllerApi@detail')->name('api.user.detail');
    Route::post('/lists', 'FsControllerApi@lists')->name('api.user.lists');
    Route::post('/edit', 'FsControllerApi@edit')->name('api.user.edit');
    // User Mark Operation
    Route::post('/mark', 'FsControllerApi@mark')->name('api.user.mark');
    Route::post('/markLists', 'FsControllerApi@markLists')->name('api.user.markLists');
    // Delete Post or Comment
    Route::post('/delete', 'FsControllerApi@delete')->name('api.user.delete');
    // User Data
    Route::post('/roles', 'FsControllerApi@roles')->name('api.user.roles');
    Route::post('/interactions', 'FsControllerApi@interactions')->name('api.user.interactions');
});
