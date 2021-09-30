<?php

namespace App\Http\FresnsDb\FresnsMembers;

use App\Base\Models\BaseAdminModel;

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
