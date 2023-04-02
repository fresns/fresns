<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePluginsTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('unikey', 64)->unique('plugin_unikey');
            $table->unsignedTinyInteger('type');
            $table->string('name', 64);
            $table->string('description');
            $table->string('version', 16);
            $table->string('author', 64);
            $table->string('author_link', 128)->nullable();
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('scene')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('scene', 'max')->nullable();
                    break;

                default:
                    $table->json('scene')->nullable();
            }
            $table->string('plugin_host', 128)->nullable();
            $table->string('access_path')->nullable();
            $table->string('settings_path', 128)->nullable();
            $table->unsignedTinyInteger('theme_functions')->default(0);
            $table->unsignedTinyInteger('is_upgrade')->default(0);
            $table->string('upgrade_code', 32)->nullable();
            $table->string('upgrade_version', 16)->nullable();
            $table->unsignedTinyInteger('is_enable')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('plugin_usages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('usage_type');
            $table->string('plugin_unikey', 64);
            $table->string('name', 128);
            $table->unsignedBigInteger('icon_file_id')->nullable();
            $table->string('icon_file_url')->nullable();
            $table->string('scene', 16)->nullable();
            $table->unsignedTinyInteger('editor_toolbar')->default(0);
            $table->unsignedTinyInteger('editor_number')->nullable();
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('data_sources')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('data_sources', 'max')->nullable();
                    break;

                default:
                    $table->json('data_sources')->nullable();
            }
            $table->unsignedTinyInteger('is_group_admin')->nullable()->default(0);
            $table->unsignedInteger('group_id')->nullable();
            $table->string('roles', 128)->nullable();
            $table->string('parameter', 128)->nullable();
            $table->unsignedSmallInteger('rating')->default(9);
            $table->unsignedTinyInteger('can_delete')->default(1);
            $table->unsignedTinyInteger('is_enable')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('plugin_badges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('plugin_unikey', 64);
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('display_type')->default(1);
            $table->string('value_text', 8)->nullable();
            $table->unsignedSmallInteger('value_number')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->unique(['plugin_unikey', 'user_id'], 'unikey_user');
        });

        Schema::create('plugin_callbacks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('plugin_unikey', 64);
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ulid', 64)->unique('callback_ulid');
            $table->unsignedSmallInteger('type')->default(1);
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('content')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('content', 'max')->nullable();
                    break;

                default:
                    $table->json('content')->nullable();
            }
            $table->unsignedTinyInteger('is_use')->default(0);
            $table->string('use_plugin_unikey', 64)->nullable();
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
        Schema::dropIfExists('plugins');
        Schema::dropIfExists('plugin_usages');
        Schema::dropIfExists('plugin_badges');
        Schema::dropIfExists('plugin_callbacks');
    }
}
