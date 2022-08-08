<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Fresns\Web\Http\Controllers\ApiController;
use App\Fresns\Web\Http\Middleware\AccountAuthorize;
use App\Fresns\Web\Http\Middleware\CheckSiteModel;
use App\Fresns\Web\Http\Middleware\UserAuthorize;
use Illuminate\Support\Facades\Route;

Route::prefix('engine')
    ->middleware([
        'web',
        AccountAuthorize::class,
        UserAuthorize::class,
        CheckSiteModel::class,
    ])
    ->group(function () {
        Route::get('url-sign', [ApiController::class, 'urlSign'])->name('url.sign')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class, CheckSiteModel::class]);

        Route::post('send-verify-code', [ApiController::class, 'sendVerifyCode'])->name('send.verifyCode')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
        Route::get('download-link', [ApiController::class, 'downloadLink'])->name('file.download');
        Route::post('upload-file', [ApiController::class, 'uploadFile'])->name('upload.file');

        Route::prefix('account')->name('account.')->group(function () {
            Route::post('register', [ApiController::class, 'accountRegister'])->name('register')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::post('login', [ApiController::class, 'accountLogin'])->name('login')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class, CheckSiteModel::class]);
            Route::post('reset-password', [ApiController::class, 'resetPassword'])->name('resetPassword')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class, CheckSiteModel::class]);
            Route::post('edit', [ApiController::class, 'accountEdit'])->name('edit')->withoutMiddleware([UserAuthorize::class]);
        });

        Route::prefix('user')->name('user.')->group(function () {
            Route::post('auth', [ApiController::class, 'userAuth'])->name('auth')->withoutMiddleware([UserAuthorize::class]);
            Route::post('edit', [ApiController::class, 'userEdit'])->name('edit');
            Route::post('mark', [ApiController::class, 'userMark'])->name('mark');
            Route::put('mark-note', [ApiController::class, 'userMarkNote'])->name('mark.note');
        });

        Route::delete('post/{pid}', [ApiController::class, 'postDelete'])->name('post.delete');
        Route::delete('comment/{cid}', [ApiController::class, 'commentDelete'])->name('comment.delete');

        Route::prefix('editor')->name('editor.')->group(function () {
            Route::get('{type}/drafts', [ApiController::class, 'drafts'])->name('drafts');
            Route::post('{type}/create', [ApiController::class, 'create'])->name('create');
            Route::get('{type}/{draftId}', [ApiController::class, 'detail'])->name('detail');
            Route::put('{type}/{draftId}', [ApiController::class, 'update'])->name('update');
            Route::post('{type}/{draftId}', [ApiController::class, 'publish'])->name('publish');
            Route::patch('{type}/{draftId}', [ApiController::class, 'revoke'])->name('revoke');
            Route::delete('{type}/{draftId}', [ApiController::class, 'delete'])->name('delete');
            Route::post('direct-publish', [ApiController::class, 'directPublish'])->name('direct.publish');
        });
    });
