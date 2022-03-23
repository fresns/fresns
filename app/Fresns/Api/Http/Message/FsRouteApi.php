<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

// Fresns Message API
Route::group(['namespace' => '\App\Fresns\Api\Http\Message'], function () {
    // Notify
    Route::post('/notify/lists', 'FsControllerApi@notifyLists')->name('api.notify.lists');
    Route::post('/notify/read', 'FsControllerApi@notifyRead')->name('api.notify.read');
    Route::post('/notify/delete', 'FsControllerApi@notifyDelete')->name('api.notify.delete');
    // Dialog
    Route::post('/dialog/lists', 'FsControllerApi@dialogLists')->name('api.dialog.lists');
    Route::post('/dialog/messages', 'FsControllerApi@dialogMessages')->name('api.dialog.messages');
    Route::post('/dialog/read', 'FsControllerApi@readMessage')->name('api.dialog.readMessage');
    Route::post('/dialog/send', 'FsControllerApi@sendMessage')->name('api.dialog.sendMessage');
    Route::post('/dialog/delete', 'FsControllerApi@dialogDelete')->name('api.dialog.delete');
});
