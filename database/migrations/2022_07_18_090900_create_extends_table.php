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
            $table->string('eid', 32)->unique('eid');
            $table->unsignedBigInteger('user_id');
            $table->string('plugin_unikey', 64);
            $table->unsignedTinyInteger('type')->default(1);
            $table->text('text_content')->nullable();
            $table->unsignedTinyInteger('text_is_markdown')->default(0);
            $table->unsignedTinyInteger('info_type')->nullable();
            $table->unsignedBigInteger('cover_file_id')->nullable();
            $table->string('cover_file_url')->nullable();
            $table->string('title')->nullable();
            $table->char('title_color', 6)->nullable();
            $table->string('desc_primary')->nullable();
            $table->char('desc_primary_color', 6)->nullable();
            $table->string('desc_secondary')->nullable();
            $table->char('desc_secondary_color', 6)->nullable();
            $table->string('button_name', 64)->nullable();
            $table->char('button_color', 6)->nullable();
            $table->string('parameter', 128)->nullable();
            $table->unsignedTinyInteger('position')->default(2);
            $table->json('more_json')->nullable();
            $table->unsignedTinyInteger('is_enable')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
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
