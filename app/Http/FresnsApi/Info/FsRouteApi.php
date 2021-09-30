<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

// Fresns Info API
Route::group(['prefix' => 'fresns/info', 'namespace' => '\App\Http\FresnsApi\Info'], function () {
    // System Config Info
    Route::post('/configs', 'FsControllerApi@configs')->name('api.info.configs');
    // Extensions Config Info
    Route::post('/extensions', 'FsControllerApi@extensions')->name('api.info.extensions');
    // Overview
    Route::post('/overview', 'FsControllerApi@overview')->name('api.info.overview');
    // Emojis
    Route::post('/emojis', 'FsControllerApi@emojis')->name('api.info.emojis');
    // Stop Words
    Route::post('/stopWords', 'FsControllerApi@stopWords')->name('api.info.stopWords');
    // Send Verify Code
    Route::post('/sendVerifyCode', 'FsControllerApi@sendVerifyCode')->name('api.info.sendVerifyCode');
    // Input Tips
    Route::post('/inputTips', 'FsControllerApi@inputTips')->name('api.info.inputTips');
    // Upload Log
    Route::post('/uploadLog', 'FsControllerApi@uploadLog')->name('api.info.uploadLog');
    // Callback Info
    Route::post('/callbacks', 'FsControllerApi@callbacks')->name('api.info.callbacks');
    // Download File
    Route::post('/downloadFile', 'FsControllerApi@downloadFile')->name('api.info.downloadFile');
});
