<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Language;

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
    public static function fresnsLanguageByTableId(string $tableName, string $tableColumn, int $tableId, string $langTag = '')
    {
        if (empty($langTag)) {
            $languageArr = Language::where([
                'table_name' => $tableName,
                'table_column' => $tableColumn,
                'table_id' => $tableId,
            ])->get()->toArray();
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
    }

    /**
     * Get language values based on multilingual table key.
     *
     * @param  string  $tableKey
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsLanguageByTableKey(string $tableKey, string $langTag = '')
    {
        if (empty($langTag)) {
            $languageArr = Language::where([
                'table_name' => 'configs',
                'table_column' => 'item_value',
                'table_key' => $tableKey,
            ])->get()->toArray();
            foreach ($languageArr as $language) {
                $item['langTag'] = $language['lang_tag'];
                $item['langContent'] = $language['lang_content'];
                $itemArr[] = $item;
            }
            $langContent = $itemArr;
        } else {
            $langContent = Language::where([
                'table_name' => 'configs',
                'table_column' => 'item_value',
                'table_key' => $tableKey,
                'lang_tag' => $langTag,
            ])->first()->lang_content ?? null;
        }

        return $langContent;
    }
}
