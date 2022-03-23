<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AccountWalletsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        \DB::table('account_wallets')->delete();
        
        \DB::table('account_wallets')->insert(array (
            0 => 
            array (
                'id' => 1,
                'account_id' => 1,
                'balance' => 0,
                'freeze_amount' => 0,
                'password' => NULL,
                'bank_name' => NULL,
                'swift_code' => NULL,
                'bank_address' => NULL,
                'bank_account' => NULL,
                'bank_status' => 1,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}