<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cid', 32)->unique('cid');
            $table->unsignedBigInteger('post_id')->index('comment_post_id');
            $table->unsignedBigInteger('top_parent_id')->default(0)->index('comment_top_parent_id');
            $table->unsignedBigInteger('parent_id')->default(0)->index('comment_parent_id');
            $table->unsignedBigInteger('user_id')->index('comment_user_id');
            $table->longText('content')->nullable();
            $table->string('lang_tag', 16)->nullable();
            $table->string('writing_direction', 3)->nullable();
            $table->unsignedTinyInteger('is_markdown')->default(0);
            $table->unsignedTinyInteger('is_anonymous')->default(0);
            $table->decimal('map_longitude', 12, 8)->nullable();
            $table->decimal('map_latitude', 12, 8)->nullable();
            $table->unsignedTinyInteger('is_sticky')->default(0);
            $table->unsignedTinyInteger('digest_state')->default(1)->index('comment_digest_state');
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
            $table->unsignedSmallInteger('edit_count')->default(0);
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

        Schema::create('comment_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hcid', 32)->unique('hcid');
            $table->unsignedTinyInteger('create_type')->default(1);
            $table->unsignedBigInteger('user_id')->index('comment_log_user_id');
            $table->unsignedBigInteger('post_id')->index('comment_log_post_id');
            $table->unsignedBigInteger('comment_id')->nullable()->index('comment_log_comment_id');
            $table->unsignedBigInteger('parent_comment_id')->nullable();
            $table->unsignedInteger('geotag_id')->nullable();
            $table->longText('content')->nullable();
            $table->unsignedTinyInteger('is_markdown')->default(0);
            $table->unsignedTinyInteger('is_anonymous')->default(0);
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('location_info')->nullable();
                    $table->jsonb('more_info')->nullable();
                    $table->jsonb('permissions')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('location_info', 'max')->nullable();
                    $table->nvarchar('more_info', 'max')->nullable();
                    $table->nvarchar('permissions', 'max')->nullable();
                    break;

                default:
                    $table->json('location_info')->nullable();
                    $table->json('more_info')->nullable();
                    $table->json('permissions')->nullable();
            }
            $table->unsignedTinyInteger('is_enabled')->default(1);
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
        Schema::dropIfExists('comments');
        Schema::dropIfExists('comment_logs');
    }
}
