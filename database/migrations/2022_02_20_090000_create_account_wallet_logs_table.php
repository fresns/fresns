<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountWalletLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_wallet_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('object_unikey', 64);
            $table->unsignedBigInteger('object_id')->nullable();
            $table->unsignedTinyInteger('object_type');
            $table->unsignedBigInteger('object_account_id')->nullable();
            $table->unsignedBigInteger('object_user_id')->nullable();
            $table->unsignedInteger('amount');
            $table->unsignedInteger('transaction_amount');
            $table->unsignedInteger('system_fee');
            $table->unsignedInteger('opening_balance');
            $table->unsignedInteger('closing_balance');
            $table->unsignedTinyInteger('is_enable')->default('1');
            $table->text('remark')->nullable();
            $table->json('more_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_wallet_logs');
    }
}
