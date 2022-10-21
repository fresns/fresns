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
        $defaultLangTag = Cache::remember('fresns_default_langTag', now()->addDays(), function () {
            return Config::where('item_key', 'default_language')->value('item_value');
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
        $defaultLangTag = Cache::remember('fresns_default_timezone', now()->addDays(), function () {
            return Config::where('item_key', 'default_timezone')->value('item_value');
        });

        if (is_null($defaultLangTag)) {
            Cache::forget('fresns_default_timezone');
        }

        return $defaultLangTag;
    }

    // lang tags
    public static function fresnsConfigLangTags()
    {
        $langTagArr = Cache::remember('fresns_lang_tags', now()->addDays(), function () {
            $langArr = Config::where('item_key', 'language_menus')->value('item_value');

            return collect($langArr)->pluck('langTag');
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
     * @return mixed
     */
    public static function fresnsConfigByItemKey(string $itemKey, ?string $langTag = null)
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $configCacheKey = 'fresns_config_'.$itemKey.'_'.$langTag;

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

        if (is_null($itemValue)) {
            Cache::forget($configCacheKey);
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
        $key = reset($itemKeys);
        $configCacheKey = 'fresns_config_keys_'.$key.'_'.$langTag;

        $keysData = Cache::remember($configCacheKey, now()->addDays(), function () use ($itemKeys, $langTag) {
            $data = [];
            foreach ($itemKeys as $itemKey) {
                $data[$itemKey] = ConfigHelper::fresnsConfigByItemKey($itemKey, $langTag);
            }

            return $data ?? null;
        });

        if (is_null($keysData)) {
            Cache::forget($configCacheKey);
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

        $configCacheKey = 'fresns_config_tag_'.$itemTag.'_'.$langTag;

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

        if (is_null($tagData)) {
            Cache::forget($configCacheKey);
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
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink([
                'fileId' => $configValue,
            ]);

            $key = match ($fresnsResp->getData('type')) {
                default => throw new \RuntimeException(),
                File::TYPE_IMAGE => 'imageConfig',
                File::TYPE_IMAGE => 'video',
                File::TYPE_IMAGE => 'audio',
                File::TYPE_IMAGE => 'document',
            };

            $fileUrl = $fresnsResp->getData("{$key}Url");
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
        $language_menus = ConfigHelper::fresnsConfigByItemKey('language_menus');

        if (empty($language_menus)) {
            return null;
        }

        $lengthUnit = 'mi';

        foreach ($language_menus as $menus) {
            if ($menus['langTag'] == $langTag) {
                $lengthUnit = $menus['lengthUnit'];
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
        $language_menus = ConfigHelper::fresnsConfigByItemKey('language_menus');

        if (empty($language_menus)) {
            return null;
        }

        $dateFormat = 'mm/dd/yyyy';

        foreach ($language_menus as $menus) {
            if ($menus['langTag'] == $langTag) {
                $dateFormat = $menus['dateFormat'];
            }
        }

        return $dateFormat;
    }
}
