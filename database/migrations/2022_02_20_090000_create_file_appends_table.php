<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileAppendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_appends', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('file_id')->unique('file_id');
            $table->string('file_mime');
            $table->unsignedInteger('file_size');
            $table->string('file_md5', 128)->nullable();
            $table->string('file_sha1', 128)->nullable();
            $table->string('file_original_path')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedTinyInteger('platform_id');
            $table->unsignedSmallInteger('image_width')->nullable();
            $table->unsignedSmallInteger('image_height')->nullable();
            $table->unsignedTinyInteger('image_is_long')->nullable();
            $table->unsignedTinyInteger('transcoding_state')->nullable();
            $table->string('transcoding_reason')->nullable();
            $table->unsignedSmallInteger('audio_time')->nullable();
            $table->unsignedSmallInteger('video_time')->nullable();
            $table->string('video_cover')->nullable();
            $table->string('video_gif')->nullable();
            $table->json('more_json')->nullable();
            $table->text('remark')->nullable();
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
        Schema::dropIfExists('file_appends');
    }
}
