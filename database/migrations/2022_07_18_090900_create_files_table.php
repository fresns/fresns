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
            $table->string('fid', 32)->unique('fid');
            $table->unsignedTinyInteger('type');
            $table->string('name', 128);
            $table->string('mime', 128)->nullable();
            $table->string('extension', 32);
            $table->unsignedInteger('size')->nullable();
            $table->string('md5', 128)->nullable();
            $table->string('sha', 128)->nullable();
            $table->string('sha_type', 16)->nullable();
            $table->string('path')->unique('path');
            $table->unsignedSmallInteger('image_width')->nullable();
            $table->unsignedSmallInteger('image_height')->nullable();
            $table->unsignedTinyInteger('image_is_long')->default(0);
            $table->unsignedSmallInteger('audio_time')->nullable();
            $table->unsignedSmallInteger('video_time')->nullable();
            $table->string('video_cover_path')->nullable();
            $table->string('video_gif_path')->nullable();
            $table->json('more_json')->nullable();
            $table->unsignedTinyInteger('transcoding_state')->default(1);
            $table->string('transcoding_reason')->nullable();
            $table->string('original_path')->nullable();
            $table->unsignedTinyInteger('is_sensitive')->default(0);
            $table->unsignedTinyInteger('is_enable')->default(1);
            $table->unsignedTinyInteger('physical_deletion')->default(0);
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
        Schema::dropIfExists('files');
    }
}
