<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('geotags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('gtid', 32)->unique('gtid');
            $table->unsignedSmallInteger('type')->default(1)->index('geotag_type');
            $table->unsignedBigInteger('cover_file_id')->nullable();
            $table->string('cover_file_url')->nullable();
            $table->string('place_id')->nullable()->index('geotag_place_id');
            $table->string('place_type')->default('unknown')->index('geotag_place_type');
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('name')->nullable();
                    $table->jsonb('description')->nullable();
                    break;

                default:
                    $table->json('name')->nullable();
                    $table->json('description')->nullable();
            }
            $table->unsignedInteger('city_id')->default(0)->index('geotag_city_id');
            $table->unsignedTinyInteger('map_id')->default(1)->index('geotag_map_id');
            $table->decimal('map_longitude', 12, 8)->index('geotag_map_longitude');
            $table->decimal('map_latitude', 12, 8)->index('geotag_map_latitude');
            if (config('database.default') == 'sqlite') {
                $table->text('map_location')->nullable();
            } else {
                $table->geometry('map_location')->spatialIndex('geotag_map_location');
            }
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('district')->nullable();
                    $table->jsonb('address')->nullable();
                    $table->jsonb('more_info')->nullable();
                    break;

                default:
                    $table->json('district')->nullable();
                    $table->json('address')->nullable();
                    $table->json('more_info')->nullable();
            }
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('like_count')->default(0);
            $table->unsignedInteger('dislike_count')->default(0);
            $table->unsignedInteger('follow_count')->default(0);
            $table->unsignedInteger('block_count')->default(0);
            $table->unsignedInteger('post_count')->default(0);
            $table->unsignedInteger('comment_count')->default(0);
            $table->unsignedInteger('post_digest_count')->default(0);
            $table->unsignedInteger('comment_digest_count')->default(0);
            $table->timestamp('last_post_at')->nullable();
            $table->timestamp('last_comment_at')->nullable();
            $table->boolean('is_enabled')->default(1)->index('geotag_is_enabled');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('geotag_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('usage_type');
            $table->unsignedBigInteger('usage_id');
            $table->unsignedInteger('geotag_id')->index('geotag_usage_geotag_id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['usage_type', 'usage_id'], 'geotag_usage_type_id');
        });
    }

    /**
     * Reverse fresns migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geotags');
        Schema::dropIfExists('geotag_usages');
    }
};
