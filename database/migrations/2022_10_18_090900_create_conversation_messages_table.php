<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationMessagesTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('send_user_id');
            $table->timestamp('send_deleted_at')->nullable();
            $table->unsignedTinyInteger('message_type')->default(1);
            $table->text('message_text')->nullable();
            $table->unsignedBigInteger('message_file_id')->nullable();
            $table->unsignedBigInteger('receive_user_id');
            $table->timestamp('receive_read_at')->nullable();
            $table->timestamp('receive_deleted_at')->nullable();
            $table->unsignedTinyInteger('is_enable')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse fresns migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_messages');
    }
}
