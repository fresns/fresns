<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extends', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('eid', 12)->unique('eid');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('post_id')->nullable();
            $table->string('plugin_unikey', 64)->index('plugin_unikey');
            $table->unsignedTinyInteger('frame');
            $table->unsignedTinyInteger('position')->default('2');
            $table->text('text_content')->nullable();
            $table->json('text_files')->nullable();
            $table->unsignedBigInteger('cover_file_id')->nullable();
            $table->string('cover_file_url', 255)->nullable();
            $table->string('title', 255)->nullable();
            $table->char('title_color', 6)->nullable();
            $table->string('desc_primary', 255)->nullable();
            $table->char('desc_primary_color', 6)->nullable();
            $table->string('desc_secondary', 255)->nullable();
            $table->char('desc_secondary_color', 6)->nullable();
            $table->string('btn_name', 64)->nullable();
            $table->char('btn_color', 6)->nullable();
            $table->tinyInteger('extend_type');
            $table->tinyInteger('extend_target')->default(1);
            $table->string('extend_value', 255);
            $table->unsignedTinyInteger('extend_support')->nullable();
            $table->json('more_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extends');
    }
}
