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
                'nickname' => 'Fresns',
                'password' => null,
                'avatar_file_id' => null,
                'avatar_file_url' => null,
                'banner_file_id' => null,
                'banner_file_url' => null,
                'gender' => 0,
                'birthday' => null,
                'bio' => 'Fresns is a free and open source social network service software, a general-purpose community product designed for cross-platform, and supports flexible and diverse content forms. It conforms to the trend of the times, satisfies a variety of operating scenarios, is more open and easier to re-development.',
                'location' => null,
                'verified_status' => 0,
                'verified_desc' => null,
                'verified_at' => null,
                'dialog_limit' => 1,
                'comment_limit' => 1,
                'timezone' => null,
                'expired_at' => null,
                'last_post_at' => null,
                'last_comment_at' => null,
                'last_username_at' => null,
                'last_nickname_at' => null,
                'is_enable' => 1,
                'wait_delete' => 0,
                'wait_delete_at' => null,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ]);
    }
}
