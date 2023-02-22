<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Config;
use App\Models\File;
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

    // default timezone
    public static function fresnsConfigDefaultTimezone(): string
    {
        $cacheKey = 'fresns_default_timezone';
        $cacheTag = 'fresnsConfigs';
        $defaultTimezone = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($defaultTimezone)) {
            $defaultConfig = Config::where('item_key', 'default_timezone')->first();

            $defaultTimezone = $defaultConfig?->item_value;

            CacheHelper::put($defaultTimezone, $cacheKey, $cacheTag);
        }

        return $defaultTimezone;
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
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_config_{$itemKey}_{$langTag}";
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $itemValue = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($itemValue)) {
            $itemValue = null;

            $itemData = Config::where('item_key', $itemKey)->first();
            if ($itemData) {
                $itemValue = $itemData->is_multilingual ? LanguageHelper::fresnsLanguageByTableKey($itemData->item_key, $itemData->item_type, $langTag) : $itemData->item_value;
            }

            CacheHelper::put($itemValue, $cacheKey, $cacheTag);
        }

        return $itemValue;
    }

    // Get multiple values based on multiple keys
    public static function fresnsConfigByItemKeys(array $itemKeys, ?string $langTag = null): array
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $key = reset($itemKeys).'_'.end($itemKeys).'_'.count($itemKeys);
        $cacheKey = "fresns_config_keys_{$key}_{$langTag}";

        $keysData = CacheHelper::get($cacheKey, 'fresnsConfigs');

        if (empty($keysData)) {
            $keysData = [];
            foreach ($itemKeys as $itemKey) {
                $keysData[$itemKey] = ConfigHelper::fresnsConfigByItemKey($itemKey, $langTag);
            }

            CacheHelper::put($keysData, $cacheKey, 'fresnsConfigs');
        }

        return $keysData;
    }

    // Get config value based on Tag.
    public static function fresnsConfigByItemTag(string $itemTag, ?string $langTag = null): array
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_config_tag_{$itemTag}_{$langTag}";
        $cacheTag = 'fresnsConfigs';

        $tagData = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($tagData)) {
            $itemData = Config::where('item_tag', $itemTag)->get();

            $itemDataArr = [];
            foreach ($itemData as $item) {
                if ($item->is_multilingual) {
                    $itemDataArr[$item->item_key] = LanguageHelper::fresnsLanguageByTableKey($item->item_key, $item->item_type, $langTag);
                } else {
                    $itemDataArr[$item->item_key] = $item->item_value;
                }
            }

            $tagData = $itemDataArr;

            CacheHelper::put($tagData, $cacheKey, $cacheTag);
        }

        return $tagData;
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
    public static function fresnsConfigFileUrlByItemKey(string $itemKey, ?string $urlConfig = null): string
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
        } else {
            $fileInfo = FileHelper::fresnsFileInfoById($configValue);

            $key = match ($fileInfo['type']) {
                File::TYPE_IMAGE => 'imageConfig',
                File::TYPE_VIDEO => 'video',
                File::TYPE_AUDIO => 'audio',
                File::TYPE_DOCUMENT => 'documentPreview',
                default => 'imageConfig',
            };

            $urlConfig = $urlConfig ?: "{$key}Url";

            $fileUrl = $fileInfo[$urlConfig];
        }

        return $fileUrl;
    }

    // Get length units based on langTag
    public static function fresnsConfigLengthUnit(string $langTag): string
    {
        $languageMenus = ConfigHelper::fresnsConfigByItemKey('language_menus');

        if (empty($languageMenus)) {
            return 'km';
        }

        $lengthUnit = 'mi';

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
