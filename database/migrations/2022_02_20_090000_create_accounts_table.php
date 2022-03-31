<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('aid', 12)->unique('aid');
            $table->unsignedTinyInteger('type')->default('3');
            $table->string('country_code', 8)->nullable();
            $table->string('pure_phone', 128)->nullable();
            $table->string('phone', 128)->nullable()->unique('phone');
            $table->string('email', 128)->nullable()->unique('email');
            $table->char('password', 64)->nullable();
            $table->timestamp('last_login_at')->useCurrent();
            $table->string('prove_realname', 128)->nullable();
            $table->unsignedTinyInteger('prove_gender')->default('0');
            $table->string('prove_type', 32)->nullable();
            $table->string('prove_number', 128)->nullable();
            $table->unsignedTinyInteger('prove_verify')->default('1');
            $table->string('verify_plugin_unikey', 32)->nullable();
            $table->unsignedTinyInteger('verify_type')->nullable();
            $table->timestamp('verify_at')->nullable();
            $table->text('verify_log')->nullable();
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
        Schema::dropIfExists('accounts');
    }
}
