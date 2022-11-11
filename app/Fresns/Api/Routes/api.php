<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Fresns\Api\Http\Controllers\AccountController;
use App\Fresns\Api\Http\Controllers\CommentController;
use App\Fresns\Api\Http\Controllers\CommonController;
use App\Fresns\Api\Http\Controllers\ConversationController;
use App\Fresns\Api\Http\Controllers\EditorController;
use App\Fresns\Api\Http\Controllers\GlobalController;
use App\Fresns\Api\Http\Controllers\GroupController;
use App\Fresns\Api\Http\Controllers\HashtagController;
use App\Fresns\Api\Http\Controllers\NotificationController;
use App\Fresns\Api\Http\Controllers\PostController;
use App\Fresns\Api\Http\Controllers\SearchController;
use App\Fresns\Api\Http\Controllers\UserController;
use App\Fresns\Api\Http\Middleware\CheckHeader;
use App\Fresns\Api\Http\Middleware\CheckSiteModel;
use App\Fresns\Subscribe\Middleware\UserActivate;
use Illuminate\Support\Facades\Route;

Route::prefix('v2')->middleware([
    CheckHeader::class,
    CheckSiteModel::class,
    UserActivate::class,
])->group(function () {
    // global
    Route::prefix('global')->name('global.')->withoutMiddleware([CheckSiteModel::class])->group(function () {
        Route::get('configs', [GlobalController::class, 'configs'])->name('configs');
        Route::get('code-messages', [GlobalController::class, 'codeMessages'])->name('code.messages');
        Route::get('{type}/archives', [GlobalController::class, 'archives'])->name('archives');
        Route::get('upload-token', [GlobalController::class, 'uploadToken'])->name('upload.token');
        Route::get('roles', [GlobalController::class, 'roles'])->name('roles');
        Route::get('maps', [GlobalController::class, 'maps'])->name('maps');
        Route::get('{type}/content-type', [GlobalController::class, 'contentType'])->name('content.type');
        Route::get('stickers', [GlobalController::class, 'stickers'])->name('stickers');
        Route::get('block-words', [GlobalController::class, 'blockWords'])->name('block.words');
    });

    // common
    Route::prefix('common')->name('common.')->group(function () {
        Route::get('input-tips', [CommonController::class, 'inputTips'])->name('input.tips');
        Route::get('callback', [CommonController::class, 'callback'])->name('callback')->withoutMiddleware([CheckSiteModel::class]);
        Route::post('send-verify-code', [CommonController::class, 'sendVerifyCode'])->name('send.verifyCode');
        Route::post('upload-log', [CommonController::class, 'uploadLog'])->name('upload.log')->withoutMiddleware([CheckSiteModel::class]);
        Route::post('upload-file', [CommonController::class, 'uploadFile'])->name('upload.file');
        Route::get('file/{fid}/link', [CommonController::class, 'fileLink'])->name('file.link');
        Route::get('file/{fid}/users', [CommonController::class, 'fileUsers'])->name('file.users');
    });

    // search
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('users', [SearchController::class, 'users'])->name('users');
        Route::get('groups', [SearchController::class, 'groups'])->name('groups');
        Route::get('hashtags', [SearchController::class, 'hashtags'])->name('hashtags');
        Route::get('posts', [SearchController::class, 'posts'])->name('posts');
        Route::get('comments', [SearchController::class, 'comments'])->name('comments');
    });

    // account
    Route::prefix('account')->name('account.')->withoutMiddleware([CheckSiteModel::class])->group(function () {
        Route::post('register', [AccountController::class, 'register'])->name('register');
        Route::post('login', [AccountController::class, 'login'])->name('login');
        Route::put('reset-password', [AccountController::class, 'resetPassword'])->name('reset.password');
        Route::get('detail', [AccountController::class, 'detail'])->name('detail');
        Route::get('wallet-logs', [AccountController::class, 'walletLogs'])->name('wallet.logs');
        Route::post('verify-identity', [AccountController::class, 'verifyIdentity'])->name('verify.identity');
        Route::put('edit', [AccountController::class, 'edit'])->name('edit');
        Route::delete('logout', [AccountController::class, 'logout'])->name('logout');
        Route::post('apply-delete', [AccountController::class, 'applyDelete'])->name('apply.delete');
        Route::post('recall-delete', [AccountController::class, 'recallDelete'])->name('recall.delete');
    });

    // user
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('list', [UserController::class, 'list'])->name('list');
        Route::get('{uidOrUsername}/detail', [UserController::class, 'detail'])->name('detail')->withoutMiddleware([CheckSiteModel::class]);
        Route::get('{uidOrUsername}/followers-you-follow', [UserController::class, 'followersYouFollow'])->name('followers.you.follow');
        Route::get('{uidOrUsername}/interactive/{type}', [UserController::class, 'interactive'])->name('interactive');
        Route::get('{uidOrUsername}/mark/{markType}/{listType}', [UserController::class, 'markList'])->name('mark.list');
        Route::post('auth', [UserController::class, 'auth'])->name('auth')->withoutMiddleware([CheckSiteModel::class]);
        Route::get('panel', [UserController::class, 'panel'])->name('panel')->withoutMiddleware([CheckSiteModel::class]);
        Route::put('edit', [UserController::class, 'edit'])->name('edit');
        Route::post('mark', [UserController::class, 'mark'])->name('mark');
        Route::put('mark-note', [UserController::class, 'markNote'])->name('mark.note');
    });

    // notification
    Route::prefix('notification')->name('notification.')->group(function () {
        Route::get('list', [NotificationController::class, 'list'])->name('list');
        Route::put('mark-as-read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('delete', [NotificationController::class, 'delete'])->name('delete');
    });

    // conversation
    Route::prefix('conversation')->name('conversation.')->group(function () {
        Route::get('list', [ConversationController::class, 'list'])->name('list');
        Route::get('{uidOrUsername}/detail', [ConversationController::class, 'detail'])->name('detail');
        Route::get('{uidOrUsername}/messages', [ConversationController::class, 'messages'])->name('messages');
        Route::post('send-message', [ConversationController::class, 'sendMessage'])->name('send.message');
        Route::put('mark-as-read', [ConversationController::class, 'markAsRead'])->name('read');
        Route::put('pin', [ConversationController::class, 'pin'])->name('pin');
        Route::delete('delete', [ConversationController::class, 'delete'])->name('delete');
    });

    // group
    Route::prefix('group')->name('group.')->group(function () {
        Route::get('tree', [GroupController::class, 'tree'])->name('tree');
        Route::get('categories', [GroupController::class, 'categories'])->name('categories')->withoutMiddleware([CheckSiteModel::class]);
        Route::get('list', [GroupController::class, 'list'])->name('list');
        Route::get('{gid}/detail', [GroupController::class, 'detail'])->name('detail');
        Route::get('{gid}/interactive/{type}', [GroupController::class, 'interactive'])->name('interactive');
    });

    // hashtag
    Route::prefix('hashtag')->name('hashtag.')->group(function () {
        Route::get('list', [HashtagController::class, 'list'])->name('list');
        Route::get('{hid}/detail', [HashtagController::class, 'detail'])->name('detail');
        Route::get('{hid}/interactive/{type}', [HashtagController::class, 'interactive'])->name('interactive');
    });

    // post
    Route::prefix('post')->name('post.')->group(function () {
        Route::get('list', [PostController::class, 'list'])->name('list');
        Route::get('{pid}/detail', [PostController::class, 'detail'])->name('detail');
        Route::get('{pid}/interactive/{type}', [PostController::class, 'interactive'])->name('interactive');
        Route::get('{pid}/user-list', [PostController::class, 'userList'])->name('user.list');
        Route::get('{pid}/logs', [PostController::class, 'postLogs'])->name('logs');
        Route::get('{pid}/log/{logId}', [PostController::class, 'logDetail'])->name('log.detail');
        Route::delete('{pid}', [PostController::class, 'delete'])->name('delete');
        Route::get('follow/{type}', [PostController::class, 'follow'])->name('follow');
        Route::get('nearby', [PostController::class, 'nearby'])->name('nearby');
    });

    // comment
    Route::prefix('comment')->name('comment.')->group(function () {
        Route::get('list', [CommentController::class, 'list'])->name('list');
        Route::get('{cid}/detail', [CommentController::class, 'detail'])->name('detail');
        Route::get('{cid}/interactive/{type}', [CommentController::class, 'interactive'])->name('interactive');
        Route::get('{cid}/logs', [CommentController::class, 'commentLogs'])->name('logs');
        Route::get('{cid}/log/{logId}', [CommentController::class, 'logDetail'])->name('log.detail');
        Route::delete('{cid}', [CommentController::class, 'delete'])->name('delete');
        Route::get('follow/{type}', [CommentController::class, 'follow'])->name('follow');
        Route::get('nearby', [CommentController::class, 'nearby'])->name('nearby');
    });

    // editor
    Route::prefix('editor')->name('editor.')->group(function () {
        Route::post('{type}/quick-publish', [EditorController::class, 'quickPublish'])->name('quick.publish');
        Route::get('{type}/config', [EditorController::class, 'config'])->name('config');
        Route::get('{type}/drafts', [EditorController::class, 'drafts'])->name('drafts');
        Route::post('{type}/create', [EditorController::class, 'create'])->name('create');
        Route::post('{type}/generate/{fsid}', [EditorController::class, 'generate'])->name('generate');
        Route::get('{type}/{draftId}', [EditorController::class, 'detail'])->name('detail');
        Route::put('{type}/{draftId}', [EditorController::class, 'update'])->name('update');
        Route::post('{type}/{draftId}', [EditorController::class, 'publish'])->name('publish');
        Route::patch('{type}/{draftId}', [EditorController::class, 'recall'])->name('recall');
        Route::delete('{type}/{draftId}', [EditorController::class, 'delete'])->name('delete');
    });
});
