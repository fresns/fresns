<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Fresns\Web\Http\Controllers\AccountController;
use App\Fresns\Web\Http\Controllers\CommentController;
use App\Fresns\Web\Http\Controllers\EditorController;
use App\Fresns\Web\Http\Controllers\FollowController;
use App\Fresns\Web\Http\Controllers\GroupController;
use App\Fresns\Web\Http\Controllers\HashtagController;
use App\Fresns\Web\Http\Controllers\MessageController;
use App\Fresns\Web\Http\Controllers\PortalController;
use App\Fresns\Web\Http\Controllers\PostController;
use App\Fresns\Web\Http\Controllers\ProfileController;
use App\Fresns\Web\Http\Controllers\SearchController;
use App\Fresns\Web\Http\Controllers\UserController;
use App\Fresns\Web\Http\Middleware\WebConfiguration;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;

Route::prefix(LaravelLocalization::setLocale())
    ->middleware([
        'web',
        WebConfiguration::class,
        LaravelLocalizationRedirectFilter::class,
        // AccountAuthorize::class,
        // UserAuthorize::class,
    ])
    ->group(function () {

    // homepage
    try {
        $groupCallable = [sprintf('App\Fresns\Web\Http\Controllers\%sController', Str::ucfirst(fs_db_config('default_homepage'))), 'index'];
        // dd(is_callable($groupCallable), $groupCallable);
        Route::get('/', $groupCallable)->name('home');
    } catch (\Throwable $e) {
    }

    // portal
    Route::get(fs_db_config('website_portal_path'), [PortalController::class, 'portal'])->name('portal')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);

    // users
    Route::name('user.')->prefix(fs_db_config('website_user_path'))->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('list', [UserController::class, 'list'])->name('list')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('likes', [UserController::class, 'likes'])->name('likes');
        Route::get('dislikes', [UserController::class, 'dislikes'])->name('dislikes');
        Route::get('following', [UserController::class, 'following'])->name('following');
        Route::get('blocking', [UserController::class, 'blocking'])->name('blocking');
    });

    // groups
    Route::name('group.')->prefix(fs_db_config('website_group_path'))->group(function () {
        Route::get('/', [GroupController::class, 'index'])->name('index')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('list', [GroupController::class, 'list'])->name('list')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('likes', [GroupController::class, 'likes'])->name('likes');
        Route::get('dislikes', [GroupController::class, 'dislikes'])->name('dislikes');
        Route::get('following', [GroupController::class, 'following'])->name('following');
        Route::get('blocking', [GroupController::class, 'blocking'])->name('blocking');
    });

    // hashtags
    Route::name('hashtag.')->prefix(fs_db_config('website_hashtag_path'))->group(function () {
        Route::get('/', [HashtagController::class, 'index'])->name('index')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('list', [HashtagController::class, 'list'])->name('list')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('likes', [HashtagController::class, 'likes'])->name('likes');
        Route::get('dislikes', [HashtagController::class, 'dislikes'])->name('dislikes');
        Route::get('following', [HashtagController::class, 'following'])->name('following');
        Route::get('blocking', [HashtagController::class, 'blocking'])->name('blocking');
    });

    // posts
    Route::name('post.')->prefix(fs_db_config('website_post_path'))->group(function () {
        Route::get('/', [PostController::class, 'index'])->name('index')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('list', [PostController::class, 'list'])->name('list')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('nearby', [PostController::class, 'nearby'])->name('nearby')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('location', [PostController::class, 'location'])->name('location')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('likes', [PostController::class, 'likes'])->name('likes');
        Route::get('dislikes', [PostController::class, 'dislikes'])->name('dislikes');
        Route::get('following', [PostController::class, 'following'])->name('following');
        Route::get('blocking', [PostController::class, 'blocking'])->name('blocking');
    });

    // comment
    Route::name('comment.')->prefix(fs_db_config('website_comment_path'))->group(function () {
        Route::get('/', [CommentController::class, 'index'])->name('index')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('list', [CommentController::class, 'list'])->name('list')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('nearby', [CommentController::class, 'nearby'])->name('nearby')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('location', [CommentController::class, 'location'])->name('location')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('likes', [CommentController::class, 'likes'])->name('likes');
        Route::get('dislikes', [CommentController::class, 'dislikes'])->name('dislikes');
        Route::get('following', [CommentController::class, 'following'])->name('following');
        Route::get('blocking', [CommentController::class, 'blocking'])->name('blocking');
    });

    // follow
    Route::name('follow.')->prefix('follow')->group(function () {
        Route::get('all/posts', [FollowController::class, 'allPosts'])->name('all.posts');
        Route::get('user/posts', [FollowController::class, 'userPosts'])->name('user.posts');
        Route::get('group/posts', [FollowController::class, 'groupPosts'])->name('group.posts');
        Route::get('hashtag/posts', [FollowController::class, 'hashtagPosts'])->name('hashtag.posts');
        Route::get('all/comments', [FollowController::class, 'allComments'])->name('all.comments');
        Route::get('user/comments', [FollowController::class, 'userComments'])->name('user.comments');
        Route::get('group/comments', [FollowController::class, 'groupComments'])->name('group.comments');
        Route::get('hashtag/comments', [FollowController::class, 'hashtagComments'])->name('hashtag.comments');
    });

    // search
    Route::name('search.')->prefix('search')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize'])->group(function () {
        Route::get('/', [SearchController::class, 'index'])->name('index');
        Route::get('users', [SearchController::class, 'users'])->name('users');
        Route::get('groups', [SearchController::class, 'groups'])->name('groups');
        Route::get('hashtags', [SearchController::class, 'hashtags'])->name('hashtags');
        Route::get('posts', [SearchController::class, 'posts'])->name('posts');
        Route::get('comments', [SearchController::class, 'comments'])->name('comments');
    });

    // detail
    Route::withoutMiddleware(['AccountAuthorize', 'UserAuthorize'])->group(function () {
        Route::get(fs_db_config('website_group_detail_path').'/{gid}', [GroupController::class, 'detail'])->name('group.detail');
        Route::get(fs_db_config('website_hashtag_detail_path').'/{hid}', [HashtagController::class, 'detail'])->name('hashtag.detail');
        Route::get(fs_db_config('website_post_detail_path').'/{pid}', [PostController::class, 'detail'])->name('post.detail');
        Route::get(fs_db_config('website_comment_detail_path').'/{cid}', [CommentController::class, 'detail'])->name('comment.detail');
    });

    // messages
    Route::name('message.')->prefix('messages')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('dialog/{dialogId}', [MessageController::class, 'dialog'])->name('dialog');
        Route::get('notify/{type}', [MessageController::class, 'notify'])->name('notify');
    });

    // account auth
    Route::name('account.')->prefix('account')->group(function () {
        Route::get('register', [AccountController::class, 'register'])->name('register')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize']);
        Route::get('login', [AccountController::class, 'login'])->name('login')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize', 'CheckSiteModel']);
        Route::get('reset', [AccountController::class, 'reset'])->name('reset')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize', 'CheckSiteModel']);
        Route::get('/', [AccountController::class, 'index'])->name('index')->withoutMiddleware(['UserAuthorize']);
        Route::get('user-switch', [AccountController::class, 'userSwitch'])->name('user.switch')->withoutMiddleware(['UserAuthorize']);
        Route::get('user-auth', [AccountController::class, 'userAuth'])->name('user.auth')->withoutMiddleware(['UserAuthorize']);
        Route::get('wallet', [AccountController::class, 'wallet'])->name('wallet')->withoutMiddleware(['UserAuthorize']);
        Route::get('users', [AccountController::class, 'users'])->name('users')->withoutMiddleware(['UserAuthorize']);
        Route::get('settings', [AccountController::class, 'settings'])->name('settings')->withoutMiddleware(['UserAuthorize']);
        Route::get('logout', [AccountController::class, 'logout'])->name('logout')->withoutMiddleware(['CheckSiteModel']);
    });

    // profile
    Route::name('profile.')->prefix(fs_db_config('website_user_detail_path').'/{uidOrUsername}')->withoutMiddleware(['AccountAuthorize', 'UserAuthorize'])->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('posts', [ProfileController::class, 'posts'])->name('posts');
        Route::get('comments', [ProfileController::class, 'comments'])->name('comments');
        // mark
        Route::get('likers', [ProfileController::class, 'likers'])->name('likers');
        Route::get('dislikers', [ProfileController::class, 'dislikers'])->name('dislikers');
        Route::get('followers', [ProfileController::class, 'followers'])->name('followers');
        Route::get('blockers', [ProfileController::class, 'blockers'])->name('blockers');
        // likers
        Route::get('likes/users', [ProfileController::class, 'likeUsers'])->name('likes.users');
        Route::get('likes/groups', [ProfileController::class, 'likeGroups'])->name('likes.groups');
        Route::get('likes/hashtags', [ProfileController::class, 'likeHashtags'])->name('likes.hashtags');
        Route::get('likes/posts', [ProfileController::class, 'likePosts'])->name('likes.posts');
        Route::get('likes/comments', [ProfileController::class, 'likeComments'])->name('likes.comments');
        // dislikes
        Route::get('dislikes/users', [ProfileController::class, 'dislikeUsers'])->name('dislikes.users');
        Route::get('dislikes/groups', [ProfileController::class, 'dislikeGroups'])->name('dislikes.groups');
        Route::get('dislikes/hashtags', [ProfileController::class, 'dislikeHashtags'])->name('dislikes.hashtags');
        Route::get('dislikes/posts', [ProfileController::class, 'dislikePosts'])->name('dislikes.posts');
        Route::get('dislikes/comments', [ProfileController::class, 'dislikeComments'])->name('dislikes.comments');
        // following
        Route::get('following/users', [ProfileController::class, 'followUsers'])->name('following.users');
        Route::get('following/groups', [ProfileController::class, 'followGroups'])->name('following.groups');
        Route::get('following/hashtags', [ProfileController::class, 'followHashtags'])->name('following.hashtags');
        Route::get('following/posts', [ProfileController::class, 'followPosts'])->name('following.posts');
        Route::get('following/comments', [ProfileController::class, 'followComments'])->name('following.comments');
        // blocking
        Route::get('blocking/users', [ProfileController::class, 'blockUsers'])->name('blocking.users');
        Route::get('blocking/groups', [ProfileController::class, 'blockGroups'])->name('blocking.groups');
        Route::get('blocking/hashtags', [ProfileController::class, 'blockHashtags'])->name('blocking.hashtags');
        Route::get('blocking/posts', [ProfileController::class, 'blockPosts'])->name('blocking.posts');
        Route::get('blocking/comments', [ProfileController::class, 'blockComments'])->name('blocking.comments');
    });

    // editor
    Route::name('editor.')->prefix('editor')->group(function () {
        Route::get('drafts', [EditorController::class, 'drafts'])->name('drafts');
        Route::get('editor', [EditorController::class, 'editor'])->name('editor');
    });
});
