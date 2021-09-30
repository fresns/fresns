<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsPluginUsages;

use App\Base\Models\BaseAdminModel;
use App\Http\FresnsDb\FresnsFiles\FresnsFiles;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguagesService;
use App\Http\FresnsDb\FresnsLanguages\FsModel as FresnsLanguagesModel;

class FsModel extends BaseAdminModel
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
        // files
        if (request()->input('icon_file_id')) {
            FresnsFiles::where('id', request()->input('icon_file_id'))->update([
                'table_type' => 1,
                'table_name' => FsConfig::CFG_TABLE,
                'table_field' => 'id',
                'table_id' => $id,
            ]);
        }

        // languages
        $nameArr = json_decode(request()->input('name'), true);
        $inputArr = [];
        foreach ($nameArr as $v) {
            $item = [];
            $item['lang_tag'] = $v['langTag'];
            $item['lang_content'] = $v['lang_content'];
            $item['table_field'] = FsConfig::FORM_FIELDS_MAP['name'];
            $item['table_id'] = $id;
            $item['table_name'] = FsConfig::CFG_TABLE;
            $inputArr[] = $item;
        }
        FresnsLanguagesModel::insert($inputArr);
    }

    // hook - After Editing
    public function hookUpdateAfter($id)
    {
        // files
        if (request()->input('icon_file_id')) {
            FresnsFiles::where('id', request()->input('icon_file_id'))->update([
                'table_type' => 1,
                'table_name' => FsConfig::CFG_TABLE,
                'table_field' => 'id',
                'table_id' => $id,
            ]);
        }
        // languages
        $nameArr = json_decode(request()->input('name'), true);
        $inputArr = [];
        FresnsLanguagesModel::where('table_name', FsConfig::CFG_TABLE)->where('table_field', FsConfig::FORM_FIELDS_MAP['name'])->where('table_id', $id)->delete();
        foreach ($nameArr as $v) {
            $item = [];
            $item['lang_tag'] = $v['langTag'];
            $item['lang_content'] = $v['lang_content'];
            $item['table_field'] = FsConfig::FORM_FIELDS_MAP['name'];
            $item['table_id'] = $id;
            $item['table_name'] = FsConfig::CFG_TABLE;
            // $item['alias_key'] = $v['nickname'];
            $inputArr[] = $item;
        }
        FresnsLanguagesModel::insert($inputArr);
    }
}
