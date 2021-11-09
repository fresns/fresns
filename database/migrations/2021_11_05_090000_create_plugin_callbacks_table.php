<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePluginCallbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plugin_callbacks', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('plugin_unikey', 64);
            $table->bigInteger('member_id');
            $table->string('uuid', 32)->unique('uuid');
            $table->string('types', 32);
            $table->json('content');
            $table->tinyInteger('status')->default(1);
            $table->string('use_plugin_unikey', 64)->nullable();
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
        Schema::dropIfExists('plugin_callbacks');
    }
}
