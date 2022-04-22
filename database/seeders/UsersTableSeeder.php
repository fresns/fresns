<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('users')->delete();

        \DB::table('users')->insert([
            0 => [
                'id' => 1,
                'account_id' => 1,
                'uid' => 123456,
                'username' => 'fresns',
                'nickname' => 'Fresns Test',
                'password' => null,
                'avatar_file_id' => null,
                'avatar_file_url' => null,
                'decorate_file_id' => null,
                'decorate_file_url' => null,
                'gender' => 0,
                'birthday' => null,
                'bio' => null,
                'location' => null,
                'verified_status' => 1,
                'verified_file_id' => null,
                'verified_file_url' => null,
                'verified_desc' => null,
                'dialog_limit' => 1,
                'comment_limit' => 1,
                'timezone' => null,
                'last_post_at' => null,
                'last_comment_at' => null,
                'last_username_at' => null,
                'last_nickname_at' => null,
                'is_enable' => 1,
                'expired_at' => null,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => null,
            ],
        ]);
    }
}
