<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Fresns\Panel\Http\Controllers\AdminController;
use App\Fresns\Panel\Http\Controllers\BlockWordController;
use App\Fresns\Panel\Http\Controllers\ClientMenuController;
use App\Fresns\Panel\Http\Controllers\CodeMessageController;
use App\Fresns\Panel\Http\Controllers\ColumnController;
use App\Fresns\Panel\Http\Controllers\ConfigController;
use App\Fresns\Panel\Http\Controllers\DashboardController;
use App\Fresns\Panel\Http\Controllers\ExpandContentTypeController;
use App\Fresns\Panel\Http\Controllers\ExpandEditorController;
use App\Fresns\Panel\Http\Controllers\ExpandGroupController;
use App\Fresns\Panel\Http\Controllers\ExpandManageController;
use App\Fresns\Panel\Http\Controllers\ExpandPostDetailController;
use App\Fresns\Panel\Http\Controllers\ExpandUserFeatureController;
use App\Fresns\Panel\Http\Controllers\ExpandUserProfileController;
use App\Fresns\Panel\Http\Controllers\GeneralController;
use App\Fresns\Panel\Http\Controllers\GroupController;
use App\Fresns\Panel\Http\Controllers\IframeController;
use App\Fresns\Panel\Http\Controllers\InteractiveController;
use App\Fresns\Panel\Http\Controllers\LanguageController;
use App\Fresns\Panel\Http\Controllers\LanguageMenuController;
use App\Fresns\Panel\Http\Controllers\LanguagePackController;
use App\Fresns\Panel\Http\Controllers\LoginController;
use App\Fresns\Panel\Http\Controllers\MapController;
use App\Fresns\Panel\Http\Controllers\PluginController;
use App\Fresns\Panel\Http\Controllers\PluginUsageController;
use App\Fresns\Panel\Http\Controllers\PolicyController;
use App\Fresns\Panel\Http\Controllers\PublishController;
use App\Fresns\Panel\Http\Controllers\RenameController;
use App\Fresns\Panel\Http\Controllers\RoleController;
use App\Fresns\Panel\Http\Controllers\SendController;
use App\Fresns\Panel\Http\Controllers\SessionKeyController;
use App\Fresns\Panel\Http\Controllers\SettingController;
use App\Fresns\Panel\Http\Controllers\StickerController;
use App\Fresns\Panel\Http\Controllers\StickerGroupController;
use App\Fresns\Panel\Http\Controllers\StorageController;
use App\Fresns\Panel\Http\Controllers\UpgradeController;
use App\Fresns\Panel\Http\Controllers\UserController;
use App\Fresns\Panel\Http\Controllers\UserSearchController;
use App\Fresns\Panel\Http\Controllers\VerifyCodeController;
use App\Fresns\Panel\Http\Controllers\WalletController;
use App\Models\Config;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

try {
    $loginConfig = Config::where('item_key', 'panel_path')->first();
    $loginUrl = $loginConfig ? $loginConfig->item_value : 'admin';
} catch (\Exception $e) {
    $loginUrl = 'admin';
}

