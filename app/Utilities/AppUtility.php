<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\ConfigHelper;
use Browser;

class AppUtility
{
    public static function currentVersion()
    {
        return \Cache::remember('currentVersion', 3600, function () {
            $fresnsJson = file_get_contents(
                base_path('fresns.json')
            );

            $currentVersion = json_decode($fresnsJson, true);

            return $currentVersion;
        });
    }

    public static function newVersion()
    {
        return \Cache::remember('newVersion', 3600, function () {
            try {
                $versionInfoUrl = config('FsConfig.version_url');
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', $versionInfoUrl);
                $versionInfo = json_decode($response->getBody(), true);
                $buildType = ConfigHelper::fresnsConfigByItemKey('build_type');

                if ($buildType == 1) {
                    return $versionInfo['stableBuild'];
                }

                return $versionInfo['betaBuild'];
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    public static function editVersion(string $version, int $versionInt)
    {
        $fresnsJson = file_get_contents(
            $path = base_path('fresns.json')
        );

        $currentVersion = json_decode($fresnsJson, true);

        $currentVersion['version'] = $version;
        $currentVersion['versionInt'] = $versionInt;

        $editContent = json_encode($currentVersion, JSON_PRETTY_PRINT);

        return file_put_contents($path, $editContent);
    }

    public static function getMarketHeader(): array
    {
        $isHttps = \request()->getScheme() === 'https';

        $appConfig = ConfigHelper::fresnsConfigByItemKeys([
            'install_datetime',
            'build_type',
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
            'httpHost' => \request()->getHttpHost(),
            'siteName' => $appConfig['site_name'],
            'siteDesc' => $appConfig['site_desc'],
            'siteCopyright' => $appConfig['site_copyright'],
            'timezone' => $appConfig['default_timezone'],
            'language' => $appConfig['default_language'],
        ];

        return $header;
    }

    public static function getDeviceInfo(): array
    {
        $deviceInfo = [
            'type' => Browser::deviceType(),
            'brand' => Browser::deviceFamily(),
            'model' => Browser::deviceModel(),
            'platform' => Browser::platformName(),
            'platformName' => Browser::platformFamily(),
            'platformVersion' => Browser::platformVersion(),
            'browser' => Browser::browserName(),
            'browserName' => Browser::browserFamily(),
            'browserVersion' => Browser::browserVersion(),
            'browserEngine' => Browser::browserEngine(),
            'networkType' => '',
            'networkIpv4' => $_SERVER['REMOTE_ADDR'] ?? '',
            'networkIpv6' => '',
            'networkPort' => $_SERVER['REMOTE_PORT'] ?? '',
            'mapId' => '',
            'latitude' => '',
            'longitude' => '',
            'scale' => '',
            'nation' => '',
            'province' => '',
            'city' => '',
            'district' => '',
            'adcode' => '',
            'positionName' => '',
            'address' => '',
        ];

        return $deviceInfo;
    }
}
