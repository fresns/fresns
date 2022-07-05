<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDialogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dialogs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('a_user_id');
            $table->unsignedBigInteger('b_user_id');
            $table->unsignedBigInteger('latest_message_id')->nullable();
            $table->timestamp('latest_message_time')->nullable();
            $table->text('latest_message_text')->nullable();
            $table->unsignedTinyInteger('a_is_read')->default(1);
            $table->unsignedTinyInteger('b_is_read')->default(1);
            $table->unsignedTinyInteger('a_is_display')->default(1);
            $table->unsignedTinyInteger('b_is_display')->default(1);
            $table->unsignedTinyInteger('a_is_deactivate')->default(1);
            $table->unsignedTinyInteger('b_is_deactivate')->default(1);
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
        Schema::dropIfExists('dialogs');
    }
}
