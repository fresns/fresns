<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\AppHelper;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Models\Plugin;
use Browser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;

class AppUtility
{
    public static function currentVersion()
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

    public static function newVersion()
    {
        $cacheKey = 'fresns_new_version';
        $cacheTag = 'fresnsSystems';
        $newVersion = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($newVersion)) {
            try {
                $versionInfoUrl = AppUtility::getAppHost().'/version.json';
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', $versionInfoUrl);
                $versionInfo = json_decode($response->getBody(), true);
                $buildType = ConfigHelper::fresnsConfigByItemKey('build_type');

                if ($buildType == 1) {
                    $newVersion = $versionInfo['stableBuild'];
                } else {
                    $newVersion = $versionInfo['betaBuild'];
                }
            } catch (\Exception $e) {
                $newVersion = AppHelper::getAppVersion();
            }

            CacheHelper::put($newVersion, $cacheKey, $cacheTag, 10, now()->addHours(6));
        }

        return $newVersion;
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
        $appHost = base64_decode('aHR0cHM6Ly9hcHAuZnJlc25zLm9yZw==', true);

        return $appHost;
    }

    public static function getApiHost()
    {
        $apiHost = base64_decode('aHR0cHM6Ly9tYXJrZXQuZnJlc25zLmNvbQ==', true);

        return $apiHost;
    }

    public static function macroMarketHeaders()
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

        $headers = [
            'panelLangTag' => App::getLocale(),
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

        Http::macro('market', function () use ($headers) {
            return Http::withHeaders($headers)->baseUrl(AppUtility::getApiHost());
        });
    }

    public static function getDeviceInfo(): array
    {
        $ip = request()->ip();
        if (strpos($ip, ':') !== false) {
            $ipv4 = null;
            $ipv6 = $ip;
        } else {
            $ipv4 = $ip;
            $ipv6 = null;
        }

        $networkType = null;
        if (empty(request()->header('HTTP_VIA'))) {
            $networkType = 'wifi';
        }

        $deviceInfo = [
            'agent' => Browser::userAgent(),
            'type' => Browser::deviceType(),
            'mac' => '',
            'brand' => Browser::deviceFamily(),
            'model' => Browser::deviceModel(),
            'platformName' => Browser::platformFamily(),
            'platformVersion' => Browser::platformVersion(),
            'browserName' => Browser::browserFamily(),
            'browserVersion' => Browser::browserVersion(),
            'browserEngine' => Browser::browserEngine(),
            'networkType' => $networkType,
            'networkIpv4' => $ipv4,
            'networkIpv6' => $ipv6,
            'networkPort' => $_SERVER['REMOTE_PORT'],
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
        logger('upgrade:fresns upgrade command check');

        $currentVersionInt = AppUtility::currentVersion()['versionInt'] ?? 0;
        $newVersionInt = AppUtility::newVersion()['versionInt'] ?? 0;

        logger('currentVersionInt: '.$currentVersionInt);
        logger('newVersionInt: '.$newVersionInt);
        if (! $currentVersionInt || ! $newVersionInt) {
            return false;
        }

        logger('upgrade:fresns upgrade-{n} command');
        $versionInt = $currentVersionInt;

        while ($versionInt <= $newVersionInt) {
            $versionInt++;
            $command = 'fresns:upgrade-'.$versionInt;
            if (\Artisan::has($command)) {
                $exitCode = \Artisan::call($command);

                logger($command.' exitCode = '.$exitCode);

                if ($exitCode == Command::FAILURE) {
                    return false;
                }
            }
        }

        return true;
    }
}
