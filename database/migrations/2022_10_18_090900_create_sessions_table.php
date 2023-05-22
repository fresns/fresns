<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('session_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('platform_id');
            $table->string('name', 64);
            $table->unsignedTinyInteger('type')->default(1);
            $table->string('plugin_fskey', 32)->nullable();
            $table->string('app_id', 8)->unique('app_id');
            $table->string('app_secret', 32);
            $table->unsignedTinyInteger('is_read_only')->default(0);
            $table->unsignedTinyInteger('is_enabled')->default(1);
            $table->text('remark')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('session_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('platform_id');
            $table->string('version', 16);
            $table->string('app_id', 8)->nullable()->index('token_app_id');
            $table->unsignedBigInteger('account_id');
            $table->string('account_token', 32);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_token', 32)->nullable();
            $table->string('scope', 128)->nullable();
            $table->text('payload')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['account_id', 'account_token'], 'account_id_token');
            $table->unique(['user_id', 'user_token'], 'user_id_token');
        });

        Schema::create('session_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('plugin_fskey', 64)->default('Fresns')->index('log_plugin_fskey');
            $table->unsignedTinyInteger('type')->default(1)->index('log_type');
            $table->unsignedTinyInteger('platform_id');
            $table->string('version', 16);
            $table->string('app_id', 8)->nullable()->index('log_app_id');
            $table->string('lang_tag', 16)->nullable();
            $table->string('object_name', 128);
            $table->string('object_action', 128)->nullable();
            $table->unsignedTinyInteger('object_result');
            $table->unsignedBigInteger('object_order_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable()->index('log_account_id');
            $table->unsignedBigInteger('user_id')->nullable()->index('log_user_id');
            $table->string('device_token', 128)->nullable();
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('device_info')->nullable();
                    $table->jsonb('more_json')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('device_info', 'max')->nullable();
                    $table->nvarchar('more_json', 'max')->nullable();
                    break;

                default:
                    $table->json('device_info')->nullable();
                    $table->json('more_json')->nullable();
            }
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
        Schema::dropIfExists('session_keys');
        Schema::dropIfExists('session_tokens');
        Schema::dropIfExists('session_logs');
    }
}
