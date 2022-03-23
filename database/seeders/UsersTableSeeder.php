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
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'account_id' => 1,
                'uid' => 123456,
                'username' => 'fresns',
                'nickname' => 'Fresns Test',
                'password' => NULL,
                'avatar_file_id' => NULL,
                'avatar_file_url' => NULL,
                'decorate_file_id' => NULL,
                'decorate_file_url' => NULL,
                'gender' => 0,
                'birthday' => NULL,
                'bio' => NULL,
                'location' => NULL,
                'verified_status' => 1,
                'verified_file_id' => NULL,
                'verified_file_url' => NULL,
                'verified_desc' => NULL,
                'dialog_limit' => 1,
                'comment_limit' => 1,
                'timezone' => NULL,
                'language' => NULL,
                'last_post_at' => NULL,
                'last_comment_at' => NULL,
                'last_username_at' => NULL,
                'last_nickname_at' => NULL,
                'is_enable' => 1,
                'expired_at' => NULL,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}