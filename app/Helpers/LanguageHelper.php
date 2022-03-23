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
    public static function fresnsLanguageByTableColumn(string $tableName, string $tableColumn, int $tableId, string $langTag = '')
    {
        if ($langTag) {
            $arr = Language::where(['table_name' => $tableName, 'table_column' => $tableColumn, 'table_id' => $tableId, 'lang_tag' => $langTag])->first();
            $arr = empty($arr) ? [] : $arr->toArray();
        } else {
            $arr = Language::where(['table_name' => $tableName, 'table_column' => $tableColumn, 'table_id' => $tableId])->get()->toArray();
        }

        return $arr;
    }
}
