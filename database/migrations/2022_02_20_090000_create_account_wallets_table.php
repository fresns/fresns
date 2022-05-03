<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_wallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id')->unique('account_id');
            $table->unsignedInteger('balance')->default('0');
            $table->unsignedInteger('freeze_amount')->default('0');
            $table->char('password', 64)->nullable();
            $table->string('bank_name', 64)->nullable();
            $table->string('swift_code', 32)->nullable();
            $table->string('bank_address', 255)->nullable();
            $table->string('bank_account', 128)->nullable();
            $table->unsignedTinyInteger('bank_status')->default('1');
            $table->unsignedTinyInteger('is_enable')->default('1');
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
        Schema::dropIfExists('account_wallets');
    }
}
