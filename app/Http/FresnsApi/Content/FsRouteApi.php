<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

// Fresns Content API
Route::group(['prefix' => 'fresns', 'namespace' => '\App\Http\FresnsApi\Content'], function () {
    // Group
    Route::post('/group/trees', 'FsControllerApi@groupTrees')->name('api.content.groupTrees');
    Route::post('/group/lists', 'FsControllerApi@groupLists')->name('api.content.groupLists');
    Route::post('/group/detail', 'FsControllerApi@groupDetail')->name('api.content.groupDetail');
    // Hashtag
    Route::post('/hashtag/lists', 'FsControllerApi@hashtagLists')->name('api.content.hashtagLists');
    Route::post('/hashtag/detail', 'FsControllerApi@hashtagDetail')->name('api.content.hashtagDetail');
    // Post
    Route::post('/post/lists', 'FsControllerApi@postLists')->name('api.content.postLists');
    Route::post('/post/follows', 'FsControllerApi@postFollows')->name('api.content.postFollows');
    Route::post('/post/nearbys', 'FsControllerApi@postNearbys')->name('api.content.postNearbys');
    Route::post('/post/detail', 'FsControllerApi@postDetail')->name('api.content.postDetail');
    // Comment
    Route::post('/comment/lists', 'FsControllerApi@commentLists')->name('api.content.commentLists');
    Route::post('/comment/detail', 'FsControllerApi@commentDetail')->name('api.content.commentDetail');
});
