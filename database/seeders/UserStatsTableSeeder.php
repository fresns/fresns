<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserStatsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('user_stats')->delete();

        \DB::table('user_stats')->insert([
            0 => [
                'id' => 1,
                'user_id' => 1,
                'like_user_count' => 0,
                'like_group_count' => 0,
                'like_hashtag_count' => 0,
                'like_post_count' => 0,
                'like_comment_count' => 0,
                'follow_user_count' => 0,
                'follow_group_count' => 0,
                'follow_hashtag_count' => 0,
                'follow_post_count' => 0,
                'follow_comment_count' => 0,
                'block_user_count' => 0,
                'block_group_count' => 0,
                'block_hashtag_count' => 0,
                'block_post_count' => 0,
                'block_comment_count' => 0,
                'like_me_count' => 0,
                'follow_me_count' => 0,
                'block_me_count' => 0,
                'post_publish_count' => 0,
                'post_like_count' => 0,
                'comment_publish_count' => 0,
                'comment_like_count' => 0,
                'extcredits1' => 0,
                'extcredits2' => 0,
                'extcredits3' => 0,
                'extcredits4' => 0,
                'extcredits5' => 0,
                'created_at' => '2021-10-08 10:00:00',
                'updated_at' => '2021-10-08 10:00:00',
                'deleted_at' => null,
            ],
        ]);
    }
}
