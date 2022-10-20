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
use App\Fresns\Web\Http\Middleware\AccountAuthorize;
use App\Fresns\Web\Http\Middleware\CheckSiteModel;
use App\Fresns\Web\Http\Middleware\UserAuthorize;
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
        AccountAuthorize::class,
        UserAuthorize::class,
        CheckSiteModel::class,
    ])
    ->group(function () {

        // homepage
        try {
            $defaultHomepage = [sprintf('App\Fresns\Web\Http\Controllers\%sController', Str::ucfirst(fs_db_config('default_homepage'))), 'index'];
            Route::get('/', $defaultHomepage)->name('home')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
        } catch (\Throwable $e) {
        }

        // portal
        Route::get(fs_db_config('website_portal_path'), [PortalController::class, 'index'])->name('portal')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);

        // users
        Route::name('user.')->prefix(fs_db_config('website_user_path'))->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('list', [UserController::class, 'list'])->name('list')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('likes', [UserController::class, 'likes'])->name('likes');
            Route::get('dislikes', [UserController::class, 'dislikes'])->name('dislikes');
            Route::get('following', [UserController::class, 'following'])->name('following');
            Route::get('blocking', [UserController::class, 'blocking'])->name('blocking');
        });

        // groups
        Route::name('group.')->prefix(fs_db_config('website_group_path'))->group(function () {
            Route::get('/', [GroupController::class, 'index'])->name('index')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('list', [GroupController::class, 'list'])->name('list')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('likes', [GroupController::class, 'likes'])->name('likes');
            Route::get('dislikes', [GroupController::class, 'dislikes'])->name('dislikes');
            Route::get('following', [GroupController::class, 'following'])->name('following');
            Route::get('blocking', [GroupController::class, 'blocking'])->name('blocking');
        });

        // hashtags
        Route::name('hashtag.')->prefix(fs_db_config('website_hashtag_path'))->group(function () {
            Route::get('/', [HashtagController::class, 'index'])->name('index')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('list', [HashtagController::class, 'list'])->name('list')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('likes', [HashtagController::class, 'likes'])->name('likes');
            Route::get('dislikes', [HashtagController::class, 'dislikes'])->name('dislikes');
            Route::get('following', [HashtagController::class, 'following'])->name('following');
            Route::get('blocking', [HashtagController::class, 'blocking'])->name('blocking');
        });

        // posts
        Route::name('post.')->prefix(fs_db_config('website_post_path'))->group(function () {
            Route::get('/', [PostController::class, 'index'])->name('index')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('list', [PostController::class, 'list'])->name('list')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('nearby', [PostController::class, 'nearby'])->name('nearby')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('location', [PostController::class, 'location'])->name('location')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('likes', [PostController::class, 'likes'])->name('likes');
            Route::get('dislikes', [PostController::class, 'dislikes'])->name('dislikes');
            Route::get('following', [PostController::class, 'following'])->name('following');
            Route::get('blocking', [PostController::class, 'blocking'])->name('blocking');
        });

        // comments
        Route::name('comment.')->prefix(fs_db_config('website_comment_path'))->group(function () {
            Route::get('/', [CommentController::class, 'index'])->name('index')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('list', [CommentController::class, 'list'])->name('list')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('nearby', [CommentController::class, 'nearby'])->name('nearby')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('location', [CommentController::class, 'location'])->name('location')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('likes', [CommentController::class, 'likes'])->name('likes');
            Route::get('dislikes', [CommentController::class, 'dislikes'])->name('dislikes');
            Route::get('following', [CommentController::class, 'following'])->name('following');
            Route::get('blocking', [CommentController::class, 'blocking'])->name('blocking');
        });

        // detail
        Route::withoutMiddleware([AccountAuthorize::class, UserAuthorize::class])->group(function () {
            Route::get(fs_db_config('website_group_detail_path').'/{gid}', [GroupController::class, 'detail'])->name('group.detail');
            Route::get(fs_db_config('website_hashtag_detail_path').'/{hid}', [HashtagController::class, 'detail'])->name('hashtag.detail');
            Route::get(fs_db_config('website_post_detail_path').'/{pid}', [PostController::class, 'detail'])->name('post.detail');
            Route::get(fs_db_config('website_comment_detail_path').'/{cid}', [CommentController::class, 'detail'])->name('comment.detail');
        });

        // profile
        Route::name('profile.')->prefix(fs_db_config('website_user_detail_path').'/{uidOrUsername}')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class])->group(function () {
            try {
                $homeListConfig = str_replace('it_', '', fs_db_config('it_home_list'));
                $profileHome = str_replace('user_', '', $homeListConfig);
                Route::get('/', [ProfileController::class, Str::camel($profileHome)])->name('index');
            } catch (\Throwable $e) {
            }

            Route::get('posts', [ProfileController::class, 'posts'])->name('posts');
            Route::get('comments', [ProfileController::class, 'comments'])->name('comments');
            // mark records
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
            Route::get('following/users', [ProfileController::class, 'followingUsers'])->name('following.users');
            Route::get('following/groups', [ProfileController::class, 'followingGroups'])->name('following.groups');
            Route::get('following/hashtags', [ProfileController::class, 'followingHashtags'])->name('following.hashtags');
            Route::get('following/posts', [ProfileController::class, 'followingPosts'])->name('following.posts');
            Route::get('following/comments', [ProfileController::class, 'followingComments'])->name('following.comments');
            // blocking
            Route::get('blocking/users', [ProfileController::class, 'blockingUsers'])->name('blocking.users');
            Route::get('blocking/groups', [ProfileController::class, 'blockingGroups'])->name('blocking.groups');
            Route::get('blocking/hashtags', [ProfileController::class, 'blockingHashtags'])->name('blocking.hashtags');
            Route::get('blocking/posts', [ProfileController::class, 'blockingPosts'])->name('blocking.posts');
            Route::get('blocking/comments', [ProfileController::class, 'blockingComments'])->name('blocking.comments');
        });

        // search
        Route::name('search.')->prefix('search')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class])->group(function () {
            Route::get('/', [SearchController::class, 'index'])->name('index');
            Route::get('users', [SearchController::class, 'users'])->name('users');
            Route::get('groups', [SearchController::class, 'groups'])->name('groups');
            Route::get('hashtags', [SearchController::class, 'hashtags'])->name('hashtags');
            Route::get('posts', [SearchController::class, 'posts'])->name('posts');
            Route::get('comments', [SearchController::class, 'comments'])->name('comments');
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

        // account
        Route::name('account.')->prefix('account')->group(function () {
            Route::get('register', [AccountController::class, 'register'])->name('register')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class]);
            Route::get('login', [AccountController::class, 'login'])->name('login')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class, CheckSiteModel::class]);
            Route::get('logout', [AccountController::class, 'logout'])->name('logout')->withoutMiddleware([UserAuthorize::class, CheckSiteModel::class]);
            Route::get('reset-password', [AccountController::class, 'resetPassword'])->name('resetPassword')->withoutMiddleware([AccountAuthorize::class, UserAuthorize::class, CheckSiteModel::class]);
            Route::get('/', [AccountController::class, 'index'])->name('index')->withoutMiddleware([UserAuthorize::class]);
            Route::get('wallet', [AccountController::class, 'wallet'])->name('wallet')->withoutMiddleware([UserAuthorize::class]);
            Route::get('users', [AccountController::class, 'users'])->name('users')->withoutMiddleware([UserAuthorize::class]);
            Route::get('settings', [AccountController::class, 'settings'])->name('settings')->withoutMiddleware([UserAuthorize::class]);
        });

        // messages
        Route::name('message.')->prefix('messages')->group(function () {
            Route::get('/', [MessageController::class, 'index'])->name('index');
            Route::get('dialog/{dialogId}', [MessageController::class, 'dialog'])->name('dialog');
            Route::get('notify/{types}', [MessageController::class, 'notify'])->name('notify');
        });

        // editor
        Route::name('editor.')->prefix('editor')->group(function () {
            // draft box
            Route::get('drafts/{type}', [EditorController::class, 'drafts'])->name('drafts');

            // editor
            Route::get('{type}', [EditorController::class, 'index'])->name('index');
            Route::get('{type}/{draftId}', [EditorController::class, 'edit'])->name('edit');

            // editor request
            Route::post('direct-publish', [EditorController::class, 'directPublish'])->name('direct.publish');
            Route::post('store/{type}', [EditorController::class, 'store'])->name('store');
            Route::post('publish/{type}/{draftId}', [EditorController::class, 'publish'])->name('publish');
            Route::patch('recall/{type}/{draftId}', [EditorController::class, 'recall'])->name('recall');
        });
    });
