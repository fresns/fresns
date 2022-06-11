<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Fresns\Api\Http\Controllers\AccountController;
use App\Fresns\Api\Http\Controllers\CommentController;
use App\Fresns\Api\Http\Controllers\CommonController;
use App\Fresns\Api\Http\Controllers\DialogController;
use App\Fresns\Api\Http\Controllers\EditorController;
use App\Fresns\Api\Http\Controllers\GlobalController;
use App\Fresns\Api\Http\Controllers\GroupController;
use App\Fresns\Api\Http\Controllers\HashtagController;
use App\Fresns\Api\Http\Controllers\NotifyController;
use App\Fresns\Api\Http\Controllers\PostController;
use App\Fresns\Api\Http\Controllers\SearchController;
use App\Fresns\Api\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v2')->group(function () {
    Route::prefix('global')->group(function () {
        Route::get('configs', [GlobalController::class, 'configs'])->name('global.configs');
        Route::get('upload-token', [GlobalController::class, 'uploadToken'])->name('global.uploadToken');
        Route::get('roles', [GlobalController::class, 'roles'])->name('global.roles');
        Route::get('maps', [GlobalController::class, 'maps'])->name('global.maps');
        Route::get('content-type', [GlobalController::class, 'contentType'])->name('global.contentType');
        Route::get('stickers', [GlobalController::class, 'stickers'])->name('global.stickers');
        Route::get('block-words', [GlobalController::class, 'blockWords'])->name('global.blockWords');
    });

    Route::prefix('common')->group(function () {
        Route::get('input-tips', [CommonController::class, 'inputTips'])->name('common.inputTips');
        Route::get('callbacks', [CommonController::class, 'callbacks'])->name('common.callbacks');
        Route::post('send-verify-code', [CommonController::class, 'sendVerifyCode'])->name('common.sendVerifyCode');
        Route::post('upload-log', [CommonController::class, 'uploadLog'])->name('common.uploadLog');
        Route::post('upload-file', [CommonController::class, 'uploadFile'])->name('common.uploadFile');
        Route::get('file/{fid}/download-link', [CommonController::class, 'downloadFile'])->name('common.downloadFile');
        Route::get('file/{fid}/users', [CommonController::class, 'downloadUsers'])->name('common.downloadUsers');
    });

    Route::prefix('search')->group(function () {
        Route::get('users', [SearchController::class, 'users'])->name('search.users');
        Route::get('groups', [SearchController::class, 'groups'])->name('search.groups');
        Route::get('hashtags', [SearchController::class, 'hashtags'])->name('search.hashtags');
        Route::get('posts', [SearchController::class, 'posts'])->name('search.posts');
        Route::get('comments', [SearchController::class, 'comments'])->name('search.comments');
    });

    Route::prefix('account')->group(function () {
        Route::post('register', [AccountController::class, 'register'])->name('account.register');
        Route::post('login', [AccountController::class, 'login'])->name('account.login');
        Route::put('reset-password', [AccountController::class, 'resetPassword'])->name('account.resetPassword');
        Route::get('detail', [AccountController::class, 'detail'])->name('account.detail');
        Route::get('wallet-logs', [AccountController::class, 'walletLogs'])->name('account.walletLogs');
        Route::get('verify-identity', [AccountController::class, 'verifyIdentity'])->name('account.verifyIdentity');
        Route::put('edit', [AccountController::class, 'edit'])->name('account.edit');
        Route::delete('logout', [AccountController::class, 'logout'])->name('account.logout');
        Route::post('apply-delete', [AccountController::class, 'applyDelete'])->name('account.applyDelete');
        Route::post('revoke-delete', [AccountController::class, 'revokeDelete'])->name('account.revokeDelete');
    });

    Route::prefix('user')->group(function () {
        Route::get('list', [UserController::class, 'list'])->name('user.list');
        Route::get('{uidOrUsername}/detail', [UserController::class, 'detail'])->name('user.detail');
        Route::get('{uidOrUsername}/interactive/{type}', [UserController::class, 'interactive'])->name('user.interactive');
        Route::get('{uidOrUsername}/mark/{markType}/{listType}', [UserController::class, 'markList'])->name('user.markList');
        Route::post('auth', [UserController::class, 'auth'])->name('user.auth');
        Route::get('panel', [UserController::class, 'panel'])->name('user.panel');
        Route::put('edit', [UserController::class, 'edit'])->name('user.edit');
        Route::post('mark', [UserController::class, 'mark'])->name('user.mark');
    });

    Route::prefix('notify')->group(function () {
        Route::get('{type}/list', [NotifyController::class, 'list'])->name('notify.list');
        Route::put('mark-as-read', [NotifyController::class, 'markAsRead'])->name('notify.read');
        Route::delete('delete', [NotifyController::class, 'delete'])->name('notify.delete');
    });

    Route::prefix('dialog')->group(function () {
        Route::get('list', [DialogController::class, 'list'])->name('dialog.list');
        Route::get('{dialogId}/messages', [DialogController::class, 'messages'])->name('dialog.messages');
        Route::post('send-message', [DialogController::class, 'sendMessage'])->name('dialog.sendMessage');
        Route::put('mark-as-read', [DialogController::class, 'markAsRead'])->name('dialog.read');
        Route::delete('delete', [DialogController::class, 'delete'])->name('dialog.delete');
    });

    Route::prefix('group')->group(function () {
        Route::get('tree', [GroupController::class, 'tree'])->name('group.tree');
        Route::get('categories', [GroupController::class, 'categories'])->name('group.categories');
        Route::get('list', [GroupController::class, 'list'])->name('group.list');
        Route::get('{gid}/detail', [GroupController::class, 'detail'])->name('group.detail');
        Route::get('{gid}/interactive/{type}', [GroupController::class, 'interactive'])->name('group.interactive');
    });

    Route::prefix('hashtag')->group(function () {
        Route::get('list', [HashtagController::class, 'list'])->name('hashtag.list');
        Route::get('{hid}/detail', [HashtagController::class, 'detail'])->name('hashtag.detail');
        Route::get('{hid}/interactive/{type}', [HashtagController::class, 'interactive'])->name('hashtag.interactive');
    });

    Route::prefix('post')->group(function () {
        Route::get('list', [PostController::class, 'list'])->name('post.list');
        Route::get('{pid}/detail', [PostController::class, 'detail'])->name('post.detail');
        Route::get('{pid}/interactive/{type}', [PostController::class, 'interactive'])->name('post.interactive');
        Route::get('{pid}/user-list', [PostController::class, 'userList'])->name('post.userList');
        Route::get('{pid}/logs', [PostController::class, 'postLogs'])->name('post.logs');
        Route::get('log/{logId}/detail', [PostController::class, 'logDetail'])->name('post.log.detail');
        Route::delete('{pid}', [PostController::class, 'delete'])->name('post.delete');
        Route::get('follow/{type}', [PostController::class, 'follow'])->name('post.follow');
        Route::get('nearby', [PostController::class, 'nearby'])->name('post.nearby');
    });

    Route::prefix('comment')->group(function () {
        Route::get('list', [CommentController::class, 'list'])->name('comment.list');
        Route::get('{cid}/detail', [CommentController::class, 'detail'])->name('comment.detail');
        Route::get('{cid}/interactive/{type}', [CommentController::class, 'interactive'])->name('comment.interactive');
        Route::get('{cid}/logs', [CommentController::class, 'commentLogs'])->name('comment.logs');
        Route::get('log/{logId}/detail', [CommentController::class, 'logDetail'])->name('comment.log.detail');
        Route::delete('{cid}', [CommentController::class, 'delete'])->name('comment.delete');
    });

    Route::prefix('editor')->group(function () {
        Route::post('publish', [EditorController::class, 'publish'])->name('editor.publish');
        Route::get('{type}/config', [EditorController::class, 'config'])->name('editor.config');
        // post editor
        Route::post('post/create', [EditorController::class, 'postCreate'])->name('editor.post.create');
        Route::get('post/logs', [EditorController::class, 'postLogs'])->name('editor.post.logs');
        Route::get('post/{logId}/detail', [EditorController::class, 'postDetail'])->name('editor.post.detail');
        Route::put('post/{logId}/update', [EditorController::class, 'postUpdate'])->name('editor.post.update');
        Route::post('post/{logId}/revoke', [EditorController::class, 'postRevoke'])->name('editor.post.revoke');
        Route::delete('post/{logId}/delete', [EditorController::class, 'postDelete'])->name('editor.post.delete');
        // comment editor
        Route::post('comment/create', [EditorController::class, 'commentCreate'])->name('editor.comment.create');
        Route::get('comment/logs', [EditorController::class, 'commentLogs'])->name('editor.comment.logs');
        Route::get('comment/{logId}/detail', [EditorController::class, 'commentDetail'])->name('editor.comment.detail');
        Route::put('comment/{logId}/update', [EditorController::class, 'commentUpdate'])->name('editor.comment.update');
        Route::post('comment/{logId}/revoke', [EditorController::class, 'commentRevoke'])->name('editor.comment.revoke');
        Route::delete('comment/{logId}/delete', [EditorController::class, 'commentDelete'])->name('editor.comment.delete');
        // submit
        Route::post('submit', [EditorController::class, 'submit'])->name('editor.submit');
    });
});
