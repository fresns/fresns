<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\ConfigHelper;

class VersionUtility
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
}
