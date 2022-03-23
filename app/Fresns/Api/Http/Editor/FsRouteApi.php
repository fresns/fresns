<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

// Fresns Editor API
Route::group(['prefix' => 'editor', 'namespace' => '\App\Fresns\Api\Http\Editor'], function () {
    // Configs
    Route::post('/configs', 'FsControllerApi@configs')->name('api.editor.configs');
    // Pull
    Route::post('/create', 'FsControllerApi@create')->name('api.editor.create');
    Route::post('/lists', 'FsControllerApi@lists')->name('api.editor.lists');
    Route::post('/detail', 'FsControllerApi@detail')->name('api.editor.detail');
    // Push
    Route::post('/update', 'FsControllerApi@update')->name('api.editor.update');
    Route::post('/submit', 'FsControllerApi@submit')->name('api.editor.submit');
    Route::post('/publish', 'FsControllerApi@publish')->name('api.editor.publish');
    // Operation
    Route::post('/upload', 'FsControllerApi@upload')->name('api.editor.upload');
    Route::post('/uploadToken', 'FsControllerApi@uploadToken')->name('api.editor.uploadToken');
    Route::post('/delete', 'FsControllerApi@delete')->name('api.editor.delete');
    Route::post('/revoke', 'FsControllerApi@revoke')->name('api.editor.revoke');
});
