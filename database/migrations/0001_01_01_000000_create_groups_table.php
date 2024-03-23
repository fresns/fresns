<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('gid', 32)->unique('gid');
            $table->unsignedInteger('parent_id')->default(0)->index('group_parent_id');
            $table->unsignedBigInteger('user_id')->nullable();
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('name');
                    $table->jsonb('description')->nullable();
                    break;

                default:
                    $table->json('name');
                    $table->json('description')->nullable();
            }
            $table->unsignedSmallInteger('type')->default(1)->index('group_type');
            $table->unsignedTinyInteger('privacy')->default(1)->index('group_privacy');
            $table->unsignedTinyInteger('private_end_after')->default(1);
            $table->unsignedTinyInteger('visibility')->default(1)->index('group_visibility');
            $table->unsignedTinyInteger('follow_type')->default(1);
            $table->string('follow_app_fskey', 64)->nullable();
            $table->unsignedBigInteger('cover_file_id')->nullable();
            $table->string('cover_file_url')->nullable();
            $table->unsignedBigInteger('banner_file_id')->nullable();
            $table->string('banner_file_url')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(9);
            $table->boolean('is_recommend')->default(0)->index('group_is_recommend');
            $table->unsignedSmallInteger('recommend_sort_order')->default(9);
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('permissions')->nullable();
                    $table->jsonb('more_info')->nullable();
                    break;

                default:
                    $table->json('permissions')->nullable();
                    $table->json('more_info')->nullable();
            }
            $table->unsignedInteger('subgroup_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('like_count')->default(0);
            $table->unsignedInteger('dislike_count')->default(0);
            $table->unsignedInteger('follow_count')->default(0);
            $table->unsignedInteger('block_count')->default(0);
            $table->unsignedInteger('post_count')->default(0);
            $table->unsignedInteger('comment_count')->default(0);
            $table->unsignedInteger('post_digest_count')->default(0);
            $table->unsignedInteger('comment_digest_count')->default(0);
            $table->timestamp('last_post_at')->nullable();
            $table->timestamp('last_comment_at')->nullable();
            $table->boolean('is_enabled')->default(1)->index('group_is_enabled');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('group_admins', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('group_id')->index('group_admin_group_id');
            $table->unsignedBigInteger('user_id')->index('group_admin_user_id');
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
        Schema::dropIfExists('groups');
        Schema::dropIfExists('group_admins');
    }
};
