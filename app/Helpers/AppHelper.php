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
    const VERSION = '3.6.1';
    const VERSION_MD5 = '0cce8edc24ad7a5dcfe98a5431c29ea1';
    const VERSION_MD5_16BIT = '24ad7a5dcfe98a54';

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
                $sizeResult = DB::select('SELECT SUM(data_length + index_length) AS "Size" FROM information_schema.TABLES')[0]->Size;
                break;

            case 'mariadb':
                $name = 'MariaDB';
                $version = DB::select('select version()')[0]->{'version()'};
                $sizeResult = DB::select('SELECT SUM(data_length + index_length) AS "Size" FROM information_schema.TABLES')[0]->Size;
                break;

            case 'pgsql':
                $name = 'PostgreSQL';
                $fullVersion = DB::select('select version()')[0]->version;
                preg_match('/\d+\.\d+/', $fullVersion, $matches);
                $version = $matches[0] ?? '';
                $sizeResult = DB::select('SELECT SUM(pg_total_relation_size(quote_ident(schemaname) || \'.\' || quote_ident(tablename))) AS "Size" FROM pg_tables')[0]->Size;
                break;

            case 'sqlsrv':
                $name = 'SQL Server';
                $version = DB::select('SELECT @@VERSION as version')[0]->version;
                // 获取总字节，注意 SQL Server 计算的基本单位是 KB，因此乘以 1024 转换为字节
                $sizeResult = DB::select('SELECT SUM(size) * 8 * 1024 AS "Size" FROM sys.master_files WHERE type_desc = \'ROWS\'')[0]->Size;
                break;

            default:
                $name = $type;
                $version = '';
                $sizeResult = 0;
        }

        $dbInfo = [
            'name' => $name,
            'version' => $version,
            'size' => StrHelper::fileSize($sizeResult),
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
    public static function getPluginConfig(string $fskey): array
    {
        $pluginJsonFile = config('plugins.paths.plugins').'/'.$fskey.'/plugin.json';

        if (! file_exists($pluginJsonFile)) {
            return [];
        }

        $pluginConfig = json_decode(File::get($pluginJsonFile), true);

        return $pluginConfig;
    }

    // get theme config
    public static function getThemeConfig(string $fskey): array
    {
        $themeJsonFile = config('themes.paths.themes').'/'.$fskey.'/theme.json';

        if (! file_exists($themeJsonFile)) {
            return [];
        }

        $themeConfig = json_decode(File::get($themeJsonFile), true);

        return $themeConfig;
    }

    // get device info
    public static function getDeviceInfo(): array
    {
        $forwardedIp = request()->header('X-Forwarded-For');

        if ($forwardedIp) {
            $ipList = explode(',', $forwardedIp);
            $ipAddress = trim($ipList[0]);
        } else {
            $ipAddress = request()->ip();
        }

        if (strpos($ipAddress, ':') !== false) {
            $ipv4 = null;
            $ipv6 = $ipAddress;
        } else {
            $ipv4 = $ipAddress;
            $ipv6 = null;
        }

        $networkType = null;
        if (empty(request()->header('HTTP_VIA'))) {
            $networkType = 'wifi';
        }

        $deviceInfo = [
            'agent' => Browser::userAgent(),
            'type' => Browser::deviceType(),
            'platformName' => Browser::platformName(),
            'platformFamily' => Browser::platformFamily(),
            'platformVersion' => Browser::platformVersion(),
            'browserName' => Browser::browserName(),
            'browserFamily' => Browser::browserFamily(),
            'browserVersion' => Browser::browserVersion(),
            'browserEngine' => Browser::browserEngine(),
            'deviceFamily' => Browser::deviceFamily(),
            'deviceModel' => Browser::deviceModel(),
            'deviceMac' => null,
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

        if (empty($clientLangTag)) {
            return ConfigHelper::fresnsConfigDefaultLangTag();
        }

        $languageStatus = ConfigHelper::fresnsConfigByItemKey('language_status');

        if (! $languageStatus) {
            return ConfigHelper::fresnsConfigDefaultLangTag();
        }

        return $clientLangTag;
    }
}
