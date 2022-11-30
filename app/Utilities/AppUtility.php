<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\AppHelper;
use App\Helpers\ConfigHelper;
use App\Models\Plugin;
use Browser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AppUtility
{
    public static function currentVersion()
    {
        // Cache::tags(['fresnsSystems'])
        return Cache::remember('fresns_current_version', now()->addDays(), function () {
            $fresnsJson = file_get_contents(
                base_path('fresns.json')
            );

            return json_decode($fresnsJson, true);
        });
    }

    public static function newVersion()
    {
        // Cache::tags(['fresnsSystems'])
        return Cache::remember('fresns_new_version', now()->addHours(6), function () {
            try {
                $versionInfoUrl = AppUtility::getAppHost().'/version.json';
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', $versionInfoUrl);
                $versionInfo = json_decode($response->getBody(), true);
                $buildType = ConfigHelper::fresnsConfigByItemKey('build_type');

                if ($buildType == 1) {
                    return $versionInfo['stableBuild'];
                }

                return $versionInfo['betaBuild'];
            } catch (\Exception $e) {
                return AppHelper::getAppVersion();
            }
        });
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

    public static function editVersion(string $version, int $versionInt)
    {
        $fresnsJson = file_get_contents(
            $path = base_path('fresns.json')
        );

        $currentVersion = json_decode($fresnsJson, true);
        if (! $currentVersion) {
            throw new \RuntimeException('Failed to update version information');
        }

        $currentVersion['version'] = $version;
        $currentVersion['versionInt'] = $versionInt;

        $editContent = json_encode($currentVersion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        file_put_contents($path, $editContent);

        return true;
    }

    public static function checkPluginsStatus(int $type)
    {
        $fresnsJsonFile = file_get_contents(config('plugins.manager.default.file'));

        $fresnsJson = json_decode($fresnsJsonFile, true);

        $plugins = $fresnsJson['plugins'] ?? null;

        $pluginModels = Plugin::type($type)->get();

        foreach ($pluginModels as $plugin) {
            $status = $plugins[$plugin->unikey] ?? false;

            $plugin->is_enable = $status;
            $plugin->save();
        }
    }

    public static function getAppHost()
    {
        $appHost = base64_decode('aHR0cHM6Ly9hcHAuZnJlc25zLmNu', true);

        return $appHost;
    }

    public static function getApiHost()
    {
        $apiHost = base64_decode('aHR0cHM6Ly9tYXJrZXQuZnJlc25zLmNu', true);

        return $apiHost;
    }

    public static function macroMarketHeader()
    {
        Http::macro('market', function () {
            return Http::withHeaders(
                AppUtility::getMarketHeader()
            )
            ->baseUrl(
                AppUtility::getApiHost()
            );
        });
    }

    public static function getMarketHeader(): array
    {
        $isHttps = \request()->getScheme() === 'https';

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

        $header = [
            'panelLangTag' => \App::getLocale(),
            'installDatetime' => $appConfig['install_datetime'],
            'buildType' => $appConfig['build_type'],
            'version' => self::currentVersion()['version'],
            'versionInt' => self::currentVersion()['versionInt'],
            'httpSsl' => $isHttps ? 1 : 0,
            'httpHost' => \request()->getHost(),
            'httpPort' => \request()->getPort(),
            'siteUrl' => $appConfig['site_url'],
            'siteName' => base64_encode($appConfig['site_name']),
            'siteDesc' => base64_encode($appConfig['site_desc']),
            'siteCopyright' => base64_encode($appConfig['site_copyright']),
            'siteTimezone' => $appConfig['default_timezone'],
            'siteLanguage' => $appConfig['default_language'],
        ];

        return $header;
    }

    public static function getDeviceInfo(): array
    {
        $deviceInfo = [
            'type' => Browser::deviceType(),
            'mac' => '',
            'brand' => Browser::deviceFamily(),
            'model' => Browser::deviceModel(),
            'platformName' => Browser::platformFamily(),
            'platformVersion' => Browser::platformVersion(),
            'browserName' => Browser::browserFamily(),
            'browserVersion' => Browser::browserVersion(),
            'browserEngine' => Browser::browserEngine(),
            'networkType' => '',
            'networkIpv4' => request()->ip(),
            'networkIpv6' => '',
            'networkPort' => $_SERVER['REMOTE_PORT'] ?? '',
            'networkTimezone' => '',
            'networkOffset' => '',
            'networkCurrency' => '',
            'networkIsp' => '',
            'networkOrg' => '',
            'networkAs' => '',
            'networkAsName' => '',
            'networkMobile' => '',
            'networkProxy' => '',
            'networkHosting' => '',
            'appImei' => '',
            'appAndroidId' => '',
            'appOaid' => '',
            'appIdfa' => '',
            'simImsi' => '',
            'mapId' => '',
            'latitude' => '',
            'longitude' => '',
            'scale' => '',
            'continent' => '',
            'continentCode' => '',
            'country' => '',
            'countryCode' => '',
            'region' => '',
            'regionCode' => '',
            'city' => '',
            'district' => '',
            'zip' => '',
        ];

        return $deviceInfo;
    }

    public static function executeUpgradeCommand(): bool
    {
        logger('upgrade:fresns upgrade command');

        $currentVersionInt = AppUtility::currentVersion()['versionInt'] ?? 0;
        $newVersionInt = AppUtility::newVersion()['versionInt'] ?? 0;

        if (! $currentVersionInt || ! $newVersionInt) {
            return false;
        }

        $versionInt = $currentVersionInt;

        while ($versionInt <= $newVersionInt) {
            $versionInt++;
            $command = 'fresns:upgrade-'.$versionInt;
            if (\Artisan::has($command)) {
                $exitCode = \Artisan::call($command);
                if ($exitCode) {
                    return false;
                }
            }
        }

        return true;
    }
}
