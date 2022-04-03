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
    public static function fresnsConfigByItemKey(string $itemKey, string $langTag = '')
    {
        $itemData = Config::where('item_key', $itemKey)->first();

        if (empty($langTag)) {
            $langTag = Config::where('item_key', 'default_language')->value('item_value');
        }

        if (empty($itemData)) {
            return null;
        } else {
            if ($itemData->is_multilingual == 1) {
                return LanguageHelper::fresnsLanguageByTableKey($itemData->item_key, $langTag);
            } elseif ($itemData->item_type == 'number') {
                return intval($itemData->item_value);
            }
        }

        return $itemData->item_value ?? null;
    }

    /**
     * Get multiple values based on multiple keys.
     *
     * @param  array  $itemKeys
     * @param  string  $langTag
     * @return mixed
     */
    public static function fresnsConfigByItemKeys(array $itemKeys, string $langTag = ''): array
    {
        $itemData = [];

        foreach ($itemKeys as $key) {
            $itemData[$key] = ConfigHelper::fresnsConfigByItemKey($key, $langTag);
        }

        return $itemData;
    }

    /**
     * Get config value based on Tag.
     *
     * @param  string  $itemTag
     * @param  string  $langTag
     * @return mixed
     */
    public static function fresnsConfigByItemTag(string $itemTag, string $langTag = '')
    {
        $itemData = Config::where('item_tag', $itemTag)->get()->toArray();

        $itemDataArr = [];
        foreach ($itemData as $item) {
            $itemDataArr[$item['item_key']] = ConfigHelper::fresnsConfigByItemKey($item['item_key'], $langTag);
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
        if (is_numeric($file)) {
            return 'ID';
        } elseif (preg_match("/^(http:\/\/|https:\/\/).*$/", $file)) {
            return 'URL';
        }

        return 'null';
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
    public static function fresnsConfigLengthUnits(string $langTag)
    {
        $language_menus = ConfigHelper::fresnsConfigByItemKey('language_menus');

        if (empty($language_menus)) {
            return null;
        }

        $lengthUnits = 'mi';

        foreach ($language_menus as $menus) {
            if ($menus['langTag'] == $langTag) {
                $lengthUnits = $menus['lengthUnits'];
            }
        }

        return $lengthUnits;
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
