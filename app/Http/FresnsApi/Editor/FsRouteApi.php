<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

// Fresns Editor API
Route::group(['prefix' => 'fresns', 'namespace' => '\App\Http\FresnsApi\Editor'], function () {
    // Configs
    Route::post('/editor/configs', 'FsControllerApi@configs')->name('api.editor.configs');
    // Pull
    Route::post('/editor/create', 'FsControllerApi@create')->name('api.editor.create');
    Route::post('/editor/lists', 'FsControllerApi@lists')->name('api.editor.lists');
    Route::post('/editor/detail', 'FsControllerApi@detail')->name('api.editor.detail');
    // Push
    Route::post('/editor/update', 'FsControllerApi@update')->name('api.editor.update');
    Route::post('/editor/submit', 'FsControllerApi@submit')->name('api.editor.submit');
    Route::post('/editor/publish', 'FsControllerApi@publish')->name('api.editor.publish');
    // Operation
    Route::post('/editor/upload', 'FsControllerApi@upload')->name('api.editor.upload');
    Route::post('/editor/uploadToken', 'FsControllerApi@uploadToken')->name('api.editor.uploadToken');
    Route::post('/editor/delete', 'FsControllerApi@delete')->name('api.editor.delete');
    Route::post('/editor/revoke', 'FsControllerApi@revoke')->name('api.editor.revoke');
});
