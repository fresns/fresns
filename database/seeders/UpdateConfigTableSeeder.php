<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateConfigTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $this->updateOrInsertConfig('fresns_version', '1.3.0', 'string', 'systems');
        $this->updateOrInsertConfig('fresns_version_int', 4, 'number', 'systems');
    }

    /**
     * set configs.
     */
    public static function updateOrInsertConfig($itemKey, $itemValue = '', $item_type = 'string', $item_tag = 'systems')
    {
        try {
            $cond = ['item_key' => $itemKey];
            $upInfo = ['item_value' => $itemValue, 'item_type'=>$item_type, 'item_tag'=>$item_tag];
            DB::table('configs')->updateOrInsert($cond, $upInfo);

            return ['code' => '000000', 'message' => 'success'];
        }
        catch (\Exception $e) {
            return ['code' => $e->getCode(), 'message' => $e->getMessage()];
        }
    }
}
