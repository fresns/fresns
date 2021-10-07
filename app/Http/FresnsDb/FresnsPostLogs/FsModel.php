<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsPostLogs;

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

    public function getRawSqlQuery()
    {
        $mid = request()->header('mid');
        $query = DB::table(FsConfig::CFG_TABLE)
            ->where('deleted_at', null)
            ->where('member_id', $mid);
        $request = request();
        // 1.Draft + review rejection (state=1+4)
        // 2.Under Review (state=2)
        $status = $request->input('status');
        if ($status == 1) {
            $query->where('state', 1);
            $query->orWhere('state', 4);
        } else {
            $query->where('state', 2);
        }
        $class = $request->input('class');

        if ($class == 1) {
            $query->where('post_id', null);
        } else {
            $query->where('post_id', '!=', null);
        }
        $query->orderBy('id', 'asc');

        return $query;
    }

    // Search for sorted fields
    public function initOrderByFields()
    {
        $orderByFields = [
            'id' => 'DESC',
            // 'updated_at' => 'DESC',
        ];

        return $orderByFields;
    }
}
