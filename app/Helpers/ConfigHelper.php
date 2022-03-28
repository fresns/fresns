<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Config;
use App\Models\Language;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class ConfigHelper
{
    /**
     * Get multiple values based on multiple keys.
     *
     * @param  string  $itemKey
     * @param  string  $langTag
     * @return mixed
     */
    public static function fresnsConfigByItemKeys(array $itemKeys, string $langTag = ''): array
    {
        $data = [];

        foreach ($itemKeys as $key) {
            $data[$key] = ConfigHelper::fresnsConfigByItemKey($key, $langTag);
        }

        return $data;
    }

    /**
     * Get config value based on Key.
     *
     * @param  string  $itemKey
     * @param  string  $langTag
     * @return mixed
     */
    public static function fresnsConfigByItemKey(string $itemKey, string $langTag = '')
    {
        return app(ConfigHelper::class)->configByItem($itemKey, $langTag);
    }

    /**
     * @param  string  $item
     * @param  string  $langTag
     * @return array|false|string
     */
    public function configByItem(string $item, string $langTag = '')
    {
        $itemValue = Config::where('item_key', $item)->first();

        if (empty($itemValue) || ($itemValue->is_multilingual == 1 && empty($langTag))) {
            return $itemValue->item_value ?? '';
        } elseif ($itemValue->is_multilingual == 1) {
            return self::getLangContent('configs', $langTag, $item);
        }

        return $itemValue->item_value;
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
        $value = app(ConfigHelper::class)->configByTag($itemTag, $langTag);

        return $value;
    }

    /**
     * @param  string  $item
     * @param  string  $langTag
     * @return array|JsonResource
     */
    public function configByTag(string $item, string $langTag = '')
    {
        $itemValue = Config::select(['item_value', 'is_multilingual', 'item_type', 'item_key'])->where('item_tag', '=', $item)->get();
        if (empty($itemValue)) {
            return [];
        }
        $tagArr = $itemValue->toArray();
        $resultArr = [];
        foreach ($tagArr as $item) {
            if ($item['is_multilingual'] == 1 && ! empty($langTag)) {
                $content = $this->getLangContent('configs', $langTag, $item['item_key']);
                $resultArr[$item['item_key']] = $content;
            } else {
                $resultArr[$item['item_key']] = $item['item_value'];
            }
        }

        return $resultArr;
    }

    /**
     * @param  string  $tableName
     * @param  string  $langTag
     * @param  string  $tableKey
     * @return mixed|string
     */
    protected function getLangContent(string $tableName, string $langTag, string $tableKey)
    {
        $condition = array_filter(['table_name' => $tableName, 'lang_tag' => $langTag, 'table_key'=>$tableKey]);
        $langContent = Language::where($condition)->first();

        return $langContent->lang_content ?? '';
    }

    /**
     * Get length units based on langTag.
     *
     * @param  string  $langTag
     * @return string
     */
    public static function fresnsConfigLengthUnits(string $langTag)
    {
        $language_menus = Config::where('item_key', 'language_menus')->first();
        $langArr = $language_menus->item_value ?? '';
        $lengthUnits = 'mi';
        foreach ($langArr as $item) {
            if ($item['langTag'] == $langTag) {
                $lengthUnits = $item['lengthUnits'];
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
        $language_menus = Config::where('item_key', 'language_menus')->first();
        $langArr = Arr::get($language_menus, 'item_value', []);
        if (empty($langArr)) {
            return '';
        }
        $dateFormat = 'mm/dd/yyyy';
        foreach ($langArr as $item) {
            if ($item['langTag'] == $langTag) {
                $dateFormat = $item['dateFormat'];
            }
        }

        return $dateFormat;
    }

    /**
     * Determine the storage type based on the file key value.
     *
     * @param  string  $itemKey
     * @return string
     */
    public static function fresnsConfigFileByItemKey(string $itemKey)
    {
        $file = Config::where('item_key', $itemKey)->first();
        if ($file && is_numeric($itemKey)) {
            return 'ID';
        } elseif (preg_match("/^(http:\/\/|https:\/\/).*$/", $itemKey)) {
            return 'URL';
        }

        return 'null';
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

        return true;
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

        return true;
    }
}
