<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentAppendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment_appends', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('comment_id')->unique('comment_id');
            $table->unsignedTinyInteger('is_plugin_editor')->default(0);
            $table->string('editor_unikey', 64)->nullable();
            $table->unsignedTinyInteger('can_delete')->default(1);
            $table->unsignedTinyInteger('is_close_btn')->default(0);
            $table->unsignedTinyInteger('is_change_btn')->default(0);
            $table->string('btn_name_key', 64)->nullable();
            $table->string('btn_style', 64)->nullable();
            $table->string('ip_location', 64)->nullable();
            $table->json('map_json')->nullable();
            $table->string('map_scale', 8)->nullable();
            $table->string('map_continent_code', 8)->nullable();
            $table->string('map_country_code', 8)->nullable();
            $table->string('map_region_code', 8)->nullable();
            $table->string('map_city_code', 8)->nullable();
            $table->string('map_city', 64)->nullable();
            $table->string('map_zip', 32)->nullable();
            $table->string('map_poi', 128)->nullable();
            $table->string('map_poi_id', 64)->nullable();
            $table->unsignedSmallInteger('edit_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['map_continent_code', 'map_country_code', 'map_region_code', 'map_city_code'], 'continent_country_region_city');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comment_appends');
    }
}
