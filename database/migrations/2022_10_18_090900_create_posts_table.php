<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('pid', 32)->unique('pid');
            $table->unsignedBigInteger('user_id')->index('post_user_id');
            $table->unsignedInteger('group_id')->default(0)->index('post_group_id');
            $table->unsignedInteger('geotag_id')->default(0)->index('post_geotag_id');
            $table->unsignedBigInteger('quoted_post_id')->default(0)->index('post_quoted_id');
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->string('lang_tag', 16)->nullable()->index('post_lang_tag');
            $table->boolean('is_markdown')->default(0);
            $table->boolean('is_anonymous')->default(0)->index('post_is_anonymous');
            $table->unsignedTinyInteger('sticky_state')->default(1)->index('post_sticky_state');
            $table->unsignedTinyInteger('digest_state')->default(1)->index('post_digest_state');
            $table->timestamp('digested_at')->nullable();
            $table->unsignedInteger('view_count')->default(0);
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
            $table->unsignedInteger('quote_count')->default(0);
            $table->unsignedInteger('edit_count')->default(0);
            $table->timestamp('last_edit_at')->nullable();
            $table->timestamp('last_comment_at')->nullable();
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('more_info')->nullable();
                    $table->jsonb('permissions')->nullable();
                    break;

                default:
                    $table->json('more_info')->nullable();
                    $table->json('permissions')->nullable();
            }
            $table->unsignedTinyInteger('rank_state')->default(1);
            $table->boolean('is_enabled')->default(1)->index('post_is_enabled');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('post_auths', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedTinyInteger('auth_type')->default(1);
            $table->unsignedBigInteger('auth_id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['post_id', 'auth_type', 'auth_id'], 'post_auth_type_id');
        });

        Schema::create('post_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id')->index('post_associated_post_id');
            $table->unsignedBigInteger('user_id')->index('post_associated_user_id');
            $table->string('app_fskey', 64);
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('more_info')->nullable();
                    break;

                default:
                    $table->json('more_info')->nullable();
            }
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('post_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hpid', 32)->unique('hpid');
            $table->unsignedTinyInteger('create_type')->default(1);
            $table->unsignedBigInteger('user_id')->index('post_log_user_id');
            $table->unsignedBigInteger('post_id')->nullable()->index('post_log_post_id');
            $table->unsignedBigInteger('quoted_post_id')->nullable();
            $table->unsignedInteger('group_id')->nullable();
            $table->unsignedInteger('geotag_id')->nullable();
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->string('lang_tag', 16)->nullable()->index('post_log_lang_tag');
            $table->boolean('is_markdown')->default(0);
            $table->boolean('is_anonymous')->default(0);
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('location_info')->nullable();
                    $table->jsonb('more_info')->nullable();
                    $table->jsonb('permissions')->nullable();
                    break;

                default:
                    $table->json('location_info')->nullable();
                    $table->json('more_info')->nullable();
                    $table->json('permissions')->nullable();
            }
            $table->boolean('is_enabled')->default(1);
            $table->unsignedTinyInteger('state')->default(1)->index('post_log_state');
            $table->string('reason')->nullable();
            $table->timestamp('submit_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse fresns migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_auths');
        Schema::dropIfExists('post_users');
        Schema::dropIfExists('post_logs');
    }
}
