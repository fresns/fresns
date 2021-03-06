<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotifiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('type');
            $table->text('content')->nullable();
            $table->unsignedTinyInteger(' is_markdown')->default(0);
            $table->string('plugin_unikey', 64)->nullable();
            $table->unsignedTinyInteger('is_access_plugin')->default(0);
            $table->unsignedBigInteger('action_user_id')->nullable();
            $table->unsignedTinyInteger('action_type')->nullable();
            $table->unsignedBigInteger('action_id')->nullable();
            $table->unsignedTinyInteger('is_read')->default(0);
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
        Schema::dropIfExists('notifies');
    }
}
