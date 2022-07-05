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
            $table->unsignedTinyInteger('type');
            $table->string('plugin_unikey', 64);
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->string('transaction_code', 128)->nullable();
            $table->unsignedDecimal('amount_total', 10);
            $table->unsignedDecimal('transaction_amount', 10);
            $table->unsignedDecimal('system_fee', 10);
            $table->unsignedDecimal('opening_balance', 10);
            $table->unsignedDecimal('closing_balance', 10);
            $table->unsignedBigInteger('object_account_id')->nullable();
            $table->unsignedBigInteger('object_user_id')->nullable();
            $table->unsignedTinyInteger('is_enable')->default(1);
            $table->text('remark')->nullable();
            $table->json('more_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
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
