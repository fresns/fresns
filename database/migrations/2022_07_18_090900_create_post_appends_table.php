<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostAppendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_appends', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id')->unique('post_id');
            $table->unsignedTinyInteger('is_plugin_editor')->default(0);
            $table->string('editor_unikey', 64)->nullable();
            $table->unsignedTinyInteger('can_delete')->default(1);
            $table->unsignedTinyInteger('is_allow')->default(0);
            $table->unsignedTinyInteger('allow_proportion')->nullable();
            $table->string('allow_btn_name', 64)->nullable();
            $table->string('allow_plugin_unikey', 64)->nullable();
            $table->unsignedTinyInteger('is_user_list')->default(0);
            $table->string('user_list_name', 128)->nullable();
            $table->string('user_list_plugin_unikey', 64)->nullable();
            $table->unsignedTinyInteger('is_comment')->default(1);
            $table->unsignedTinyInteger('is_comment_public')->default(1);
            $table->unsignedTinyInteger('is_comment_btn')->default(0);
            $table->string('comment_btn_name', 64)->nullable();
            $table->string('comment_btn_style', 64)->nullable();
            $table->string('comment_btn_plugin_unikey', 64)->nullable();
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
            $table->unsignedInteger('edit_count')->default(0);
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
        Schema::dropIfExists('post_appends');
    }
}
