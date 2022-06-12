<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Config;
use App\Models\File;

class ConfigHelper
{
    /**
     * Get config value based on Key.
     *
     * @param  string  $itemKey
     * @param  string  $langTag
     * @return mixed
     */
    public static function fresnsConfigByItemKey(string $itemKey, ?string $langTag = null)
    {
        $cacheKeyConfigItemKey = 'cache_config_item_key_'.$itemKey.$langTag;
        $cacheKeyLangTag = 'cache_langTag_'.$itemKey.$langTag;

        // Cache 1 hour
        $expireAt = now()->addHours(1);

        $langTag = cache()->remember($cacheKeyLangTag, $expireAt, function () use ($langTag) {
            return $langTag ?: Config::where('item_key', 'default_language')->value('item_value');
        });

        if (is_null($langTag)) {
            cache()->forget($cacheKeyLangTag);
        }

        $itemValue = cache()->remember($cacheKeyConfigItemKey, $expireAt, function () use ($itemKey, $langTag) {
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
            cache()->forget($cacheKeyConfigItemKey);
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
        $data = [];
        foreach ($itemKeys as $itemKey) {
            $data[$itemKey] = ConfigHelper::fresnsConfigByItemKey($itemKey, $langTag);
        }

        return $data;
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
        $langTag = $langTag ?: Config::where('item_key', 'default_language')->value('item_value');
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
        if (is_int($file)) {
            return 'ID';
        } elseif (preg_match("/^(http:\/\/|https:\/\/).*$/", $file)) {
            return 'URL';
        }

        return 'ID';
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

    /**
     * Digital Value +1.
     *
     * @param  string  $itemKey
     * @return bool
     */
    public static function fresnsCountAdd(string $itemKey)
    {
        $count = self::fresnsConfigByItemKey($itemKey);
        Config::where('item_key', $itemKey)->update(['item_value'=>$count + 1]);

        return 'true';
    }

    /**
     * Digital Value -1.
     *
     * @param  string  $itemKey
     * @return bool
     */
    public static function fresnsCountMinus(string $itemKey)
    {
        $count = self::fresnsConfigByItemKey($itemKey);
        Config::where('item_key', $itemKey)->update(['item_value'=>$count - 1]);

        return 'true';
    }
}
