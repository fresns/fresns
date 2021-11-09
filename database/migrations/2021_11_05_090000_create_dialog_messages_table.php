<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDialogMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dialog_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dialog_id');
            $table->unsignedBigInteger('send_member_id');
            $table->timestamp('send_deleted_at')->nullable();
            $table->text('message_text')->nullable();
            $table->unsignedBigInteger('file_id')->nullable();
            $table->unsignedBigInteger('recv_member_id');
            $table->timestamp('recv_read_at')->nullable();
            $table->timestamp('recv_deleted_at')->nullable();
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
        Schema::dropIfExists('dialog_messages');
    }
}
