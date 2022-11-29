<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Language;
use App\Models\Seo;
use Illuminate\Support\Facades\Cache;

class LanguageHelper
{
    /**
     * Get language values based on multilingual columns.
     *
     * @param  string  $tableName
     * @param  string  $tableColumn
     * @param  int  $tableId
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsLanguageByTableId(string $tableName, string $tableColumn, int $tableId, ?string $langTag = null)
    {
        $cacheKey = "fresns_{$tableName}_{$tableColumn}_{$tableId}_{$langTag}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $langContentCache = Cache::remember($cacheKey, $cacheTime, function () use ($tableName, $tableColumn, $tableId, $langTag) {
            if (empty($langTag)) {
                $languageArr = Language::where([
                    'table_name' => $tableName,
                    'table_column' => $tableColumn,
                    'table_id' => $tableId,
                ])->get()->toArray();

                if ($languageArr->isEmpty()) {
                    return null;
                }

                foreach ($languageArr as $language) {
                    $item['langTag'] = $language['lang_tag'];
                    $item['langContent'] = $language['lang_content'];
                    $itemArr[] = $item;
                }
                $langContent = $itemArr;
            } else {
                $langContent = Language::where([
                    'table_name' => $tableName,
                    'table_column' => $tableColumn,
                    'table_id' => $tableId,
                    'lang_tag' => $langTag,
                ])->first()->lang_content ?? null;
            }

            return $langContent;
        });

        return $langContentCache;
    }

    /**
     * Get language values based on multilingual table key.
     *
     * @param  string  $tableKey
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsLanguageByTableKey(string $tableKey, ?string $itemType = null, ?string $langTag = null)
    {
        $itemType = $itemType ?: 'string';

        if (empty($langTag)) {
            $languageArr = Language::where([
                'table_name' => 'configs',
                'table_column' => 'item_value',
                'table_key' => $tableKey,
            ])->get();

            if ($languageArr->isEmpty()) {
                return null;
            }

            foreach ($languageArr as $language) {
                $item['langTag'] = $language['lang_tag'];
                $item['langContent'] = $language->formatConfigItemValue($itemType);
                $itemArr[] = $item;
            }

            $langContent = $itemArr;
        } else {
            $langContent = Language::where([
                'table_name' => 'configs',
                'table_column' => 'item_value',
                'table_key' => $tableKey,
                'lang_tag' => $langTag,
            ])->first()?->formatConfigItemValue($itemType);
        }

        return $langContent;
    }

    // get fresns seo language data
    public static function fresnsLanguageSeoDataById(string $type, int $id, ?string $langTag = null)
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();
        $usageType = match ($type) {
            'user' => Seo::TYPE_USER,
            'group' => Seo::TYPE_GROUP,
            'hashtag' => Seo::TYPE_HASHTAG,
            'post' => Seo::TYPE_POST,
            'comment' => Seo::TYPE_COMMENT,
        };

        $cacheKey = "fresns_seo_{$type}_{$id}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $seoData = Cache::remember($cacheKey, $cacheTime, function () use ($usageType, $id) {
            return Seo::where('usage_type', $usageType)->where('usage_id', $id)->get();
        });

        $langContent = $seoData->where('lang_tag', $langTag)->first();

        return $langContent ?? $seoData->first();
    }
}
