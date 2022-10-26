<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cid', 32)->unique('cid');
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('top_parent_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->longText('content')->nullable();
            $table->char('lang_tag', 16)->nullable();
            $table->char('writing_direction', 3)->nullable();
            $table->unsignedTinyInteger('is_markdown')->default(0);
            $table->unsignedTinyInteger('is_anonymous')->default(0);
            $table->unsignedTinyInteger('map_id')->nullable();
            $table->decimal('map_longitude', 12, 8)->nullable();
            $table->decimal('map_latitude', 12, 8)->nullable();
            $table->unsignedTinyInteger('is_sticky')->default(0);
            $table->unsignedTinyInteger('digest_state')->default(1);
            $table->unsignedInteger('like_count')->default(0);
            $table->unsignedInteger('dislike_count')->default(0);
            $table->unsignedInteger('follow_count')->default(0);
            $table->unsignedInteger('block_count')->default(0);
            $table->unsignedInteger('comment_count')->default(0);
            $table->unsignedInteger('comment_digest_count')->default(0);
            $table->unsignedInteger('comment_like_count')->default(0);
            $table->unsignedInteger('comment_dislike_count')->default(0);
            $table->unsignedInteger('comment_follow_count')->default(0);
            $table->unsignedInteger('comment_block_count')->default(0);
            $table->timestamp('latest_edit_at')->nullable();
            $table->timestamp('latest_comment_at')->nullable();
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
        Schema::dropIfExists('comments');
    }
}
