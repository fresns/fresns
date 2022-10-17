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
            $table->string('aid', 32)->unique('aid');
            $table->unsignedTinyInteger('type')->default(3);
            $table->string('country_code', 8)->nullable();
            $table->string('pure_phone', 128)->nullable();
            $table->string('phone', 128)->nullable()->unique('phone');
            $table->string('email', 128)->nullable()->unique('email');
            $table->char('password', 64)->nullable();
            $table->timestamp('last_login_at');
            $table->unsignedTinyInteger('is_verify')->default(0);
            $table->string('verify_plugin_unikey', 32)->nullable();
            $table->string('verify_real_name', 128)->nullable();
            $table->unsignedTinyInteger('verify_gender')->default(0);
            $table->string('verify_cert_type', 32)->nullable();
            $table->string('verify_cert_number', 128)->nullable();
            $table->unsignedTinyInteger('verify_identity_type')->nullable();
            $table->timestamp('verify_at')->nullable();
            $table->text('verify_log')->nullable();
            $table->unsignedTinyInteger('is_enable')->default(1);
            $table->unsignedTinyInteger('wait_delete')->default(0);
            $table->timestamp('wait_delete_at')->nullable();
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
        Schema::dropIfExists('accounts');
    }
}
