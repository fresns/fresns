<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('platform_id');
            $table->string('version', 16);
            $table->char('app_id', 8)->nullable();
            $table->unsignedBigInteger('account_id');
            $table->char('account_token', 32);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->char('user_token', 32)->nullable();
            $table->string('scope', 128)->nullable();
            $table->text('payload')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['account_id', 'account_token'], 'account_id_token');
            $table->unique(['user_id', 'user_token'], 'user_id_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('session_tokens');
    }
}
