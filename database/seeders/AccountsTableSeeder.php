<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountsTableSeeder extends Seeder
{
    /**
     * Fresns seed file.
     */
    public function run(): void
    {
        DB::table('accounts')->delete();

        DB::table('accounts')->insert([
            [
                'id' => 1,
                'aid' => 'fresns',
                'type' => 1,
                'country_code' => '65',
                'pure_phone' => '12345678',
                'phone' => '6512345678',
                'email' => 'admin@admin.com',
                'password' => '$2y$10$NAnHTCpECr8mR./fDq21q./Og2x/JKzhDUw0hX8VYFTuSb2UOrk3i', //password=123456
                'last_login_at' => '2022-10-18 17:00:00',
                'is_verify' => 1,
                'verify_plugin_fskey' => null,
                'verify_real_name' => null,
                'verify_gender' => 1,
                'verify_cert_type' => null,
                'verify_cert_number' => null,
                'verify_identity_type' => null,
                'verify_at' => null,
                'verify_log' => null,
                'is_enabled' => 1,
                'wait_delete' => 0,
                'wait_delete_at' => null,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ]);
    }
}
