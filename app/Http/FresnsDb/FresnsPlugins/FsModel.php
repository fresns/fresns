<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsPlugins;

use App\Base\Models\BaseAdminModel;
use Illuminate\Support\Facades\DB;

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
    }

    // Get plugins based on scenarios
    public static function buildSelectOptionsByUnikey($scene): array
    {
        if (empty($scene)) {
            return [];
        }

        $opts = DB::table(FsConfig::CFG_TABLE)
            ->select('unikey AS key', 'name AS text')
            ->where('scene', 'LIKE', "%$scene%")
            ->where('deleted_at', null)
            ->get()->toArray();

        return $opts;
    }

    public function initOrderByFields()
    {
        $orderByFields = [
            // 'rank_num' => 'ASC',
            'id' => 'ASC',
            // 'updated_at' => 'DESC',
        ];

        return $orderByFields;
    }
}
