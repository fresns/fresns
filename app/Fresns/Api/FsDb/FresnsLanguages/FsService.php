<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsLanguages;

use App\Fresns\Api\Base\Services\BaseAdminService;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigsService;
use App\Fresns\Api\Helpers\ApiLanguageHelper;

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
        $lang_content = FresnsLanguages::where('table_name', $table)->where('table_column', $field)->where('table_id', $tableId)->where('lang_tag', $langTag)->value('lang_content');
        if (empty($lang_content)) {
            $langTag = ApiLanguageHelper::getDefaultLanguage();
            $lang_content = FresnsLanguages::where('table_name', $table)->where('table_column', $field)->where('table_id',
            $tableId)->where('lang_tag', $langTag)->value('lang_content');
        }

        return $lang_content;
    }

    public static function getLanguageByTableKey($table, $field, $tableKey, $langTag)
    {
        $lang_content = FresnsLanguages::where('table_name', $table)->where('table_column', $field)->where('table_key', $tableKey)->where('lang_tag', $langTag)->value('lang_content');
        if (empty($lang_content)) {
            $langTag = ApiLanguageHelper::getDefaultLanguage();
            $lang_content = FresnsLanguages::where('table_name', $table)->where('table_column', $field)->where('table_key',
            $tableKey)->where('lang_tag', $langTag)->value('lang_content');
        }

        return $lang_content;
    }

    // Insert into table data
    public static function addLanguages($json, $tableName, $tableColumn, $tableId)
    {
        FsModel::where('table_name', $tableName)->where('table_column', $tableColumn)->where('table_id', $tableId)->delete();
        $langArr = json_decode($json, true);
        $itemArr = [];
        foreach ($langArr as $lang) {
            $item = [];
            $item['table_name'] = $tableName;
            $item['table_column'] = $tableColumn;
            $item['table_id'] = $tableId;
            $item['lang_tag'] = $lang['langTag'];
            $item['lang_content'] = $lang['lang_content'] ?? null;
            $itemArr[] = $item;
        }
        FsModel::insert($itemArr);
    }

    // Get table data
    public static function getLanguages($tableName, $tableColumn, $tableId)
    {
        $languageArr = FresnsConfigsService::getLanguageStatus();
        $languagesOption = $languageArr['languagesOption'];

        // Search for the corresponding language
        $langMap = FresnsLanguages::where('table_name', $tableName)
            ->where('table_column', $tableColumn)
            ->where('table_id', $tableId)
            ->pluck('lang_content', 'lang_tag')
            ->toArray();

        $languageArr = [];
        if ($langMap) {
            foreach ($languagesOption as $languages) {
                $it = [];
                $it['key'] = $languages['key'];
                $it['text'] = $languages['text'];
                $it['lang_content'] = $langMap[$languages['key']] ?? null;
                $languageArr[] = $it;
            }
        }

        return $languageArr;
    }
}
