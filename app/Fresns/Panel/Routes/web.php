<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use App\Fresns\Panel\Http\Controllers\AccountController;
use App\Fresns\Panel\Http\Controllers\AdminController;
use App\Fresns\Panel\Http\Controllers\AppController;
use App\Fresns\Panel\Http\Controllers\AppKeyController;
use App\Fresns\Panel\Http\Controllers\AppManageController;
use App\Fresns\Panel\Http\Controllers\AppUsageController;
use App\Fresns\Panel\Http\Controllers\ChannelController;
use App\Fresns\Panel\Http\Controllers\ClientController;
use App\Fresns\Panel\Http\Controllers\CodeMessageController;
use App\Fresns\Panel\Http\Controllers\CommonController;
use App\Fresns\Panel\Http\Controllers\ContentController;
use App\Fresns\Panel\Http\Controllers\DashboardController;
use App\Fresns\Panel\Http\Controllers\ExtendController;
use App\Fresns\Panel\Http\Controllers\GeneralController;
use App\Fresns\Panel\Http\Controllers\GroupController;
use App\Fresns\Panel\Http\Controllers\InteractionController;
use App\Fresns\Panel\Http\Controllers\LanguageController;
use App\Fresns\Panel\Http\Controllers\LanguagePackController;
use App\Fresns\Panel\Http\Controllers\LoginController;
use App\Fresns\Panel\Http\Controllers\PolicyController;
use App\Fresns\Panel\Http\Controllers\PublishController;
use App\Fresns\Panel\Http\Controllers\RoleController;
use App\Fresns\Panel\Http\Controllers\SendController;
use App\Fresns\Panel\Http\Controllers\SettingController;
use App\Fresns\Panel\Http\Controllers\StickerController;
use App\Fresns\Panel\Http\Controllers\StickerGroupController;
use App\Fresns\Panel\Http\Controllers\StorageController;
use App\Fresns\Panel\Http\Controllers\UpgradeController;
use App\Fresns\Panel\Http\Controllers\UserController;
use App\Fresns\Panel\Http\Controllers\WalletController;
use App\Helpers\CacheHelper;
use App\Helpers\PrimaryHelper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

try {
    $itemData = PrimaryHelper::fresnsModelByFsid('config', 'panel_configs');

    $loginPath = $itemData?->item_value['path'] ?? 'admin';
} catch (\Exception $e) {
    $loginPath = 'admin';
}

