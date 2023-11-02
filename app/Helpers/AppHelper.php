<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Utilities\CommandUtility;
use Browser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AppHelper
{
    const VERSION = '2.21.1';
    const VERSION_MD5 = 'b3ab1609ffb630c7e644196a97460863';
    const VERSION_MD5_16BIT = 'ffb630c7e644196a';

    // fresns test helper
    public static function fresnsTestHelper(): mixed
    {
        $fresnsTest = Str::ulid();

        return $fresnsTest;
    }

    // get system info
    public static function getSystemInfo(): array
    {
        $disableFunctions = explode(',', ini_get('disable_functions'));

        $phpCliInfo = 'PHP function proc_open is disabled and cannot fetch information';
        if (function_exists('proc_open') && ! in_array('proc_open', $disableFunctions)) {
            $phpCliInfo = CommandUtility::getPhpProcess(['-v'])->run()->getOutput();
        }

        $systemInfo = [
            'server' => php_uname('s').' '.php_uname('r'),
            'web' => $_SERVER['SERVER_SOFTWARE'],
            'composer' => self::getComposerVersionInfo(),
            'php' => [
                'version' => PHP_VERSION,
                'cliInfo' => $phpCliInfo,
                'uploadMaxFileSize' => ini_get('upload_max_filesize'),
            ],
        ];

        return $systemInfo;
    }

    // get database info
    public static function getDatabaseInfo(): array
    {
        $type = config('database.default');

        switch ($type) {
            case 'sqlite':
                $name = 'SQLite';

                $SQLite = config('database.connections.sqlite.database');

                $db = new \SQLite3($SQLite);

                $version = $db->querySingle('SELECT sqlite_version()');

                $sizeResult = filesize($SQLite);
                break;

            case 'mysql':
                $name = 'MySQL';
                $version = DB::select('select version()')[0]->{'version()'};

                $sizeResult = DB::select('SELECT SUM(data_length + index_length) / 1024 / 1024 AS "Size" FROM information_schema.TABLES')[0]->Size;
                break;

            case 'pgsql':
                $name = 'PostgreSQL';
                $fullVersion = DB::select('select version()')[0]->version;
                preg_match('/\d+\.\d+/', $fullVersion, $matches);
                $version = $matches[0] ?? '';

                $sizeResult = DB::select('SELECT SUM(pg_total_relation_size(quote_ident(schemaname) || \'.\' || quote_ident(tablename))) / 1024 / 1024 AS "Size" FROM pg_tables')[0]->Size;
                break;

            case 'sqlsrv':
                $name = 'SQL Server';
                $version = DB::select('SELECT @@VERSION as version')[0]->version;

                $sizeResult = DB::select('SELECT SUM(size) * 8 / 1024 AS "Size" FROM sys.master_files WHERE type_desc = \'ROWS\'')[0]->Size;
                break;
            default:
                $name = $type;
                $version = '';
                $sizeResult = 0;
        }

        $sizeMb = 0;
        $sizeGb = 0;
        if ($sizeResult > 0) {
            $sizeMb = round($sizeResult, 2);
            $sizeGb = round($sizeResult / 1024, 2);
        }
        if ($sizeResult > 0 && $type == 'sqlite') {
            $sizeMb = round($sizeResult / 1024 / 1024, 2);
            $sizeGb = round($sizeResult / 1024 / 1024 / 1024, 2);
        }

        $dbInfo = [
            'name' => $name,
            'version' => $version,
            'size' => $sizeGb > 1 ? $sizeGb.' GB' : $sizeMb.' MB',
            'timezone' => 'UTC'.DateHelper::fresnsDatabaseTimezone(),
            'envTimezone' => config('app.timezone'),
            'envTimezoneToUtc' => 'UTC'.DateHelper::fresnsDatabaseTimezoneByName(config('app.timezone')),
        ];

        return $dbInfo;
    }

    // get composer version info
    public static function getComposerVersionInfo(): array
    {
        $versionInfo = [
            'version' => 0,
            'versionInfo' => 'PHP function proc_open is disabled and version information is not available',
        ];

        $disableFunctions = explode(',', ini_get('disable_functions'));

        if (function_exists('proc_open') && ! in_array('proc_open', $disableFunctions)) {
            $composerInfo = CommandUtility::getComposerProcess(['-V'])->run()->getOutput();
            $toArray = explode(' ', $composerInfo);

            $version = null;
            foreach ($toArray as $item) {
                if (substr_count($item, '.') == 2) {
                    $version = $item;
                    break;
                }
            }

            $versionInfo = [
                'version' => $version ?? 0,
                'versionInfo' => $composerInfo,
            ];
        }

        return $versionInfo;
    }

    // get plugin config
    public static function getPluginConfig(string $plugin): array
    {
        $pluginJsonFile = config('plugins.paths.plugins').'/'.$plugin.'/plugin.json';

        if (! file_exists($pluginJsonFile)) {
            return [];
        }

        $pluginConfig = json_decode(File::get($pluginJsonFile), true);

        return $pluginConfig;
    }

    // get device info
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
            'mac' => null,
            'brand' => Browser::deviceFamily(),
            'model' => Browser::deviceModel(),
            'platformName' => Browser::platformFamily(),
            'platformVersion' => Browser::platformVersion(),
            'browserName' => Browser::browserFamily(),
            'browserVersion' => Browser::browserVersion(),
            'browserEngine' => Browser::browserEngine(),
            'appImei' => null,
            'appAndroidId' => null,
            'appOaid' => null,
            'appIdfa' => null,
            'simImsi' => null,
            'networkType' => $networkType,
            'networkIpv4' => $ipv4,
            'networkIpv6' => $ipv6,
            'networkPort' => $_SERVER['REMOTE_PORT'] ?? null,
            'networkTimezone' => null,
            'networkOffset' => null,
            'networkIsp' => null,
            'networkOrg' => null,
            'networkAs' => null,
            'networkAsName' => null,
            'networkMobile' => false,
            'networkProxy' => false,
            'networkHosting' => false,
            'mapId' => 1,
            'latitude' => null,
            'longitude' => null,
            'scale' => null,
            'continent' => null,
            'continentCode' => null,
            'country' => null,
            'countryCode' => null,
            'region' => null,
            'regionCode' => null,
            'city' => null,
            'district' => null,
            'zip' => null,
        ];

        return $deviceInfo;
    }

    // get headers
    public static function getHeaders(): array
    {
        $headers = request()->headers->all();

        $newHeaders = [];
        foreach ($headers as $name => $values) {
            $filteredValues = array_filter($values);

            $mergedValue = implode(',', $filteredValues);

            $newHeaders[$name] = $mergedValue;
        }

        return $newHeaders;
    }

    // get lang tag
    public static function getLangTag(): string
    {
        $clientLangTag = \request()->header('X-Fresns-Client-Lang-Tag');
        $defaultLanguage = ConfigHelper::fresnsConfigDefaultLangTag();

        if (empty($clientLangTag)) {
            return $defaultLanguage;
        }

        $languageStatus = ConfigHelper::fresnsConfigByItemKey('language_status');

        if (! $languageStatus) {
            return $defaultLanguage;
        }

        return $clientLangTag;
    }
}
