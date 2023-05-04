<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DomainsTableSeeder extends Seeder
{
    /**
     * Fresns seed file.
     */
    public function run(): void
    {
        DB::table('domains')->delete();

        DB::table('domains')->insert([
            [
                'domain' => 'fresns.com',
                'host' => 'fresns.com',
                'post_count' => 0,
                'comment_count' => 0,
                'is_enabled' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'domain' => 'fresns.org',
                'host' => 'fresns.org',
                'post_count' => 0,
                'comment_count' => 0,
                'is_enabled' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'domain' => 'fresns.net',
                'host' => 'fresns.net',
                'post_count' => 0,
                'comment_count' => 0,
                'is_enabled' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'domain' => 'fresns.com',
                'host' => 'discuss.fresns.com',
                'post_count' => 0,
                'comment_count' => 0,
                'is_enabled' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'domain' => 'fresns.com',
                'host' => 'developer.fresns.com',
                'post_count' => 0,
                'comment_count' => 0,
                'is_enabled' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'domain' => 'fresns.com',
                'host' => 'marketplace.fresns.com',
                'post_count' => 0,
                'comment_count' => 0,
                'is_enabled' => 1,
                'created_at' => '2022-10-18 17:00:00',
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ]);
    }
}
