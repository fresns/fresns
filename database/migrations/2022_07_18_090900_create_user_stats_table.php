<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique('user_id');
            $table->unsignedInteger('like_user_count')->default(0);
            $table->unsignedInteger('like_group_count')->default(0);
            $table->unsignedInteger('like_hashtag_count')->default(0);
            $table->unsignedInteger('like_post_count')->default(0);
            $table->unsignedInteger('like_comment_count')->default(0);
            $table->unsignedInteger('dislike_user_count')->default(0);
            $table->unsignedInteger('dislike_group_count')->default(0);
            $table->unsignedInteger('dislike_hashtag_count')->default(0);
            $table->unsignedInteger('dislike_post_count')->default(0);
            $table->unsignedInteger('dislike_comment_count')->default(0);
            $table->unsignedInteger('follow_user_count')->default(0);
            $table->unsignedInteger('follow_group_count')->default(0);
            $table->unsignedInteger('follow_hashtag_count')->default(0);
            $table->unsignedInteger('follow_post_count')->default(0);
            $table->unsignedInteger('follow_comment_count')->default(0);
            $table->unsignedInteger('block_user_count')->default(0);
            $table->unsignedInteger('block_group_count')->default(0);
            $table->unsignedInteger('block_hashtag_count')->default(0);
            $table->unsignedInteger('block_post_count')->default(0);
            $table->unsignedInteger('block_comment_count')->default(0);
            $table->unsignedInteger('like_me_count')->default(0);
            $table->unsignedInteger('dislike_me_count')->default(0);
            $table->unsignedInteger('follow_me_count')->default(0);
            $table->unsignedInteger('block_me_count')->default(0);
            $table->unsignedInteger('post_publish_count')->default(0);
            $table->unsignedInteger('post_digest_count')->default(0);
            $table->unsignedInteger('post_like_count')->default(0);
            $table->unsignedInteger('post_dislike_count')->default(0);
            $table->unsignedInteger('post_follow_count')->default(0);
            $table->unsignedInteger('post_block_count')->default(0);
            $table->unsignedInteger('comment_publish_count')->default(0);
            $table->unsignedInteger('comment_like_count')->default(0);
            $table->unsignedInteger('comment_dislike_count')->default(0);
            $table->unsignedInteger('comment_follow_count')->default(0);
            $table->unsignedInteger('comment_block_count')->default(0);
            $table->unsignedInteger('extcredits1')->default(0);
            $table->unsignedInteger('extcredits2')->default(0);
            $table->unsignedInteger('extcredits3')->default(0);
            $table->unsignedInteger('extcredits4')->default(0);
            $table->unsignedInteger('extcredits5')->default(0);
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
        Schema::dropIfExists('user_stats');
    }
}
