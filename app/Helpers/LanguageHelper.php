<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Language;
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

        $langContentCache = Cache::remember($cacheKey, now()->addDays(), function () use ($tableName, $tableColumn, $tableId, $langTag) {
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

        if (is_null($langContentCache)) {
            Cache::forget($cacheKey);
        }

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
}
