<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('aid', 32)->unique('aid');
            $table->unsignedTinyInteger('type')->default(3);
            $table->string('country_code', 8)->nullable();
            $table->string('pure_phone', 128)->nullable();
            $table->string('phone', 128)->nullable()->unique('phone');
            $table->string('email', 128)->nullable()->unique('email');
            $table->string('password', 64)->nullable();
            $table->date('birthday')->nullable();
            $table->timestamp('last_login_at');
            $table->boolean('is_verify')->default(0);
            $table->string('verify_app_fskey', 32)->nullable();
            $table->string('verify_real_name', 128)->nullable();
            $table->unsignedTinyInteger('verify_gender')->default(1);
            $table->string('verify_cert_type', 32)->nullable();
            $table->string('verify_cert_number', 128)->nullable();
            $table->unsignedTinyInteger('verify_identity_type')->nullable();
            $table->timestamp('verify_at')->nullable();
            $table->text('verify_log')->nullable();
            $table->string('fs_connected_id', 26)->nullable()->unique('fs_connected_id');
            $table->string('fs_connected_token', 64)->nullable()->unique('fs_connected_token');
            $table->boolean('is_enabled')->default(1);
            $table->boolean('wait_delete')->default(0);
            $table->timestamp('wait_delete_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('account_connects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedTinyInteger('connect_platform_id');
            $table->string('connect_account_id', 128);
            $table->string('connect_token', 128)->nullable();
            $table->string('connect_refresh_token', 128)->nullable();
            $table->timestamp('refresh_token_expired_at')->nullable();
            $table->string('connect_username', 128)->nullable();
            $table->string('connect_nickname', 128)->nullable();
            $table->string('connect_avatar')->nullable();
            $table->string('app_fskey', 64);
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('more_info')->nullable();
                    break;

                default:
                    $table->json('more_info')->nullable();
            }
            $table->boolean('is_enabled')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->unique(['account_id', 'connect_platform_id'], 'account_connect_platform');
            $table->unique(['connect_platform_id', 'connect_account_id'], 'account_connect_id');
        });

        Schema::create('account_wallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id')->unique('wallet_account_id');
            $table->decimal('balance', 10)->default(0);
            $table->decimal('freeze_amount', 10)->default(0);
            $table->string('password', 64)->nullable();
            $table->boolean('is_enabled')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('account_wallet_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id')->index('wallet_log_account_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedTinyInteger('type');
            $table->string('app_fskey', 64);
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->string('transaction_code', 128)->nullable();
            $table->decimal('amount_total', 10);
            $table->decimal('transaction_amount', 10);
            $table->decimal('system_fee', 10);
            $table->decimal('opening_balance', 10);
            $table->decimal('closing_balance', 10);
            $table->unsignedBigInteger('object_account_id')->nullable();
            $table->unsignedBigInteger('object_user_id')->nullable();
            $table->unsignedBigInteger('object_wallet_log_id')->nullable();
            $table->unsignedTinyInteger('state')->default(1);
            $table->text('remark')->nullable();
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('more_info')->nullable();
                    break;

                default:
                    $table->json('more_info')->nullable();
            }
            $table->timestamp('success_at')->nullable();
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
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('account_connects');
        Schema::dropIfExists('account_wallets');
        Schema::dropIfExists('account_wallet_logs');
    }
}
