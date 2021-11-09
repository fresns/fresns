<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 12)->unique('uuid');
            $table->unsignedTinyInteger('file_type');
            $table->string('file_name', 128);
            $table->string('file_extension', 32);
            $table->string('file_path');
            $table->unsignedSmallInteger('rank_num')->default('9');
            $table->unsignedTinyInteger('is_enable')->default('1');
            $table->unsignedTinyInteger('table_type');
            $table->string('table_name', 64);
            $table->string('table_field', 64);
            $table->unsignedBigInteger('table_id')->nullable();
            $table->string('table_key', 64)->nullable();
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
        Schema::dropIfExists('files');
    }
}
