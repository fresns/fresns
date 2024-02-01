<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeotagsTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('geotags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('gtid')->unique('gtid');
            $table->string('place_id')->nullable()->unique('geotag_place_id');
            $table->string('place_type')->default('unknown')->index('geotag_place_type');
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('name')->nullable();
                    $table->jsonb('description')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('name', 'max')->nullable();
                    $table->nvarchar('description', 'max')->nullable();
                    break;

                default:
                    $table->json('name')->nullable();
                    $table->json('description')->nullable();
            }
            $table->unsignedTinyInteger('map_id')->default(1);
            $table->decimal('map_longitude', 12, 8)->index('geotag_map_longitude');
            $table->decimal('map_latitude', 12, 8)->index('geotag_map_latitude');
            switch (config('database.default')) {
                case 'pgsql':
                    $table->point('map_location')->index('geotag_map_location');
                    $table->jsonb('location_info')->nullable();
                    break;

                case 'sqlsrv':
                    $table->geography('map_location')->index('geotag_map_location');
                    $table->nvarchar('location_info', 'max')->nullable();
                    break;

                default:
                    $table->point('map_location')->index('geotag_map_location');
                    $table->json('location_info')->nullable();
            }
            $table->unsignedSmallInteger('type')->default(1)->index('geotag_type');
            $table->unsignedBigInteger('cover_file_id')->nullable();
            $table->string('cover_file_url')->nullable();
            $table->string('continent_code', 8)->nullable();
            $table->string('country_code', 8)->nullable();
            $table->string('region_code', 8)->nullable()->index('geotag_region_code');
            $table->string('city_code', 8)->nullable()->index('geotag_city_code');
            $table->string('zip', 32)->nullable();
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('more_info')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('more_info', 'max')->nullable();
                    break;

                default:
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
            $table->unsignedTinyInteger('is_enabled')->default(1)->index('geotag_is_enabled');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['continent_code', 'country_code'], 'geotag_continent_country');
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
}
