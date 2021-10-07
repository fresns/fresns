<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsCommentLogs;

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
        $query = DB::table(FsConfig::CFG_TABLE)->where('deleted_at', null);
        $request = request();
        // 1.Draft + review rejection (state=1+4)
        // 2.Under Review (state=2)
        $status = $request->input('status');
        if ($status == 1) {
            $query->where('state', 1)->orwhere('state', 4);
        } else {
            $query->where('state', 2);
        }
        $class = $request->input('class');
        if ($class == 1) {
            $query->where('comment_id', null);
        } else {
            $query->where('comment_id', '!=', null);
        }
        $query->orderBy('id', 'desc');

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
