<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountConnectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_connects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedTinyInteger('connect_id');
            $table->string('connect_token', 128);
            $table->string('connect_refresh_token', 128)->nullable();
            $table->string('connect_username', 128)->nullable();
            $table->string('connect_nickname', 128);
            $table->string('connect_avatar')->nullable();
            $table->string('plugin_unikey', 64);
            $table->unsignedTinyInteger('is_enable')->default(1);
            $table->json('more_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->unique(['connect_id', 'connect_token'], 'connect_id_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_connects');
    }
}
