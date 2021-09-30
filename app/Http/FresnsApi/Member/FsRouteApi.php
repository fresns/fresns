<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

// Fresns Member API
Route::group(['prefix' => 'fresns/member', 'namespace' => '\App\Http\FresnsApi\Member'], function () {
    // Member Login
    Route::post('/auth', 'FsControllerApi@auth')->name('api.member.auth');
    Route::post('/detail', 'FsControllerApi@detail')->name('api.member.detail');
    Route::post('/lists', 'FsControllerApi@lists')->name('api.member.lists');
    Route::post('/edit', 'FsControllerApi@edit')->name('api.member.edit');
    // Member Mark Operation
    Route::post('/mark', 'FsControllerApi@mark')->name('api.member.mark');
    Route::post('/markLists', 'FsControllerApi@markLists')->name('api.member.markLists');
    // Delete Post or Comment
    Route::post('/delete', 'FsControllerApi@delete')->name('api.member.delete');
    // Member Data
    Route::post('/roles', 'FsControllerApi@roles')->name('api.member.roles');
    Route::post('/interactions', 'FsControllerApi@interactions')->name('api.member.interactions');
});
