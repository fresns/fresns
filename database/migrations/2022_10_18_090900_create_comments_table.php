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
            $table->unsignedTinyInteger('rank_state')->default(1);
            $table->unsignedTinyInteger('is_enable')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('comment_appends', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('comment_id')->unique('comment_id');
            $table->unsignedTinyInteger('is_plugin_editor')->default(0);
            $table->string('editor_unikey', 64)->nullable();
            $table->unsignedTinyInteger('can_delete')->default(1);
            $table->unsignedTinyInteger('is_close_btn')->default(0);
            $table->unsignedTinyInteger('is_change_btn')->default(0);
            $table->string('btn_name_key', 64)->nullable();
            $table->string('btn_style', 64)->nullable();
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('more_json')->nullable();
                    $table->jsonb('map_json')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('more_json', 'max')->nullable();
                    $table->nvarchar('map_json', 'max')->nullable();
                    break;

                default:
                    $table->json('more_json')->nullable();
                    $table->json('map_json')->nullable();
            }
            $table->unsignedTinyInteger('map_id')->nullable();
            $table->string('map_continent_code', 8)->nullable();
            $table->string('map_country_code', 8)->nullable();
            $table->string('map_region_code', 8)->nullable()->index('comment_map_region_code');
            $table->string('map_city_code', 8)->nullable()->index('comment_map_city_code');
            $table->string('map_zip', 32)->nullable();
            $table->string('map_poi_id', 64)->nullable()->index('comment_map_poi_id');
            $table->unsignedSmallInteger('edit_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['map_continent_code', 'map_country_code'], 'comment_continent_country');
        });

        Schema::create('comment_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('comment_id')->nullable();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('parent_comment_id')->nullable();
            $table->unsignedTinyInteger('create_type')->default(1);
            $table->unsignedTinyInteger('is_plugin_editor')->default(0);
            $table->string('editor_unikey', 64)->nullable();
            $table->longText('content')->nullable();
            $table->unsignedTinyInteger('is_markdown')->default(0);
            $table->unsignedTinyInteger('is_anonymous')->default(0);
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('map_json')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('map_json', 'max')->nullable();
                    break;

                default:
                    $table->json('map_json')->nullable();
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
        Schema::dropIfExists('comments');
        Schema::dropIfExists('comment_appends');
        Schema::dropIfExists('comment_logs');
    }
}
