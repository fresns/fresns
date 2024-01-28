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
            $table->unsignedBigInteger('parent_id')->default(0)->index('post_parent_id');
            $table->unsignedBigInteger('user_id')->index('post_user_id');
            $table->unsignedInteger('group_id')->default(0)->index('post_group_id');
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->string('lang_tag', 16)->nullable();
            $table->unsignedTinyInteger('is_markdown')->default(0);
            $table->unsignedTinyInteger('is_anonymous')->default(0);
            $table->decimal('map_longitude', 12, 8)->nullable();
            $table->decimal('map_latitude', 12, 8)->nullable();
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
            $table->unsignedInteger('post_count')->default(0);
            $table->unsignedInteger('edit_count')->default(0);
            $table->timestamp('last_edit_at')->nullable();
            $table->timestamp('last_comment_at')->nullable();
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('more_info')->nullable();
                    $table->jsonb('permissions')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('more_info', 'max')->nullable();
                    $table->nvarchar('permissions', 'max')->nullable();
                    break;

                default:
                    $table->json('more_info')->nullable();
                    $table->json('permissions')->nullable();
            }
            $table->unsignedTinyInteger('rank_state')->default(1);
            $table->unsignedTinyInteger('is_enabled')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('post_auths', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedTinyInteger('type')->default(1);
            $table->unsignedBigInteger('object_id');
            $table->unsignedTinyInteger('is_initial')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['post_id', 'type', 'object_id'], 'post_auth_type_id');
        });

        Schema::create('post_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id')->index('post_affiliated_post_id');
            $table->unsignedBigInteger('user_id')->index('post_affiliated_user_id');
            $table->string('app_fskey', 64);
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('more_info')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('more_info', 'max')->nullable();
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
            $table->unsignedBigInteger('user_id')->index('post_log_user_id');
            $table->unsignedBigInteger('post_id')->nullable()->index('post_log_post_id');
            $table->unsignedBigInteger('parent_post_id')->nullable();
            $table->unsignedTinyInteger('create_type')->default(1);
            $table->unsignedTinyInteger('is_plugin_editor')->default(0);
            $table->string('editor_fskey', 64)->nullable();
            $table->unsignedInteger('group_id')->nullable();
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->unsignedTinyInteger('is_markdown')->default(0);
            $table->unsignedTinyInteger('is_anonymous')->default(0);
            $table->unsignedTinyInteger('is_comment_disabled')->default(0);
            $table->unsignedTinyInteger('is_comment_private')->default(0);
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('map_json')->nullable();
                    $table->jsonb('read_json')->nullable();
                    $table->jsonb('user_list_json')->nullable();
                    $table->jsonb('comment_btn_json')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('map_json', 'max')->nullable();
                    $table->nvarchar('read_json', 'max')->nullable();
                    $table->nvarchar('user_list_json', 'max')->nullable();
                    $table->nvarchar('comment_btn_json', 'max')->nullable();
                    break;

                default:
                    $table->json('map_json')->nullable();
                    $table->json('read_json')->nullable();
                    $table->json('user_list_json')->nullable();
                    $table->json('comment_btn_json')->nullable();
            }
            $table->unsignedTinyInteger('state')->default(1);
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
        Schema::dropIfExists('post_appends');
        Schema::dropIfExists('post_allows');
        Schema::dropIfExists('post_users');
        Schema::dropIfExists('post_logs');
    }
}