Route::get($loginPath, [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post($loginPath, [LoginController::class, 'login'])->name('login');

Route::middleware(['panelAuth'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // fresns upgrade
    Route::post('auto-upgrade', [UpgradeController::class, 'autoUpgrade'])->name('upgrade.auto');
    Route::post('manual-upgrade', [UpgradeController::class, 'manualUpgrade'])->name('upgrade.manual');
    Route::get('upgrade/info', [UpgradeController::class, 'upgradeInfo'])->name('upgrade.info');

    // update config
    Route::prefix('update')->name('update.')->group(function () {
        Route::put('languages/{itemKey}', [CommonController::class, 'updateLanguages'])->name('languages'); // all
        Route::patch('language/{itemKey}/{langTag}', [CommonController::class, 'updateLanguage'])->name('language'); // monolingual
        Route::patch('item/{itemKey}', [CommonController::class, 'updateItem'])->name('item'); // config item
    });
    // search
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('users', [CommonController::class, 'searchUsers'])->name('users');
        Route::get('groups', [CommonController::class, 'searchGroups'])->name('groups');
    });

    // dashboard-home
    Route::get('dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::get('dashboard-data', [DashboardController::class, 'dashboardData'])->name('dashboard.data');
    Route::get('composer/diagnose', [DashboardController::class, 'composerDiagnose'])->name('composer.diagnose');
    Route::get('composer/config', [DashboardController::class, 'composerConfigInfo'])->name('composer.config');
    // dashboard-events
    Route::get('events', [DashboardController::class, 'eventList'])->name('events.index');
    // dashboard-caches
    Route::get('caches', [SettingController::class, 'caches'])->name('caches.index');
    Route::any('cache/all/clear', [SettingController::class, 'cacheAllClear'])->name('cache.all.clear');
    Route::post('cache/select/clear', [SettingController::class, 'cacheSelectClear'])->name('cache.select.clear');
    // dashboard-upgrades
    Route::get('upgrades', [UpgradeController::class, 'show'])->name('upgrades');
    Route::patch('upgrade/check', [UpgradeController::class, 'checkFresnsVersion'])->name('upgrade.check');
    // dashboard-admins
    Route::resource('admins', AdminController::class)->only([
        'index', 'store', 'destroy',
    ]);
    // dashboard-settings
    Route::get('settings', [SettingController::class, 'show'])->name('settings');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    // systems
    Route::prefix('systems')->group(function () {
        // languages
        Route::get('languages', [LanguageController::class, 'index'])->name('languages.index');
        Route::post('languageMenus', [LanguageController::class, 'store'])->name('languageMenus.store');
        Route::put('languageMenus/update-default', [LanguageController::class, 'updateDefaultLanguage'])->name('languageMenus.default.update');
        Route::put('languageMenus/{langTag}', [LanguageController::class, 'update'])->name('languageMenus.update');
        Route::patch('languageMenus/{langTag}/order', [LanguageController::class, 'updateOrder'])->name('languageMenus.order.update');
        Route::delete('languageMenus/{langTag}', [LanguageController::class, 'destroy'])->name('languageMenus.destroy');
        // storage
        Route::get('storage/image', [StorageController::class, 'imageShow'])->name('storage.image.index');
        Route::put('storage/image', [StorageController::class, 'imageUpdate'])->name('storage.image.update');
        Route::get('storage/video', [StorageController::class, 'videoShow'])->name('storage.video.index');
        Route::put('storage/video', [StorageController::class, 'videoUpdate'])->name('storage.video.update');
        Route::get('storage/audio', [StorageController::class, 'audioShow'])->name('storage.audio.index');
        Route::put('storage/audio', [StorageController::class, 'audioUpdate'])->name('storage.audio.update');
        Route::get('storage/document', [StorageController::class, 'documentShow'])->name('storage.document.index');
        Route::put('storage/document', [StorageController::class, 'documentUpdate'])->name('storage.document.update');
        Route::get('storage/substitution', [StorageController::class, 'substitutionShow'])->name('storage.substitution.index');
        Route::put('storage/substitution', [StorageController::class, 'substitutionUpdate'])->name('storage.substitution.update');
        // general
        Route::get('general', [GeneralController::class, 'show'])->name('general.index');
        Route::put('general', [GeneralController::class, 'update'])->name('general.update');
        // policy
        Route::get('policy', [PolicyController::class, 'show'])->name('policy.index');
        Route::put('policy', [PolicyController::class, 'update'])->name('policy.update');
        // send
        Route::get('send', [SendController::class, 'show'])->name('send.index');
        Route::put('send', [SendController::class, 'update'])->name('send.update');
        Route::put('send/verifyCodeTemplate/{itemKey}/sms', [SendController::class, 'updateSms'])->name('send.sms.update');
        Route::put('send/verifyCodeTemplate/{itemKey}/email', [SendController::class, 'updateEmail'])->name('send.email.update');
        // account
        Route::get('account', [AccountController::class, 'show'])->name('account.index');
        Route::put('account', [AccountController::class, 'update'])->name('account.update');
        // wallet
        Route::get('wallet', [WalletController::class, 'show'])->name('wallet.index');
        Route::put('wallet', [WalletController::class, 'update'])->name('wallet.update');
    });

    // operations
    Route::prefix('operations')->group(function () {
        // user
        Route::get('user', [UserController::class, 'show'])->name('user.index');
        Route::put('user', [UserController::class, 'update'])->name('user.update');
        // content
        Route::get('content', [ContentController::class, 'show'])->name('content.index');
        Route::put('content', [ContentController::class, 'update'])->name('content.update');
        // interaction
        Route::get('interaction', [InteractionController::class, 'show'])->name('interaction.index');
        // publish-post
        Route::get('publish/post', [PublishController::class, 'postShow'])->name('publish.post.index');
        Route::put('publish/post', [PublishController::class, 'postUpdate'])->name('publish.post.update');
        // publish-comment
        Route::get('publish/comment', [PublishController::class, 'commentShow'])->name('publish.comment.index');
        Route::put('publish/comment', [PublishController::class, 'commentUpdate'])->name('publish.comment.update');
        // roles
        Route::resource('roles', RoleController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        Route::patch('roles/{role}/order', [RoleController::class, 'updateSortOrder'])->name('roles.order');
        Route::get('roles/{role}/permissions', [RoleController::class, 'showPermissions'])->name('roles.permissions.index');
        Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');
        // stickers
        Route::resource('stickers', StickerGroupController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        Route::patch('stickers/{sticker}/order', [StickerGroupController::class, 'updateSortOrder'])->name('stickers.order');
        Route::put('sticker-images/batch', [StickerController::class, 'batchUpdate'])->name('sticker-images.batch.update');
        Route::resource('sticker-images', StickerController::class)->only([
            'index', 'store', 'update', 'destroy',
        ])->parameters([
            'sticker-images' => 'stickerImage',
        ]);
        // groups
        Route::resource('groups', GroupController::class)->only([
            'index', 'store', 'update',
        ]);
        Route::patch('groups/{group}/order', [GroupController::class, 'updateSortOrder'])->name('groups.order');
        Route::patch('groups/{group}/recommend-order', [GroupController::class, 'updateRecommendSortOrder'])->name('groups.recommend-order');
        Route::patch('groups/{group}/status', [GroupController::class, 'updateStatus'])->name('groups.status');
        Route::put('groups/{group}/merge', [GroupController::class, 'mergeGroup'])->name('groups.merge');
    });

    // extends
    Route::prefix('extends')->group(function () {
        // content-handler
        Route::get('content-handler', [ExtendController::class, 'contentHandlerIndex'])->name('content-handler.index');
        Route::put('content-handler', [ExtendController::class, 'contentHandlerUpdate'])->name('content-handler.update');
        // command-words
        Route::get('command-words', [ExtendController::class, 'commandWordsIndex'])->name('command-words.index');
        Route::post('command-words', [ExtendController::class, 'commandWordsStore'])->name('command-words.store');
        Route::delete('command-words', [ExtendController::class, 'commandWordsDestroy'])->name('command-words.destroy');
    });

    // app usages
    Route::prefix('app-usages')->name('app-usages.')->group(function () {
        Route::get('{usageType}', [AppUsageController::class, 'show'])->name('index');
        Route::post('{usageType}/store', [AppUsageController::class, 'store'])->name('store');
        Route::put('update/{id}', [AppUsageController::class, 'update'])->name('update');
        Route::delete('destroy/{id}', [AppUsageController::class, 'destroy'])->name('destroy');
        Route::patch('order/{id}', [AppUsageController::class, 'updateOrder'])->name('update-order');
    });

    // clients
    Route::prefix('clients')->group(function () {
        // channels
        Route::get('channels', [ChannelController::class, 'index'])->name('channels.index');
        Route::put('channels/{type}', [ChannelController::class, 'update'])->name('channels.update');
        // language pack
        Route::resource('language-packs', LanguagePackController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        // code messages
        Route::resource('code-messages', CodeMessageController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        // path
        Route::get('paths', [ClientController::class, 'pathIndex'])->name('paths.index');
        Route::put('paths', [ClientController::class, 'pathUpdate'])->name('paths.update');
        // app key
        Route::resource('keys', AppKeyController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        Route::patch('keys/{key}/reset', [AppKeyController::class, 'reset'])->name('keys.reset');
        // basic
        Route::get('basic', [ClientController::class, 'basicIndex'])->name('client.basic');
        Route::put('basic', [ClientController::class, 'basicUpdate'])->name('client.basic.update');
        // status
        Route::get('status', [ClientController::class, 'statusIndex'])->name('client.status');
        Route::put('status', [ClientController::class, 'statusUpdate'])->name('client.status.update');
        // web engine
        Route::put('web-engine', [ClientController::class, 'engineUpdate'])->name('client.engine.update');
    });

    // app center
    Route::prefix('app-center')->name('app-center.')->group(function () {
        // apps
        Route::get('plugins', [AppController::class, 'plugins'])->name('plugins');
        Route::get('themes', [AppController::class, 'themes'])->name('themes');
        Route::get('apps', [AppController::class, 'apps'])->name('apps');
        // marketplace
        Route::get('marketplace', [AppController::class, 'iframe'])->name('marketplace');
        // install
        Route::put('install', [AppController::class, 'install'])->name('install');
        // settings
        Route::get('settings', [AppController::class, 'iframe'])->name('plugin.settings');
        Route::get('functions', [AppController::class, 'iframe'])->name('theme.functions');
    });

    // plugin manage
    Route::prefix('plugin')->name('plugin.')->group(function () {
        // dashboard upgrade page
        Route::patch('update-code', [AppManageController::class, 'updateCode'])->name('update.code');
        // check status
        Route::post('check-status', [AppManageController::class, 'pluginCheckStatus'])->name('check.status');
        // plugin upgrade
        Route::put('upgrade', [AppManageController::class, 'pluginUpgrade'])->name('upgrade');
        // activate or deactivate
        Route::patch('update', [AppManageController::class, 'pluginUpdate'])->name('update');
        // uninstall
        Route::delete('uninstall', [AppManageController::class, 'pluginUninstall'])->name('uninstall');
    });

    // theme manage
    Route::prefix('theme')->name('theme.')->group(function () {
        Route::put('upgrade', [AppManageController::class, 'themeUpgrade'])->name('upgrade');
        Route::delete('uninstall', [AppManageController::class, 'themeUninstall'])->name('uninstall');
    });

    // app manage
    Route::prefix('app')->name('app.')->group(function () {
        Route::post('download', [AppManageController::class, 'appDownload'])->name('download');
        Route::delete('delete', [AppManageController::class, 'appDelete'])->name('delete');
    });
});

// FsLang
Route::get('js/{locale?}/translations', function ($locale) {
    $panelLangCacheKey = "fresns_panel_translation_{$locale}";
    $panelLangCacheTag = 'fresnsSystems';
    $langStrings = CacheHelper::get($panelLangCacheKey, $panelLangCacheTag);

    if (empty($langStrings)) {
        $langPath = app_path('Fresns/Panel/Resources/lang/'.$locale);

        if (! is_dir($langPath)) {
            $langPath = app_path('Fresns/Panel/Resources/lang/'.config('app.locale'));
        }

        $langStrings = collect(File::allFiles($langPath))->flatMap(function ($file) {
            $name = basename($file, '.php');
            $strings[$name] = require $file;

            return $strings;
        })->toJson();

        CacheHelper::put($langStrings, $panelLangCacheKey, $panelLangCacheTag);
    }

    // get request, return translation content
    return \response()->json([
        'data' => json_decode($langStrings, true),
    ]);
})->name('translations');

// empty page
Route::any('{any}', [LoginController::class, 'emptyPage'])->name('empty')->where('any', '.*');
