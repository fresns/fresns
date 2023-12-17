<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use App\Fresns\Panel\Http\Controllers\AccountController;
use App\Fresns\Panel\Http\Controllers\AdminController;
use App\Fresns\Panel\Http\Controllers\AppController;
use App\Fresns\Panel\Http\Controllers\CodeMessageController;
use App\Fresns\Panel\Http\Controllers\ColumnController;
use App\Fresns\Panel\Http\Controllers\CommonController;
use App\Fresns\Panel\Http\Controllers\ConfigController;
use App\Fresns\Panel\Http\Controllers\ContentController;
use App\Fresns\Panel\Http\Controllers\DashboardController;
use App\Fresns\Panel\Http\Controllers\ExtendContentHandlerController;
use App\Fresns\Panel\Http\Controllers\GeneralController;
use App\Fresns\Panel\Http\Controllers\GroupController;
use App\Fresns\Panel\Http\Controllers\IframeController;
use App\Fresns\Panel\Http\Controllers\InteractionController;
use App\Fresns\Panel\Http\Controllers\LanguageController;
use App\Fresns\Panel\Http\Controllers\LanguagePackController;
use App\Fresns\Panel\Http\Controllers\LoginController;
use App\Fresns\Panel\Http\Controllers\MenuController;
use App\Fresns\Panel\Http\Controllers\PluginController;
use App\Fresns\Panel\Http\Controllers\PluginUsageController;
use App\Fresns\Panel\Http\Controllers\PolicyController;
use App\Fresns\Panel\Http\Controllers\PublishController;
use App\Fresns\Panel\Http\Controllers\RoleController;
use App\Fresns\Panel\Http\Controllers\SendController;
use App\Fresns\Panel\Http\Controllers\SessionKeyController;
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
        Route::patch('status/{itemKey}', [CommonController::class, 'updateStatus'])->name('status'); // config status
    });
    // search users
    Route::get('search/users', [CommonController::class, 'searchUsers'])->name('search.users');

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
        Route::put('languageMenus/status', [LanguageController::class, 'switchStatus'])->name('languageMenus.status');
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
        Route::patch('content/hashtag-regexp', [ContentController::class, 'updateHashtagRegexp'])->name('content.update.hashtag-regexp');
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
            'index', 'store', 'update', 'destroy',
        ]);
        Route::get('groups/recommend', [GroupController::class, 'recommendIndex'])->name('groups.recommend.index');
        Route::get('groups/inactive', [GroupController::class, 'disableIndex'])->name('groups.inactive.index');
        Route::put('groups/{group}/merge', [GroupController::class, 'mergeGroup'])->name('groups.merge');
        Route::put('groups/{group}/rating', [GroupController::class, 'updateRating'])->name('groups.rating.update');
        Route::put('groups/{group}/recommend_rating', [GroupController::class, 'updateRecommendRank'])->name('groups.recommend.rating.update');
        Route::put('groups/{group}/enable', [GroupController::class, 'updateEnable'])->name('groups.enable.update');
        Route::get('groups/categories', [GroupController::class, 'groupIndex'])->name('groups.categories.index');
    });

    // extends
    Route::prefix('extends')->group(function () {
        // content-handler
        Route::get('content-handler', [ExtendContentHandlerController::class, 'index'])->name('content-handler.index');
        Route::put('content-handler', [ExtendContentHandlerController::class, 'update'])->name('content-handler.update');
    });

    // plugin usages
    Route::prefix('plugin-usages')->name('plugin-usages.')->group(function () {
        Route::get('{usageType}', [PluginUsageController::class, 'show'])->name('index');
        Route::post('{usageType}/store', [PluginUsageController::class, 'store'])->name('store');
        Route::put('update/{id}', [PluginUsageController::class, 'update'])->name('update');
        Route::delete('destroy/{id}', [PluginUsageController::class, 'destroy'])->name('destroy');
        Route::patch('order/{id}', [PluginUsageController::class, 'updateOrder'])->name('update-order');
    });

    // clients
    Route::prefix('clients')->group(function () {
        // menus
        Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
        Route::put('menus/{key}/update', [MenuController::class, 'update'])->name('menus.update');
        // update default_homepage
        Route::put('configs/{config:item_key}', [ConfigController::class, 'update'])->name('configs.update');
        // columns
        Route::get('columns', [ColumnController::class, 'index'])->name('columns.index');
        // language pack
        Route::get('language-packs', [LanguagePackController::class, 'index'])->name('language.packs.index');
        Route::get('language-packs/{langTag}/edit', [LanguagePackController::class, 'edit'])->name('language.packs.edit');
        Route::put('language-packs/{langTag}', [LanguagePackController::class, 'update'])->name('language.packs.update');
        // code messages
        Route::get('code-messages', [CodeMessageController::class, 'index'])->name('code.messages.index');
        Route::put('code-messages/{codeMessage}', [CodeMessageController::class, 'update'])->name('code.messages.update');
        // path
        Route::get('paths', [AppController::class, 'pathIndex'])->name('paths.index');
        Route::put('paths', [AppController::class, 'pathUpdate'])->name('paths.update');
        // basic
        Route::get('basic', [AppController::class, 'basicIndex'])->name('client.basic');
        Route::put('basic', [AppController::class, 'basicUpdate'])->name('client.basic.update');
        // status
        Route::get('status', [AppController::class, 'statusIndex'])->name('client.status');
        Route::put('status', [AppController::class, 'statusUpdate'])->name('client.status.update');
    });

    // app center
    Route::prefix('app-center')->group(function () {
        // plugins
        Route::get('plugins', [PluginController::class, 'index'])->name('plugins.index');
        // apps
        Route::get('apps', [PluginController::class, 'appIndex'])->name('apps.index');
        // session key
        Route::resource('keys', SessionKeyController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        Route::put('keys/{key}/reset', [SessionKeyController::class, 'reset'])->name('keys.reset');
    });

    // iframe
    Route::get('setting', [IframeController::class, 'setting'])->name('iframe.setting');
    Route::get('marketplace', [IframeController::class, 'marketplace'])->name('iframe.marketplace');

    // plugin manage
    Route::prefix('plugin')->name('plugin.')->group(function () {
        // dashboard upgrade page
        Route::patch('update-code', [PluginController::class, 'updateCode'])->name('update.code');
        // plugin install and upgrade
        Route::put('install', [PluginController::class, 'install'])->name('install');
        Route::put('upgrade', [PluginController::class, 'upgrade'])->name('upgrade');
        // activate or deactivate
        Route::patch('update', [PluginController::class, 'update'])->name('update');
        // uninstall
        Route::delete('uninstall', [PluginController::class, 'uninstall'])->name('uninstall');
        // check status
        Route::post('check-status', [PluginController::class, 'checkStatus'])->name('check.status');
    });

    // apps
    Route::prefix('app')->name('app.')->group(function () {
        Route::post('download', [PluginController::class, 'appDownload'])->name('download');
        Route::delete('delete', [PluginController::class, 'appDelete'])->name('delete');
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
