<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePluginBadgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plugin_badges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('plugin_unikey', 64)->index('plugin_unikey');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('display_type')->default('1');
            $table->string('value_text', 8)->nullable();
            $table->unsignedInteger('value_number')->nullable();
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
        Schema::dropIfExists('plugin_badges');
    }
}
