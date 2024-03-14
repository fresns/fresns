<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppsTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('fskey', 64)->unique('fskey');
            $table->unsignedTinyInteger('type')->default(1);
            $table->string('name', 64);
            $table->string('description');
            $table->string('version', 16);
            $table->string('author', 64);
            $table->string('author_link', 128)->nullable();
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('panel_usages')->nullable();
                    break;

                default:
                    $table->json('panel_usages')->nullable();
            }
            $table->string('app_host', 128)->nullable();
            $table->string('access_path')->nullable();
            $table->string('settings_path', 128)->nullable();
            $table->boolean('is_upgrade')->default(0);
            $table->string('upgrade_code', 32)->nullable();
            $table->string('upgrade_version', 16)->nullable();
            $table->boolean('is_enabled')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('app_callbacks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('app_fskey', 64)->index('callback_app_fskey');
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->ulid('ulid')->unique('callback_ulid');
            $table->unsignedSmallInteger('type')->default(1);
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('content')->nullable();
                    break;

                default:
                    $table->json('content')->nullable();
            }
            $table->boolean('is_used')->default(0);
            $table->string('used_app_fskey', 64)->nullable()->index('callback_used_app_fskey');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('app_usages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('usage_type')->index('app_usage_type');
            $table->string('app_fskey', 64);
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('name');
                    break;

                default:
                    $table->json('name');
            }
            $table->unsignedBigInteger('icon_file_id')->nullable();
            $table->string('icon_file_url')->nullable();
            $table->string('scene', 16)->nullable();
            $table->boolean('editor_toolbar')->default(0);
            $table->unsignedTinyInteger('editor_number')->nullable();
            $table->boolean('is_group_admin')->nullable()->default(0);
            $table->unsignedInteger('group_id')->nullable()->index('app_usage_group_id');
            $table->string('roles', 128)->nullable();
            $table->string('parameter', 128)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(9);
            $table->boolean('can_delete')->default(1);
            $table->boolean('is_enabled')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('app_badges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('app_fskey', 64);
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('display_type')->default(1);
            $table->unsignedSmallInteger('value_number')->nullable();
            $table->string('value_text', 8)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->unique(['app_fskey', 'user_id'], 'app_badge_user_id');
        });
    }

    /**
     * Reverse fresns migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apps');
        Schema::dropIfExists('app_callbacks');
        Schema::dropIfExists('app_usages');
        Schema::dropIfExists('app_badges');
    }
}
