<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AccountsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        \DB::table('accounts')->delete();
        
        \DB::table('accounts')->insert(array (
            0 => 
            array (
                'id' => 1,
                'aid' => 'fresns',
                'type' => 1,
                'country_code' => '1',
                'pure_phone' => '1234567890',
                'phone' => '11234567890',
                'email' => 'admin@admin.com',
                'password' => '$2y$10$NAnHTCpECr8mR./fDq21q./Og2x/JKzhDUw0hX8VYFTuSb2UOrk3i', //password=123456
                'last_login_at' => '2021-10-08 10:00:00',
                'prove_realname' => NULL,
                'prove_gender' => 0,
                'prove_type' => NULL,
                'prove_number' => NULL,
                'prove_verify' => 1,
                'verify_plugin_unikey' => NULL,
                'verify_type' => NULL,
                'verify_log' => NULL,
                'is_enable' => 1,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}