<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('gid', 32)->unique('gid');
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name', 64);
            $table->string('description')->nullable();
            $table->unsignedTinyInteger('type')->default(2);
            $table->unsignedTinyInteger('type_mode')->default(1);
            $table->unsignedTinyInteger('type_find')->default(1);
            $table->unsignedTinyInteger('type_follow')->default(1);
            $table->string('plugin_unikey', 64)->nullable();
            $table->unsignedTinyInteger('sublevel_public')->default(0);
            $table->unsignedBigInteger('cover_file_id')->nullable();
            $table->string('cover_file_url')->nullable();
            $table->unsignedBigInteger('banner_file_id')->nullable();
            $table->string('banner_file_url')->nullable();
            $table->unsignedSmallInteger('rating')->default(9);
            $table->unsignedTinyInteger('is_recommend')->default(0);
            $table->unsignedSmallInteger('recommend_rating')->default(9);
            $table->json('permissions');
            $table->unsignedInteger('like_count')->default(0);
            $table->unsignedInteger('dislike_count')->default(0);
            $table->unsignedInteger('follow_count')->default(0);
            $table->unsignedInteger('block_count')->default(0);
            $table->unsignedInteger('post_count')->default(0);
            $table->unsignedInteger('comment_count')->default(0);
            $table->unsignedInteger('post_digest_count')->default(0);
            $table->unsignedInteger('comment_digest_count')->default(0);
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
        Schema::dropIfExists('groups');
    }
}
