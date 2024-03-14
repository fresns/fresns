<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitiesTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('type')->default(1)->index('city_type');
            $table->unsignedBigInteger('cover_file_id')->nullable();
            $table->string('cover_file_url')->nullable();
            $table->unsignedInteger('parent_id')->default(0)->index('city_parent_id');
            $table->string('continent_code', 8)->nullable()->index('city_continent_code');
            $table->string('country_code', 8)->nullable();
            $table->string('region_code', 8)->nullable();
            $table->string('city_code', 8)->nullable();
            $table->string('zip', 32)->nullable();
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('continent')->nullable();
                    $table->jsonb('country')->nullable();
                    $table->jsonb('region')->nullable();
                    $table->jsonb('city')->nullable();
                    break;

                default:
                    $table->json('continent')->nullable();
                    $table->json('country')->nullable();
                    $table->json('region')->nullable();
                    $table->json('city')->nullable();
            }
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['continent_code', 'country_code'], 'city_continent_country');
            $table->index(['continent_code', 'country_code', 'region_code'], 'city_continent_country_region');
            $table->unique(['continent_code', 'country_code', 'region_code', 'city_code'], 'city_index');
        });
    }

    /**
     * Reverse fresns migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
}
