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
        Route::get('top-list', [ApiController::class, 'topList'])->name('top.list')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);

        Route::get('url-sign', [ApiController::class, 'urlSign'])->name('url.sign')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class, CheckSiteModel::class]);

        Route::get('input-tips', [ApiController::class, 'getInputTips'])->name('input.tips')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
        Route::get('archives', [ApiController::class, 'getArchives'])->name('archives')->withoutMiddleware([UserAuthorize::class]);
        Route::post('send-verify-code', [ApiController::class, 'sendVerifyCode'])->name('send.verify.code')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
        Route::post('upload-file', [ApiController::class, 'uploadFile'])->name('upload.file');

        Route::prefix('account')->name('account.')->group(function () {
            Route::post('register', [ApiController::class, 'accountRegister'])->name('register')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::post('login', [ApiController::class, 'accountLogin'])->name('login')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class, CheckSiteModel::class]);
            Route::post('reset-password', [ApiController::class, 'accountResetPassword'])->name('reset.password')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class, CheckSiteModel::class]);
            Route::post('verify-identity', [ApiController::class, 'accountVerifyIdentity'])->name('verify.identity')->withoutMiddleware([UserAuthorize::class]);
            Route::post('edit', [ApiController::class, 'accountEdit'])->name('edit')->withoutMiddleware([UserAuthorize::class]);
        });

        Route::prefix('user')->name('user.')->group(function () {
            Route::post('auth', [ApiController::class, 'userAuth'])->name('auth')->withoutMiddleware([UserAuthorize::class]);
            Route::post('edit', [ApiController::class, 'userEdit'])->name('edit');
            Route::post('mark', [ApiController::class, 'userMark'])->name('mark');
            Route::put('mark-note', [ApiController::class, 'userMarkNote'])->name('mark.note');
        });

        Route::prefix('message')->name('message.')->group(function () {
            Route::put('{type}', [ApiController::class, 'messageMarkAsRead'])->name('mark.as.read');
            Route::delete('{type}', [ApiController::class, 'messageDelete'])->name('delete');
        });

        Route::prefix('content')->name('content.')->group(function () {
            Route::get('type', [ApiController::class, 'contentType'])->name('type');
            Route::get('file/{fid}/link', [ApiController::class, 'contentFileLink'])->name('file.link');
            Route::get('file/{fid}/users', [ApiController::class, 'contentFileUsers'])->name('file.users')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::delete('{type}/{fsid}', [ApiController::class, 'contentDelete'])->name('delete');
        });

        Route::prefix('group')->name('group.')->group(function () {
            Route::get('list/{gid}', [ApiController::class, 'groupList'])->name('list');
        });

        Route::prefix('editor')->name('editor.')->group(function () {
            Route::post('direct-publish', [ApiController::class, 'directPublish'])->name('direct.publish');
            Route::post('upload-file', [ApiController::class, 'editorUploadFile'])->name('upload.file');
            Route::post('{type}/update/{draftId}', [ApiController::class, 'editorUpdate'])->name('update');
            Route::post('{type}/publish/{draftId}', [ApiController::class, 'editorPublish'])->name('publish');
            Route::patch('{type}/recall/{draftId}', [ApiController::class, 'editorRecall'])->name('recall');
            Route::delete('{type}/delete/{draftId}', [ApiController::class, 'editorDelete'])->name('delete');
        });

        // FsLang
        Route::get('js/{locale?}/translations', function ($locale) {
            $languagePack = fs_api_config('language_pack_contents');

            // get request, return translation content
            return \response()->json([
                'data' => $languagePack,
            ]);
        })->name('translations')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
    });
