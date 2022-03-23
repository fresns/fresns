<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsGroups;

use App\Fresns\Api\Base\Models\BaseCategoryModel;
use App\Fresns\Api\FsDb\FresnsFiles\FresnsFiles;
use App\Fresns\Api\FsDb\FresnsLanguages\FsModel as FresnsLanguagesModel;
use Illuminate\Support\Facades\DB;

class FsModel extends BaseCategoryModel
{
    protected $table = FsConfig::CFG_TABLE;

    // Front-end form field mapping
    public function formFieldsMap()
    {
        return FsConfig::FORM_FIELDS_MAP;
    }

    // New search criteria
    public function getAddedSearchableFields()
    {
        return FsConfig::ADDED_SEARCHABLE_FIELDS;
    }

    // hook - after adding
    public function hookStoreAfter($id)
    {
        $request = request();
        $nameArr = json_decode($request->input('name_arr'), true);
        $descriptionArr = json_decode($request->input('description_arr'), true);
        if ($nameArr) {
            self::insertLanguage($nameArr, FsConfig::CFG_TABLE, FsConfig::FORM_FIELDS_MAP['name'], $id);
        }
        if ($descriptionArr) {
            self::insertLanguage($descriptionArr, FsConfig::CFG_TABLE, FsConfig::FORM_FIELDS_MAP['description'], $id);
        }
    }

    // hook - After Editing
    public function hookUpdateAfter($id)
    {
        $request = request();
        // files table
        if (request()->input('file_id')) {
            FresnsFiles::where('id', request()->input('icon_file_id'))->update([
                'table_type' => 1,
                'table_name' => FsConfig::CFG_TABLE,
                'table_column' => 'id',
                'table_id' => $id,
            ]);
        }
        // languages table
        $nameArr = json_decode($request->input('name_arr'), true);
        $descriptionArr = json_decode($request->input('description_arr'), true);
        FresnsLanguagesModel::where('table_name', FsConfig::CFG_TABLE)->where('table_id', $id)->delete();
        if ($nameArr) {
            self::insertLanguage($nameArr, FsConfig::CFG_TABLE, FsConfig::FORM_FIELDS_MAP['name'], $id);
        }
        if ($descriptionArr) {
            self::insertLanguage($descriptionArr, FsConfig::CFG_TABLE, FsConfig::FORM_FIELDS_MAP['description'], $id);
        }
    }

    public static function insertLanguage($itemArr, $table_name, $table_filed, $table_id)
    {
        $inputArr = [];
        foreach ($itemArr as $v) {
            if ($v['lang_content']) {
                DB::table($table_name)->where('id', $table_id)->update([$table_filed => $v['lang_content']]);
            }
            $item = [];
            $item['lang_tag'] = $v['langTag'];
            $item['lang_content'] = $v['lang_content'];
            $item['table_column'] = $table_filed;
            $item['table_id'] = $table_id;
            $item['table_name'] = $table_name;
            $inputArr[] = $item;
        }
        FresnsLanguagesModel::insert($inputArr);
    }

    // Search for sorted fields
    public function initOrderByFields()
    {
        $sortType = request()->input('sortType', '');
        $sortWay = request()->input('sortWay', 2);
        $sortWayType = $sortWay == 2 ? 'DESC' : 'ASC';
        switch ($sortType) {
            case 'view':
                $orderByFields = [
                    'view_count' => $sortWayType,
                ];

                return $orderByFields;
                break;
            case 'like':
                $orderByFields = [
                    'like_count' => $sortWayType,
                ];

                return $orderByFields;
                break;
            case 'follow':
                $orderByFields = [
                    'follow_count' => $sortWayType,
                ];

                return $orderByFields;
                break;
            case 'block':
                $orderByFields = [
                    'block_count' => $sortWayType,
                ];

                return $orderByFields;
                break;
            case 'post':
                $orderByFields = [
                    'post_count' => $sortWayType,
                ];

                return $orderByFields;
                break;
            case 'digest':
                $orderByFields = [
                    'digest_count' => $sortWayType,
                ];

                return $orderByFields;
                break;
            case 'time':
                $orderByFields = [
                    'created_at' => $sortWayType,
                ];

                return $orderByFields;
                break;

            default:
                $orderByFields = [
                    'rank_num' => 'ASC',
                ];

                return $orderByFields;
                break;
        }
    }
}
