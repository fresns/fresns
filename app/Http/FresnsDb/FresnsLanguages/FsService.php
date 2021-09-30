<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsLanguages;

use App\Base\Services\BaseAdminService;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsService;

class FsService extends BaseAdminService
{
    public function __construct()
    {
        $this->model = new FsModel();
        $this->resource = FsResource::class;
        $this->resourceDetail = FsResourceDetail::class;
    }

    public function common()
    {
        $common = parent::common();

        return $common;
    }

    // Get the corresponding multilingual
    public static function getLanguageByTableId($table, $field, $tableId, $langTag = null)
    {
        $lang_content = FresnsLanguages::where('table_name', $table)->where('table_field', $field)->where('table_id',
        $tableId)->where('lang_tag', $langTag)->value('lang_content');
        if (empty($lang_content)) {
            $langTag = ApiLanguageHelper::getDefaultLanguage();
            $lang_content = FresnsLanguages::where('table_name', $table)->where('table_field', $field)->where('table_id',
            $tableId)->where('lang_tag', $langTag)->value('lang_content');
        }

        return $lang_content;
    }

    public static function getLanguageByTableKey($table, $field, $tableKey, $langTag)
    {
        $lang_content = FresnsLanguages::where('table_name', $table)->where('table_field', $field)->where('table_key', $tableKey)->where('lang_tag', $langTag)->value('lang_content');
        if (empty($lang_content)) {
            $langTag = ApiLanguageHelper::getDefaultLanguage();
            $lang_content = FresnsLanguages::where('table_name', $table)->where('table_field', $field)->where('table_key',
            $tableKey)->where('lang_tag', $langTag)->value('lang_content');
        }

        return $lang_content;
    }

    // Insert into table data
    public static function addLanguages($json, $tableName, $tableField, $tableId)
    {
        FsModel::where('table_name', $tableName)->where('table_field', $tableField)->where('table_id', $tableId)->delete();
        $langArr = json_decode($json, true);
        $itemArr = [];
        foreach ($langArr as $lang) {
            $item = [];
            $item['table_name'] = $tableName;
            $item['table_field'] = $tableField;
            $item['table_id'] = $tableId;
            $item['lang_tag'] = $lang['langTag'];
            $item['lang_content'] = $lang['lang_content'] ?? null;
            $itemArr[] = $item;
        }
        FsModel::insert($itemArr);
    }

    // Get table data
    public static function getLanguages($tableName, $tableField, $tableId)
    {
        $languageArr = FresnsConfigsService::getLanguageStatus();
        $languagesOption = $languageArr['languagesOption'];

        // Search for the corresponding language
        $langMap = FresnsLanguages::where('table_name', $tableName)
            ->where('table_field', $tableField)
            ->where('table_id', $tableId)
            ->pluck('lang_content', 'lang_tag')
            ->toArray();

        $languageArr = [];
        if ($langMap) {
            foreach ($languagesOption as $languages) {
                $it = [];
                $it['key'] = $languages['key'];
                $it['text'] = $languages['text'];
                $it['lang_content'] = $langMap[$languages['key']] ?? '';
                $languageArr[] = $it;
            }
        }

        return $languageArr;
    }
}
