<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Config;
use App\Models\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class ConfigHelper
{
    // default langTag
    public static function fresnsConfigDefaultLangTag(): string
    {
        $cacheKey = 'fresns_default_langTag';
        $cacheTag = 'fresnsConfigs';
        $defaultLangTag = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($defaultLangTag)) {
            $defaultConfig = Config::where('item_key', 'default_language')->first();

            $defaultLangTag = $defaultConfig?->item_value;

            CacheHelper::put($defaultLangTag, $cacheKey, $cacheTag);
        }

        return $defaultLangTag ?? config('app.locale');
    }

    // lang tags
    public static function fresnsConfigLangTags(): array
    {
        $cacheKey = 'fresns_lang_tags';
        $cacheTag = 'fresnsConfigs';
        $langTagArr = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($langTagArr)) {
            $langArr = Config::where('item_key', 'language_menus')->first()?->item_value;

            if ($langArr) {
                $langTagArr = collect($langArr)->pluck('langTag')->all();
            }

            CacheHelper::put($langTagArr, $cacheKey, $cacheTag);
        }

        return $langTagArr;
    }

    // Get config developer mode
    public static function fresnsConfigDeveloperMode(): array
    {
        $developerMode = Cache::rememberForever('developer_mode', function () {
            $itemData = Config::where('item_key', 'developer_mode')->first();

            return $itemData?->item_value;
        });

        $developerModeArr = [
            'cache' => $developerMode['cache'] ?? true,
            'apiSignature' => $developerMode['apiSignature'] ?? true,
        ];

        return $developerModeArr;
    }

    // Get config value based on Key
    public static function fresnsConfigByItemKey(string $itemKey, ?string $langTag = null): mixed
    {
        $itemData = PrimaryHelper::fresnsModelByFsid('config', $itemKey);

        if (empty($itemData)) {
            return null;
        }

        return $itemData->is_multilingual ? StrHelper::languageContent($itemData->item_value, $langTag) : $itemData->item_value;
    }

    // Get multiple values based on multiple keys
    public static function fresnsConfigByItemKeys(array $itemKeys, ?string $langTag = null): array
    {
        $keysData = [];

        foreach ($itemKeys as $itemKey) {
            $keysData[$itemKey] = ConfigHelper::fresnsConfigByItemKey($itemKey, $langTag);
        }

        return $keysData;
    }

    // Get config api value based on Key
    public static function fresnsConfigApiByItemKey(string $itemKey, ?string $langTag = null): mixed
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_config_api_{$itemKey}_{$langTag}";
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        // get cache
        $apiValue = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($apiValue)) {
            $config = Config::where('item_key', $itemKey)->first();

            if (empty($config)) {
                return null;
            }

            $itemValue = $config->item_value;

            if ($config->is_multilingual) {
                $itemValue = StrHelper::languageContent($config->item_value, $langTag);
            } elseif ($config->item_type == 'file') {
                $itemValue = ConfigHelper::fresnsConfigFileUrlByItemKey($config->item_key);
            } elseif ($config->item_type == 'plugin') {
                $itemValue = PluginHelper::fresnsPluginUrlByFskey($config->item_value) ?? $config->item_value;
            } elseif ($config->item_type == 'plugins') {
                if ($config->item_value) {
                    foreach ($config->item_value as $plugin) {
                        $pluginItem['order'] = (int) $plugin['order'] ?? 9;
                        $pluginItem['code'] = $plugin['code'];
                        $pluginItem['url'] = PluginHelper::fresnsPluginUrlByFskey($plugin['fskey']);

                        $itemArr[] = $pluginItem;
                    }
                    $itemValue = $itemArr;
                }
            }

            $apiValue = $itemValue;

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);
            CacheHelper::put($apiValue, $cacheKey, $cacheTag, null, $cacheTime);
        }

        return $apiValue;
    }

    // Determine the storage type based on the file key value
    public static function fresnsConfigFileValueTypeByItemKey(string $itemKey): string
    {
        $file = ConfigHelper::fresnsConfigByItemKey($itemKey);

        if (StrHelper::isPureInt($file)) {
            return 'ID';
        }

        return 'URL';
    }

    // Get config file url
    public static function fresnsConfigFileUrlByItemKey(string $itemKey, ?string $urlConfig = null): ?string
    {
        $configValue = ConfigHelper::fresnsConfigByItemKey($itemKey);

        if (! $configValue) {
            return null;
        }

        if (ConfigHelper::fresnsConfigFileValueTypeByItemKey($itemKey) == 'URL') {
            $fileUrl = $configValue;

            if (substr($configValue, 0, 1) === '/') {
                $fileUrl = StrHelper::qualifyUrl($configValue);
            }

            return $fileUrl;
        }

        return FileHelper::fresnsFileUrlById($configValue, $urlConfig);
    }

    // Get length units based on langTag
    public static function fresnsConfigLengthUnit(string $langTag): string
    {
        $languageMenus = ConfigHelper::fresnsConfigByItemKey('language_menus');

        if (empty($languageMenus)) {
            return 'km';
        }

        $lengthUnit = 'km'; // km or mi

        foreach ($languageMenus as $menu) {
            if ($menu['langTag'] == $langTag) {
                $lengthUnit = $menu['lengthUnit'];
            }
        }

        return $lengthUnit;
    }

    // Get date format according to langTag
    public static function fresnsConfigDateFormat(string $langTag): string
    {
        $languageMenus = ConfigHelper::fresnsConfigByItemKey('language_menus');

        if (empty($languageMenus)) {
            return 'mm/dd/yyyy';
        }

        $dateFormat = 'mm/dd/yyyy';

        foreach ($languageMenus as $menu) {
            if ($menu['langTag'] == $langTag) {
                $dateFormat = $menu['dateFormat'];
            }
        }

        return $dateFormat;
    }

    // Get file url expire datetime
    public static function fresnsConfigFileUrlExpire(): ?int
    {
        $cacheKey = 'fresns_config_file_url_expire';
        $cacheTag = 'fresnsConfigs';

        $urlExpire = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($urlExpire)) {
            $fileConfigArr = Config::where('item_type', 'file')->get();

            // get file type
            $fileTypeArr = [];
            foreach ($fileConfigArr as $config) {
                if (! StrHelper::isPureInt($config->item_value)) {
                    continue;
                }

                $file = File::where('id', $config->item_value)->first();
                if (! $file) {
                    continue;
                }

                $fileTypeArr[] = $file->type;
            }

            $urlExpire = null;
            if ($fileTypeArr) {
                $fileType = array_unique($fileTypeArr);

                // get anti link expire
                $antiLinkExpire = [];
                foreach ($fileType as $type) {
                    $storageConfig = FileHelper::fresnsFileStorageConfigByType($type);

                    if (! $storageConfig['antiLinkStatus']) {
                        continue;
                    }

                    $antiLinkExpire[] = $storageConfig['antiLinkExpire'];
                }

                if (empty($antiLinkExpire)) {
                    return null;
                }

                $newAntiLinkExpire = array_filter($antiLinkExpire);
                $minAntiLinkExpire = min($newAntiLinkExpire);

                $urlExpire = $minAntiLinkExpire - 1;
            }

            CacheHelper::put($urlExpire, $cacheKey, $cacheTag);
        }

        return $urlExpire;
    }
}
