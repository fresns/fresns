<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use App\Fresns\Api\Http\Controllers\AccountController;
use App\Fresns\Api\Http\Controllers\CommentController;
use App\Fresns\Api\Http\Controllers\CommonController;
use App\Fresns\Api\Http\Controllers\ConversationController;
use App\Fresns\Api\Http\Controllers\EditorController;
use App\Fresns\Api\Http\Controllers\FileController;
use App\Fresns\Api\Http\Controllers\GeotagController;
use App\Fresns\Api\Http\Controllers\GlobalController;
use App\Fresns\Api\Http\Controllers\GroupController;
use App\Fresns\Api\Http\Controllers\HashtagController;
use App\Fresns\Api\Http\Controllers\NotificationController;
use App\Fresns\Api\Http\Controllers\PostController;
use App\Fresns\Api\Http\Controllers\SearchController;
use App\Fresns\Api\Http\Controllers\UserController;
use App\Fresns\Api\Http\Middleware\CheckHeaderByWhitelist;
use App\Fresns\Api\Http\Middleware\CheckReadOnly;
use App\Fresns\Api\Http\Middleware\CheckSiteMode;
use Illuminate\Support\Facades\Route;

Route::prefix('fresns/v1')->middleware([
    CheckHeaderByWhitelist::class,
    CheckSiteMode::class,
    CheckReadOnly::class,
])->group(function () {
    // global
    Route::prefix('global')->name('global.')->withoutMiddleware([CheckSiteMode::class])->group(function () {
        Route::get('configs', [GlobalController::class, 'configs'])->name('configs');
        Route::get('language-pack', [GlobalController::class, 'languagePack'])->name('language.pack');
        Route::get('channels', [GlobalController::class, 'channels'])->name('channels');
        Route::get('{type}/archives', [GlobalController::class, 'archives'])->name('archives');
        Route::get('{type}/content-types', [GlobalController::class, 'contentTypes'])->name('content.types');
        Route::get('roles', [GlobalController::class, 'roles'])->name('roles');
        Route::get('stickers', [GlobalController::class, 'stickers'])->name('stickers');
    });

    // common
    Route::prefix('common')->name('common.')->group(function () {
        Route::get('ip-info', [CommonController::class, 'ipInfo'])->name('ip.info')->withoutMiddleware([CheckSiteMode::class]);
        Route::get('input-tips', [CommonController::class, 'inputTips'])->name('input.tips');
        Route::get('callback', [CommonController::class, 'callback'])->name('callback')->withoutMiddleware([CheckSiteMode::class]);
        Route::post('cmd-word', [CommonController::class, 'cmdWord'])->name('cmd.word');
    });

    // file
    Route::prefix('file')->name('file.')->group(function () {
        Route::get('storage-token', [FileController::class, 'storageToken'])->name('storage.token');
        Route::post('uploads', [FileController::class, 'uploads'])->name('uploads');
        Route::patch('{fid}/warning', [FileController::class, 'warning'])->name('warning');
        Route::get('{fid}/link', [FileController::class, 'link'])->name('link');
        Route::get('{fid}/users', [FileController::class, 'users'])->name('users');
    });

    // search
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('users', [SearchController::class, 'users'])->name('users');
        Route::get('groups', [SearchController::class, 'groups'])->name('groups');
        Route::get('geotags', [SearchController::class, 'geotags'])->name('geotags');
        Route::get('hashtags', [SearchController::class, 'hashtags'])->name('hashtags');
        Route::get('posts', [SearchController::class, 'posts'])->name('posts');
        Route::get('comments', [SearchController::class, 'comments'])->name('comments');
    });

    // account
    Route::prefix('account')->name('account.')->withoutMiddleware([CheckSiteMode::class])->group(function () {
        Route::post('auth-token', [AccountController::class, 'login'])->name('login');
        Route::delete('auth-token', [AccountController::class, 'logout'])->name('logout');
        Route::get('detail', [AccountController::class, 'detail'])->name('detail');
        Route::get('wallet-records', [AccountController::class, 'walletRecords'])->name('wallet.records');
    });

    // user
    Route::prefix('user')->name('user.')->group(function () {
        // function
        Route::post('auth-token', [UserController::class, 'auth'])->name('auth')->withoutMiddleware([CheckSiteMode::class]);
        Route::get('overview', [UserController::class, 'overview'])->name('overview')->withoutMiddleware([CheckSiteMode::class]);
        Route::get('extcredits-records', [UserController::class, 'extcreditsRecords'])->name('extcredits.records')->withoutMiddleware([CheckSiteMode::class]);
        Route::patch('profile', [UserController::class, 'edit'])->name('edit');
        Route::post('mark', [UserController::class, 'mark'])->name('mark');
        Route::patch('mark-note', [UserController::class, 'markNote'])->name('mark.note');
        Route::post('extend-action', [UserController::class, 'extendAction'])->name('extend.action');
        // interactive
        Route::get('list', [UserController::class, 'list'])->name('list');
        Route::get('{uidOrUsername}/detail', [UserController::class, 'detail'])->name('detail')->withoutMiddleware([CheckSiteMode::class]);
        Route::get('{uidOrUsername}/followers-you-follow', [UserController::class, 'followersYouFollow'])->name('followers.you.follow');
        Route::get('{uidOrUsername}/interaction/{type}', [UserController::class, 'interaction'])->name('interaction');
        Route::get('{uidOrUsername}/mark/{markType}/{listType}', [UserController::class, 'markList'])->name('mark.list');
    });

    // notification
    Route::prefix('notification')->name('notification.')->withoutMiddleware([CheckSiteMode::class])->group(function () {
        Route::get('list', [NotificationController::class, 'list'])->name('list');
        Route::patch('read-status', [NotificationController::class, 'readStatus'])->name('read');
        Route::delete('delete', [NotificationController::class, 'delete'])->name('delete');
    });

    // conversation
    Route::prefix('conversation')->name('conversation.')->group(function () {
        Route::get('list', [ConversationController::class, 'list'])->name('list')->withoutMiddleware([CheckSiteMode::class]);
        Route::get('{uidOrUsername}/detail', [ConversationController::class, 'detail'])->name('detail');
        Route::get('{uidOrUsername}/messages', [ConversationController::class, 'messages'])->name('messages');
        Route::put('{uidOrUsername}/pin', [ConversationController::class, 'pin'])->name('pin');
        Route::patch('{uidOrUsername}/read-status', [ConversationController::class, 'readStatus'])->name('read')->withoutMiddleware([CheckSiteMode::class]);
        Route::delete('{uidOrUsername}/messages', [ConversationController::class, 'deleteMessages'])->name('delete.messages');
        Route::delete('{uidOrUsername}', [ConversationController::class, 'deleteConversation'])->name('delete.conversation');
        Route::post('message', [ConversationController::class, 'sendMessage'])->name('send.message');
    });

    // group
    Route::prefix('group')->name('group.')->withoutMiddleware([CheckSiteMode::class])->group(function () {
        Route::get('tree', [GroupController::class, 'tree'])->name('tree');
        Route::get('list', [GroupController::class, 'list'])->name('list');
        Route::get('{gid}/detail', [GroupController::class, 'detail'])->name('detail');
        Route::get('{gid}/creator', [GroupController::class, 'creator'])->name('creator');
        Route::get('{gid}/admins', [GroupController::class, 'admins'])->name('admins');
        Route::get('{gid}/interaction/{type}', [GroupController::class, 'interaction'])->name('interaction')->middleware([CheckSiteMode::class]);
    });

    // hashtag
    Route::prefix('hashtag')->name('hashtag.')->group(function () {
        Route::get('list', [HashtagController::class, 'list'])->name('list');
        Route::get('{htid}/detail', [HashtagController::class, 'detail'])->name('detail');
        Route::get('{htid}/interaction/{type}', [HashtagController::class, 'interaction'])->name('interaction');
    });

    // geotag
    Route::prefix('geotag')->name('geotag.')->group(function () {
        Route::get('list', [GeotagController::class, 'list'])->name('list');
        Route::get('{gtid}/detail', [GeotagController::class, 'detail'])->name('detail');
        Route::get('{gtid}/interaction/{type}', [GeotagController::class, 'interaction'])->name('interaction');
    });

    // post
    Route::prefix('post')->name('post.')->group(function () {
        Route::get('list', [PostController::class, 'list'])->name('list');
        Route::get('timelines', [PostController::class, 'timelines'])->name('timelines');
        Route::get('nearby', [PostController::class, 'nearby'])->name('nearby');
        Route::get('{pid}/detail', [PostController::class, 'detail'])->name('detail');
        Route::get('{pid}/interaction/{type}', [PostController::class, 'interaction'])->name('interaction');
        Route::get('{pid}/users', [PostController::class, 'users'])->name('users');
        Route::get('{pid}/quotes', [PostController::class, 'quotes'])->name('quotes');
        Route::delete('{pid}', [PostController::class, 'delete'])->name('delete');
        Route::get('{pid}/histories', [PostController::class, 'histories'])->name('histories');
        Route::get('history/{hpid}/detail', [PostController::class, 'logDetail'])->name('history.detail');
    });

    // comment
    Route::prefix('comment')->name('comment.')->group(function () {
        Route::get('list', [CommentController::class, 'list'])->name('list');
        Route::get('timelines', [CommentController::class, 'timelines'])->name('timelines');
        Route::get('nearby', [CommentController::class, 'nearby'])->name('nearby');
        Route::get('{cid}/detail', [CommentController::class, 'detail'])->name('detail');
        Route::get('{cid}/interaction/{type}', [CommentController::class, 'interaction'])->name('interaction');
        Route::delete('{cid}', [CommentController::class, 'delete'])->name('delete');
        Route::get('{cid}/histories', [CommentController::class, 'histories'])->name('histories');
        Route::get('history/{hpid}/detail', [CommentController::class, 'logDetail'])->name('history.detail');
    });

    // editor
    Route::prefix('editor')->name('editor.')->group(function () {
        Route::get('{type}/configs', [EditorController::class, 'configs'])->name('configs'); // Editor Configs
        Route::post('{type}/publish', [EditorController::class, 'publish'])->name('publish'); // Quick Publish
        Route::post('{type}/edit/{fsid}', [EditorController::class, 'edit'])->name('edit'); // Edit Post or Comment
        Route::post('{type}/draft', [EditorController::class, 'draftCreate'])->name('draft.create'); // Create Draft
        Route::get('{type}/drafts', [EditorController::class, 'draftList'])->name('draft.list'); // Draft List
        Route::get('{type}/draft/{did}', [EditorController::class, 'draftDetail'])->name('draft.detail'); // Draft Detail
        Route::put('{type}/draft/{did}', [EditorController::class, 'draftUpdate'])->name('draft.update'); // Update Draft
        Route::post('{type}/draft/{did}', [EditorController::class, 'draftPublish'])->name('draft.publish'); // Publish Draft
        Route::patch('{type}/draft/{did}', [EditorController::class, 'draftRecall'])->name('draft.recall'); // Recall Draft (Draft under review)
        Route::delete('{type}/draft/{did}', [EditorController::class, 'draftDelete'])->name('draft.delete'); // Delete Draft
    });
});