Route::get($loginUrl, [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post($loginUrl, [LoginController::class, 'login'])->name('login');

Route::middleware(['panelAuth'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // update config
    Route::put('configs/{config:item_key}', [ConfigController::class, 'update'])->name('configs.update');
    // plugin usage
    Route::put('plugin-usages/{pluginUsage}/rank', [PluginUsageController::class, 'updateRank'])->name('plugin-usages.rank.update');
    Route::resource('plugin-usages', PluginUsageController::class)->only([
        'store', 'update', 'destroy',
    ])->parameters([
        'plugin-usages' => 'pluginUsage',
    ]);
    // update language
    Route::put('batch/languages/{itemKey}', [LanguageController::class, 'batchUpdate'])->name('languages.batch.update');
    Route::put('languages/{itemKey}', [LanguageController::class, 'update'])->name('languages.update');
    // users search
    Route::get('users/search', [UserSearchController::class, 'search'])->name('users.search');

    // The following pages function

    // dashboard-home
    Route::get('dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::any('cache/clear', [DashboardController::class, 'cacheClear'])->name('cache.clear');
    // dashboard-upgrades
    Route::get('upgrades', [UpgradeController::class, 'show'])->name('upgrades');
    Route::patch('upgrade/check', [UpgradeController::class, 'checkFresnsVersion'])->name('upgrade.check');
    // automatic upgrade
    Route::post('upgrade', [UpgradeController::class, 'upgrade'])->name('upgrade');
    Route::get('upgrade/info', [UpgradeController::class, 'upgradeInfo'])->name('upgrade.info');
    // physical upgrade
    Route::post('physical-upgrade', [UpgradeController::class, 'physicalUpgrade'])->name('physical.upgrade');
    Route::get('physical-upgrade/info', [UpgradeController::class, 'physicalUpgradeInfo'])->name('physical.upgrade.info');

    // dashboard-admins
    Route::resource('admins', AdminController::class)->only([
        'index', 'store', 'destroy',
    ]);
    // dashboard-settings
    Route::get('settings', [SettingController::class, 'show'])->name('settings');
    Route::put('settings/update', [SettingController::class, 'update'])->name('settings.update');

    // systems
    Route::prefix('systems')->group(function () {
        // languages
        Route::get('languages', [LanguageMenuController::class, 'index'])->name('languages.index');
        Route::post('languageMenus', [LanguageMenuController::class, 'store'])->name('languageMenus.store');
        Route::put('languageMenus/status/switch', [LanguageMenuController::class, 'switchStatus'])->name('languageMenus.status.switch');
        Route::put('languageMenus/{langTag}', [LanguageMenuController::class, 'update'])->name('languageMenus.update');
        Route::put('languageMenus/{langTag}/rank', [LanguageMenuController::class, 'updateRank'])->name('languageMenus.rank.update');
        Route::put('default/languages/update', [LanguageMenuController::class, 'updateDefaultLanguage'])->name('languageMenus.default.update');
        Route::delete('languageMenus/{langTag}', [LanguageMenuController::class, 'destroy'])->name('languageMenus.destroy');
        // general
        Route::get('general', [GeneralController::class, 'show'])->name('general.index');
        Route::put('general', [GeneralController::class, 'update'])->name('general.update');
        // policy
        Route::get('policy', [PolicyController::class, 'show'])->name('policy.index');
        Route::put('policy', [PolicyController::class, 'update'])->name('policy.update');
        // send
        Route::get('send', [SendController::class, 'show'])->name('send.index');
        Route::put('send', [SendController::class, 'update'])->name('send.update');
        // verify code
        Route::put('verifyCodes/{itemKey}/sms', [VerifyCodeController::class, 'updateSms'])->name('verifyCodes.sms.update');
        Route::put('verifyCodes/{itemKey}/email', [VerifyCodeController::class, 'updateEmail'])->name('verifyCodes.email.update');
        // user
        Route::get('user', [UserController::class, 'show'])->name('user.index');
        Route::put('user', [UserController::class, 'update'])->name('user.update');
        // wallet
        Route::get('wallet', [WalletController::class, 'show'])->name('wallet.index');
        Route::put('wallet', [WalletController::class, 'update'])->name('wallet.update');
        Route::get('wallet/recharge', [WalletController::class, 'rechargeIndex'])->name('wallet.recharge.index');
        Route::post('wallet/recharge', [WalletController::class, 'rechargeStore'])->name('wallet.recharge.store');
        Route::put('wallet/recharge/{pluginUsage}', [WalletController::class, 'rechargeUpdate'])->name('wallet.recharge.update');
        Route::get('wallet/withdraw', [WalletController::class, 'withdrawIndex'])->name('wallet.withdraw.index');
        Route::post('wallet/withdraw', [WalletController::class, 'withdrawStore'])->name('wallet.withdraw.store');
        Route::put('wallet/withdraw/{pluginUsage}', [WalletController::class, 'withdrawUpdate'])->name('wallet.withdraw.update');
        // storage-image
        Route::get('storage/image', [StorageController::class, 'imageShow'])->name('storage.image.index');
        Route::put('storage/image', [StorageController::class, 'imageUpdate'])->name('storage.image.update');
        // storage-video
        Route::get('storage/video', [StorageController::class, 'videoShow'])->name('storage.video.index');
        Route::put('storage/video', [StorageController::class, 'videoUpdate'])->name('storage.video.update');
        // storage-audio
        Route::get('storage/audio', [StorageController::class, 'audioShow'])->name('storage.audio.index');
        Route::put('storage/audio', [StorageController::class, 'audioUpdate'])->name('storage.audio.update');
        // storage-document
        Route::get('storage/document', [StorageController::class, 'documentShow'])->name('storage.document.index');
        Route::put('storage/document', [StorageController::class, 'documentUpdate'])->name('storage.document.update');
        // storage-substitution
        Route::get('storage/substitution', [StorageController::class, 'substitutionShow'])->name('storage.substitution.index');
        Route::put('storage/substitution', [StorageController::class, 'substitutionUpdate'])->name('storage.substitution.update');
        // maps
        Route::resource('maps', MapController::class)->only([
            'index', 'store', 'update',
        ]);
    });

    // operatings
    Route::prefix('operations')->group(function () {
        // rename
        Route::get('rename', [RenameController::class, 'show'])->name('rename.index');
        // interactive
        Route::get('interactive', [InteractiveController::class, 'show'])->name('interactive.index');
        Route::put('interactive', [InteractiveController::class, 'update'])->name('interactive.update');
        // stickers
        Route::resource('stickers', StickerGroupController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        Route::put('stickers/{sticker}/rank', [StickerController::class, 'updateRank'])->name('stickers.rank');
        Route::put('sticker-images/batch', [StickerController::class, 'batchUpdate'])->name('sticker-images.batch.update');
        Route::resource('sticker-images', StickerController::class)->only([
            'index', 'store', 'update', 'destroy',
        ])->parameters([
            'sticker-images' => 'stickerImage',
        ]);
        // publish-post
        Route::get('publish/post', [PublishController::class, 'postShow'])->name('publish.post.index');
        Route::put('publish/post', [PublishController::class, 'postUpdate'])->name('publish.post.update');
        // publish-comment
        Route::get('publish/comment', [PublishController::class, 'commentShow'])->name('publish.comment.index');
        Route::put('publish/comment', [PublishController::class, 'commentUpdate'])->name('publish.comment.update');
        // block-words
        Route::resource('block-words', BlockWordController::class)->only([
            'index', 'store', 'update', 'destroy',
        ])->parameters([
            'block-words' => 'blockWord',
        ]);
        Route::post('block-words/export', [BlockWordController::class, 'export'])->name('block-words.export');
        Route::post('block-words/import', [BlockWordController::class, 'import'])->name('block-words.import');
        // roles
        Route::resource('roles', RoleController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        Route::put('roles/{role}/rank', [RoleController::class, 'updateRank'])->name('roles.rank');
        Route::get('roles/{role}/permissions', [RoleController::class, 'showPermissions'])->name('roles.permissions.index');
        Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');
        // groups
        Route::resource('groups', GroupController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        Route::get('groups/recommend', [GroupController::class, 'recommendIndex'])->name('groups.recommend.index');
        Route::get('groups/inactive', [GroupController::class, 'disableIndex'])->name('groups.inactive.index');
        Route::put('groups/{group}/merge', [GroupController::class, 'mergeGroup'])->name('groups.merge');
        Route::put('groups/{group}/rank', [GroupController::class, 'updateRank'])->name('groups.rank.update');
        Route::put('groups/{group}/recom_rank', [GroupController::class, 'updateRecomRank'])->name('groups.recom.rank.update');
        Route::put('groups/{group}/enable', [GroupController::class, 'updateEnable'])->name('groups.enable.update');
        Route::get('groups/categories', [GroupController::class, 'groupIndex'])->name('groups.categories.index');
    });

    // expands
    Route::prefix('expands')->group(function () {
        // editor
        Route::resource('editor', ExpandEditorController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        Route::put('editor/{id}/rank', [ExpandEditorController::class, 'updateRank'])->name('editor.rank');
        // content-type
        Route::resource('content-type', ExpandContentTypeController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        Route::put('content-type/{id}/dataSources/{key}', [ExpandContentTypeController::class, 'updateSource'])->name('content-type.source');
        Route::put('content-type/{id}/rank', [ExpandContentTypeController::class, 'updateRank'])->name('content-type.rank');
        // post-detail
        Route::resource('post-detail', ExpandPostDetailController::class)->only([
            'index', 'update',
        ]);
        // manage
        Route::resource('manage', ExpandManageController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        Route::put('manage/{id}/rank', [ExpandManageController::class, 'updateRank'])->name('manage.rank');
        // group
        Route::resource('group', ExpandGroupController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        Route::put('group/{id}/rank', [ExpandGroupController::class, 'updateRank'])->name('group.rank');
        // user-feature
        Route::resource('user-feature', ExpandUserFeatureController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        Route::put('user-feature/{id}/rank', [ExpandUserFeatureController::class, 'updateRank'])->name('user-feature.rank');
        // user-profile
        Route::resource('user-profile', ExpandUserProfileController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        Route::put('user-profile/{id}/rank', [ExpandUserProfileController::class, 'updateRank'])->name('user-profile.rank');
    });

    // clients
    Route::prefix('clients')->group(function () {
        // menus
        Route::get('menus', [ClientMenuController::class, 'index'])->name('menus.index');
        Route::put('menus/{key}/update', [ClientMenuController::class, 'update'])->name('menus.update');
        // columns
        Route::get('columns', [ColumnController::class, 'index'])->name('columns.index');
        // language pack
        Route::get('language-packs', [LanguagePackController::class, 'index'])->name('language.packs.index');
        Route::get('language-packs/{langTag}/edit', [LanguagePackController::class, 'edit'])->name('language.packs.edit');
        Route::put('language-packs/{langTag}', [LanguagePackController::class, 'update'])->name('language.packs.update');
        // code messages
        Route::get('code-messages', [CodeMessageController::class, 'index'])->name('code.messages.index');
        // session key
        Route::resource('keys', SessionKeyController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);
        Route::put('keys/{key}/reset', [SessionKeyController::class, 'reset'])->name('keys.reset');
        // engines
        Route::get('engines', [PluginController::class, 'engineIndex'])->name('engines.index');
        Route::put('engines/{engine}/theme', [PluginController::class, 'updateEngineTheme'])->name('engines.theme.update');
        // themes
        Route::get('themes', [PluginController::class, 'themeIndex'])->name('themes.index');
        // apps
        Route::get('apps', [PluginController::class, 'appIndex'])->name('apps.index');
    });

    // plugins
    Route::prefix('plugins')->group(function () {
        Route::get('list', [PluginController::class, 'index'])->name('plugin.list');
    });

    // plugin manage
    Route::prefix('plugin')->group(function () {
        // dashboard upgrade page
        Route::patch('update-code', [PluginController::class, 'updateCode'])->name('plugin.update.code');
        // plugin install and upgrade
        Route::put('install', [PluginController::class, 'install'])->name('plugin.install');
        Route::put('upgrade', [PluginController::class, 'upgrade'])->name('plugin.upgrade');
        // activate or deactivate
        Route::patch('update', [PluginController::class, 'update'])->name('plugin.update');
        Route::patch('updateTheme', [PluginController::class, 'updateTheme'])->name('plugin.updateTheme');
        // uninstall
        Route::delete('uninstall', [PluginController::class, 'uninstall'])->name('plugin.uninstall');
        Route::delete('uninstallTheme', [PluginController::class, 'uninstallTheme'])->name('plugin.uninstallTheme');
    });

    // iframe
    Route::get('market', [IframeController::class, 'market'])->name('iframe.market');
    Route::get('plugin', [IframeController::class, 'plugin'])->name('iframe.plugin');
    Route::get('client', [IframeController::class, 'client'])->name('iframe.client');
});

// FsLang
Route::get('js/{locale?}/translations', function ($locale) {
    $langPath = app_path('Fresns/Panel/Resources/lang/'.$locale);

    if (! is_dir($langPath)) {
        $langPath = app_path('Fresns/Panel/Resources/lang/'.config('FsConfig.defaultLangTag'));
    }

    $strings = Cache::rememberForever('translations.'.$locale, function () use ($langPath) {
        return collect(File::allFiles($langPath))->flatMap(function ($file) {
            $name = basename($file, '.php');
            $strings[$name] = require $file;

            return $strings;
        })->toJson();
    });

    return response('window.translations= '.$strings.';', 200)->header('Content-Type', 'text/javascript');
})->name('translations');

// empty page
Route::any('{any}', [LoginController::class, 'emptyPage'])->name('empty')->where('any', '.*');
