<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('uid')->unique('uid');
            $table->string('username', 64)->unique('username');
            $table->string('nickname', 64)->index('nickname');
            $table->string('password', 64)->nullable();
            $table->unsignedBigInteger('avatar_file_id')->nullable();
            $table->string('avatar_file_url')->nullable();
            $table->unsignedBigInteger('banner_file_id')->nullable();
            $table->string('banner_file_url')->nullable();
            $table->unsignedTinyInteger('gender')->default(1);
            $table->timestamp('birthday')->nullable();
            $table->text('bio')->nullable();
            $table->string('location', 128)->nullable();
            $table->unsignedTinyInteger('verified_status')->default(0);
            $table->string('verified_desc')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedTinyInteger('conversation_limit')->default(1);
            $table->unsignedTinyInteger('comment_limit')->default(1);
            $table->string('timezone', 16)->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('last_post_at')->nullable();
            $table->timestamp('last_comment_at')->nullable();
            $table->timestamp('last_username_at')->nullable();
            $table->timestamp('last_nickname_at')->nullable();
            $table->unsignedTinyInteger('rank_state')->default(1);
            $table->unsignedTinyInteger('is_enabled')->default(1);
            $table->unsignedTinyInteger('wait_delete')->default(0);
            $table->timestamp('wait_delete_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

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
            $table->unsignedInteger('view_me_count')->default(0);
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
            $table->unsignedInteger('comment_digest_count')->default(0);
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

        Schema::create('user_extcredits_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('extcredits_log_user_id');
            $table->unsignedTinyInteger('extcredits_id')->index('extcredits_id');
            $table->unsignedTinyInteger('type');
            $table->unsignedInteger('amount');
            $table->unsignedInteger('opening_amount');
            $table->unsignedInteger('closing_amount');
            $table->string('plugin_fskey', 64);
            $table->text('remark')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('role_user_id');
            $table->unsignedInteger('role_id')->index('user_role_id');
            $table->unsignedTinyInteger('is_main')->default(0);
            $table->timestamp('expired_at')->nullable();
            $table->unsignedInteger('restore_role_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('user_likes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('mark_type')->default(1);
            $table->unsignedTinyInteger('like_type');
            $table->unsignedBigInteger('like_id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->unique(['user_id', 'like_type', 'like_id'], 'user_like');
        });

        Schema::create('user_follows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('follow_type');
            $table->unsignedBigInteger('follow_id');
            $table->string('user_note', 128)->nullable();
            $table->unsignedTinyInteger('is_mutual')->default(0);
            $table->unsignedTinyInteger('is_enabled')->default(1);
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->unique(['user_id', 'follow_type', 'follow_id'], 'user_follow');
        });

        Schema::create('user_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('block_type');
            $table->unsignedBigInteger('block_id');
            $table->string('user_note', 128)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->unique(['user_id', 'block_type', 'block_id'], 'user_block');
        });
    }

    /**
     * Reverse fresns migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('user_stats');
        Schema::dropIfExists('user_extcredits_logs');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('user_likes');
        Schema::dropIfExists('user_follows');
        Schema::dropIfExists('user_blocks');
    }
}
