<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archives', function (Blueprint $table) {
            $table->increments('id');
            $table->string('plugin_unikey', 64);
            $table->string('name', 64)->nullable();
            $table->text('description')->nullable();
            $table->string('code', 32)->unique('code');
            $table->unsignedTinyInteger('usage_type');
            $table->string('form_element', 16);
            $table->string('element_type', 16)->nullable();
            $table->json('element_options')->nullable();
            $table->unsignedTinyInteger('file_type')->nullable();
            $table->unsignedTinyInteger('is_multiple')->default(0);
            $table->unsignedTinyInteger('is_required')->default(0);
            $table->string('input_pattern', 128)->nullable();
            $table->unsignedSmallInteger('input_max')->nullable();
            $table->unsignedSmallInteger('input_min')->nullable();
            $table->unsignedSmallInteger('input_maxlength')->nullable();
            $table->unsignedSmallInteger('input_minlength')->nullable();
            $table->unsignedSmallInteger('input_size')->nullable();
            $table->unsignedSmallInteger('input_step')->nullable();
            $table->unsignedSmallInteger('rating')->default(9);
            $table->string('api_type', 16)->default('string');
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
        Schema::dropIfExists('archives');
    }
}
