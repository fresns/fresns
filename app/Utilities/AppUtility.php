<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\AppHelper;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Models\Account;
use App\Models\Config;
use App\Models\Plugin;
use App\Models\SessionKey;
use App\Models\UserRole;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AppUtility
{
    const BASE_URL = 'https://app.fresns.org';
    const WEBSITE_URL = 'https://fresns.org';
    const WEBSITE_ZH_HANS_URL = 'https://zh-hans.fresns.org';
    const WEBSITE_ZH_HANT_URL = 'https://zh-hant.fresns.org';
    const COMMUNITY_URL = 'https://discuss.fresns.com';
    const MARKETPLACE_URL = 'https://marketplace.fresns.com';

    public static function currentVersion(): array
    {
        $cacheKey = 'fresns_current_version';
        $cacheTag = 'fresnsSystems';
        $currentVersion = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($currentVersion)) {
            $fresnsJson = file_get_contents(
                base_path('fresns.json')
            );

            $currentVersion = json_decode($fresnsJson, true);

            CacheHelper::put($currentVersion, $cacheKey, $cacheTag, 1, now()->addDays());
        }

        return $currentVersion;
    }

    public static function newVersion(): array
    {
        $cacheKey = 'fresns_new_version';
        $cacheTag = 'fresnsSystems';
        $newVersion = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($newVersion)) {
            try {
                $versionInfoUrl = AppUtility::BASE_URL.'/version.json';
                $client = new \GuzzleHttp\Client(['verify' => false]);
                $response = $client->request('GET', $versionInfoUrl);
                $versionInfo = json_decode($response->getBody(), true);
                $buildType = ConfigHelper::fresnsConfigByItemKey('build_type');

                if ($buildType == 1) {
                    $newVersion = $versionInfo['stableBuild'];
                } else {
                    $newVersion = $versionInfo['betaBuild'];
                }
            } catch (\Exception $e) {
                $newVersion = [
                    'version' => AppHelper::VERSION,
                    'releaseDate' => null,
                    'changeIntro' => 'https://fresns.org/guide/upgrade.html#version-info',
                    'upgradeAuto' => false,
                    'upgradeIntro' => 'https://github.com/fresns/fresns/blob/2.x/CHANGELOG.md',
                    'upgradePackage' => null,
                ];
            }

            CacheHelper::put($newVersion, $cacheKey, $cacheTag, 10, now()->addHours(6));
        }

        return $newVersion;
    }

    public static function writeEnvironment(array $dbConfig, ?string $appUrl = null): void
    {
        // Get the config file template
        $envExamplePath = app_path('Fresns/Install/.env.template');
        $envPath = base_path('.env');

        $envTemp = file_get_contents($envExamplePath);

        $appKey = Encrypter::generateKey(config('app.cipher'));
        $appKey = sprintf('base64:%s', base64_encode($appKey));

        if (empty($appUrl)) {
            $appUrl = str_replace(\request()->getRequestUri(), '', \request()->getUri());
        }

        $driver = $dbConfig['DB_CONNECTION'];

        // Temp write key
        $template['APP_KEY'] = $appKey;
        $template['APP_URL'] = $appUrl;
        $template['DB_CONNECTION'] = $driver;
        $template['DB_HOST'] = ($driver == 'sqlite') ? '' : $dbConfig['DB_HOST'];
        $template['DB_PORT'] = ($driver == 'sqlite') ? '' : $dbConfig['DB_PORT'];
        $template['DB_DATABASE'] = $dbConfig['DB_DATABASE'];
        $template['DB_USERNAME'] = ($driver == 'sqlite') ? '' : $dbConfig['DB_USERNAME'];
        $template['DB_PASSWORD'] = ($driver == 'sqlite') ? '' : $dbConfig['DB_PASSWORD'];
        $template['DB_TIMEZONE'] = $dbConfig['DB_TIMEZONE'];
        $template['DB_PREFIX'] = $dbConfig['DB_PREFIX'];

        foreach ($template as $key => $value) {
            $envTemp = str_replace('{'.$key.'}', $value, $envTemp);
        }

        // Write config
        file_put_contents($envPath, $envTemp);
    }

    public static function writeInstallTime(): void
    {
        Config::updateOrCreate([
            'item_key' => 'install_datetime',
        ], [
            'item_value' => now(),
            'item_type' => 'string',
            'item_tag' => 'systems',
        ]);

        // install.lock
        $installLock = base_path('install.lock');

        file_put_contents($installLock, now()->toDateTimeString());
    }

    public static function makeAdminAccount(string $email, string $password): void
    {
        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->addAccount([
            'type' => 1,
            'account' => $email,
            'password' => $password,
        ]);

        $aid = $fresnsResp->getData('aid');

        \FresnsCmdWord::plugin('Fresns')->addUser([
            'aid' => $aid,
            'nickname' => 'Admin',
            'username' => 'admin',
        ]);

        // set account to admin
        $result = Account::whereAid($aid)->update([
            'type' => 1,
        ]);

        UserRole::where('user_id', 1)->where('is_main', 1)->update([
            'role_id' => 1,
        ]);

        Artisan::call('plugin:install', [
            'path' => realpath(base_path('extensions/plugins/FresnsEngine')),
        ]);
        Artisan::call('theme:install', [
            'path' => realpath(base_path('extensions/themes/ThemeFrame')),
        ]);
        Artisan::call('theme:install', [
            'path' => realpath(base_path('extensions/themes/Moments')),
        ]);

        AppUtility::setInitialConfiguration();

        info('update type', [$result, $aid]);
    }

    public static function setInitialConfiguration(): void
    {
        $engine = AppHelper::getPluginConfig('FresnsEngine');
        $theme = AppHelper::getThemeConfig('ThemeFrame');

        // check web engine and theme
        if (empty($engine) && empty($theme)) {
            return;
        }

        // create key
        $appKey = new SessionKey();
        $appKey->platform_id = 4;
        $appKey->name = 'Fresns Engine';
        $appKey->app_id = Str::random(8);
        $appKey->app_secret = Str::random(32);
        $appKey->save();

        // config web engine and theme
        $configKeys = [
            'engine_key_id',
            'FresnsEngine_Desktop',
            'FresnsEngine_Mobile',
        ];

        $configValues = [
            'engine_key_id' => $appKey->id,
            'FresnsEngine_Desktop' => 'ThemeFrame',
            'FresnsEngine_Mobile' => 'ThemeFrame',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();

            $config->item_value = $configValues[$configKey];
            $config->save();
        }

        // activate web engine
        Artisan::call('market:activate', ['fskey' => 'FresnsEngine']);
    }

    public static function checkVersion(): bool
    {
        $currentVersion = AppUtility::currentVersion()['version'];
        $newVersion = AppUtility::newVersion()['version'];

        if (version_compare($currentVersion, $newVersion) == -1) {
            return true; // There is a new version
        }

        return false; // No new version
    }

    public static function editVersion(string $version): bool
    {
        $fresnsJson = file_get_contents(
            $path = base_path('fresns.json')
        );

        $currentVersion = json_decode($fresnsJson, true);
        if (! $currentVersion) {
            throw new \RuntimeException('Failed to update version information');
        }

        $currentVersion['version'] = $version;

        $editContent = json_encode($currentVersion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        file_put_contents($path, $editContent);

        return true;
    }

    public static function checkPluginsStatus(int $type): void
    {
        $fresnsJsonFile = file_get_contents(config('plugins.manager.default.file'));

        $fresnsJson = json_decode($fresnsJsonFile, true);

        $plugins = $fresnsJson['plugins'] ?? null;

        $pluginModels = Plugin::type($type)->get();

        foreach ($pluginModels as $plugin) {
            $status = $plugins[$plugin->fskey] ?? false;

            $plugin->is_enabled = $status;
            $plugin->save();
        }
    }

    public static function macroMarketHeaders(): void
    {
        Http::macro('market', function () {
            return Http::withHeaders(
                AppUtility::getMarketHeaders()
            )->baseUrl(
                AppUtility::MARKETPLACE_URL
            );
        });
    }

    public static function getMarketHeaders(): array
    {
        $appConfig = ConfigHelper::fresnsConfigByItemKeys([
            'install_datetime',
            'build_type',
            'site_url',
            'site_name',
            'site_desc',
            'site_copyright',
            'default_timezone',
            'default_language',
        ]);

        $isHttps = \request()->getScheme() === 'https';

        return [
            'X-Fresns-Panel-Lang-Tag' => App::getLocale(),
            'X-Fresns-Install-Datetime' => $appConfig['install_datetime'],
            'X-Fresns-Build-Type' => $appConfig['build_type'],
            'X-Fresns-Version' => AppHelper::VERSION,
            'X-Fresns-Database' => config('database.default'),
            'X-Fresns-Http-Ssl' => $isHttps ? 1 : 0,
            'X-Fresns-Http-Host' => \request()->getHost(),
            'X-Fresns-Http-Port' => \request()->getPort(),
            'X-Fresns-System-Url' => config('app.url'),
            'X-Fresns-Site-Url' => $appConfig['site_url'],
            'X-Fresns-Site-Name' => base64_encode($appConfig['site_name']),
            'X-Fresns-Site-Desc' => base64_encode($appConfig['site_desc']),
            'X-Fresns-Site-Copyright' => base64_encode($appConfig['site_copyright']),
            'X-Fresns-Site-Timezone' => $appConfig['default_timezone'],
            'X-Fresns-Site-Language' => $appConfig['default_language'],
        ];
    }
}
