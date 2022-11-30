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
        // Cache::tags(['fresnsConfigs'])
        $defaultLangTag = Cache::remember('fresns_default_langTag', now()->addDays(), function () {
            return Config::where('item_key', 'default_language')->first()?->item_value;
        });

        if (is_null($defaultLangTag)) {
            Cache::forget('fresns_default_langTag');

            $defaultLangTag = config('app.locale');
        }

        return $defaultLangTag;
    }

    // default timezone
    public static function fresnsConfigDefaultTimezone(): string
    {
        // Cache::tags(['fresnsConfigs'])
        $defaultLangTag = Cache::remember('fresns_default_timezone', now()->addDays(), function () {
            return Config::where('item_key', 'default_timezone')->first()?->item_value;
        });

        if (is_null($defaultLangTag)) {
            Cache::forget('fresns_default_timezone');
        }

        return $defaultLangTag;
    }

    // lang tags
    public static function fresnsConfigLangTags()
    {
        // Cache::tags(['fresnsConfigs'])
        $langTagArr = Cache::remember('fresns_lang_tags', now()->addDays(), function () {
            $langArr = Config::where('item_key', 'language_menus')->first()?->item_value;

            if (! $langArr) {
                return null;
            }

            return collect($langArr)->pluck('langTag')->all();
        });

        if (is_null($langTagArr)) {
            Cache::forget('fresns_lang_tags');
        }

        return $langTagArr;
    }

    /**
     * Get config value based on Key.
     *
     * @param  string  $itemKey
     * @param  string  $langTag
     * @return string|null|array
     */
    public static function fresnsConfigByItemKey(string $itemKey, ?string $langTag = null)
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $configCacheKey = "fresns_config_{$itemKey}_{$langTag}";
        $nullCacheKey = CacheHelper::getNullCacheKey($configCacheKey);

        // null cache count
        if (Cache::get($nullCacheKey) > CacheHelper::NULL_CACHE_COUNT) {
            return null;
        }

        // Cache::tags(['fresnsConfigs'])
        $itemValue = Cache::remember($configCacheKey, now()->addDays(), function () use ($itemKey, $langTag) {
            $itemData = Config::where('item_key', $itemKey)->first();
            if (is_null($itemData)) {
                return null;
            }

            if ($itemData->is_multilingual == 1) {
                return LanguageHelper::fresnsLanguageByTableKey($itemData->item_key, $itemData->item_type, $langTag);
            }

            return $itemData->item_value ?? null;
        });

        // null cache count
        if (empty($itemValue)) {
            CacheHelper::nullCacheCount($configCacheKey, $nullCacheKey);
        }

        return $itemValue;
    }

    /**
     * Get multiple values based on multiple keys.
     *
     * @param  array  $itemKeys
     * @param  string  $langTag
     * @return mixed
     */
    public static function fresnsConfigByItemKeys(array $itemKeys, ?string $langTag = null): array
    {
        $key = reset($itemKeys).'_'.end($itemKeys).'_'.count($itemKeys);

        $configCacheKey = "fresns_config_keys_{$key}_{$langTag}";
        $nullCacheKey = CacheHelper::getNullCacheKey($configCacheKey);

        // null cache count
        if (Cache::get($nullCacheKey) > CacheHelper::NULL_CACHE_COUNT) {
            return null;
        }

        // Cache::tags(['fresnsConfigs'])
        $keysData = Cache::remember($configCacheKey, now()->addDays(), function () use ($itemKeys, $langTag) {
            $data = [];
            foreach ($itemKeys as $itemKey) {
                $data[$itemKey] = ConfigHelper::fresnsConfigByItemKey($itemKey, $langTag);
            }

            return $data ?? null;
        });

        // null cache count
        if (empty($keysData)) {
            CacheHelper::nullCacheCount($configCacheKey, $nullCacheKey);
        }

        return $keysData;
    }

    /**
     * Get config value based on Tag.
     *
     * @param  string  $itemTag
     * @param  string  $langTag
     * @return mixed
     */
    public static function fresnsConfigByItemTag(string $itemTag, ?string $langTag = null)
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $configCacheKey = "fresns_config_tag_{$itemTag}_{$langTag}";
        $nullCacheKey = CacheHelper::getNullCacheKey($configCacheKey);

        // null cache count
        if (Cache::get($nullCacheKey) > CacheHelper::NULL_CACHE_COUNT) {
            return null;
        }

        // Cache::tags(['fresnsConfigs'])
        $tagData = Cache::remember($configCacheKey, now()->addDays(), function () use ($itemTag, $langTag) {
            $itemData = Config::where('item_tag', $itemTag)->get();

            $itemDataArr = [];
            foreach ($itemData as $item) {
                if ($item->is_multilingual == 1) {
                    $itemDataArr[$item->item_key] = LanguageHelper::fresnsLanguageByTableKey($item->item_key, $item->item_type, $langTag);
                } else {
                    $itemDataArr[$item->item_key] = $item->item_value;
                }
            }

            return $itemDataArr;
        });

        // null cache count
        if (empty($tagData)) {
            CacheHelper::nullCacheCount($configCacheKey, $nullCacheKey);
        }

        return $tagData;
    }

    /**
     * Determine the storage type based on the file key value.
     *
     * @param  string  $itemKey
     * @return string
     */
    public static function fresnsConfigFileValueTypeByItemKey(string $itemKey)
    {
        $file = ConfigHelper::fresnsConfigByItemKey($itemKey);

        if (StrHelper::isPureInt($file)) {
            return 'ID';
        }

        return 'URL';
    }

    /**
     * Get config file url.
     *
     * @param  string  $itemKey
     * @return string
     */
    public static function fresnsConfigFileUrlByItemKey(string $itemKey)
    {
        $configValue = ConfigHelper::fresnsConfigByItemKey($itemKey);

        if (! $configValue) {
            return null;
        }

        if (ConfigHelper::fresnsConfigFileValueTypeByItemKey($itemKey) == 'URL') {
            $fileUrl = $configValue;
        } else {
            $fileInfo = FileHelper::fresnsFileInfoById($configValue);

            $key = match ($fileInfo['type']) {
                File::TYPE_IMAGE => 'imageConfig',
                File::TYPE_VIDEO => 'video',
                File::TYPE_AUDIO => 'audio',
                File::TYPE_DOCUMENT => 'document',
                default => 'imageConfig',
            };

            $fileUrl = $fileInfo["{$key}Url"];
        }

        return $fileUrl;
    }

    /**
     * Get length units based on langTag.
     *
     * @param  string  $langTag
     * @return string
     */
    public static function fresnsConfigLengthUnit(string $langTag)
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

    /**
     * Get date format according to langTag.
     *
     * @param  string  $langTag
     * @return string
     */
    public static function fresnsConfigDateFormat(string $langTag)
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
}
