<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AccountWalletsTableSeeder extends Seeder
{
    /**
     * Fresns seed file.
     */
    public function run(): void
    {
        \DB::table('account_wallets')->delete();

        \DB::table('account_wallets')->insert([
            0 => [
                'id' => 1,
                'account_id' => 1,
                'balance' => '0.00',
                'freeze_amount' => '0.00',
                'password' => null,
                'bank_name' => null,
                'swift_code' => null,
                'bank_address' => null,
                'bank_account' => null,
                'bank_status' => 1,
                'is_enable' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ]);
    }
}
