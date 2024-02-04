<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nmid', 32)->unique('nmid');
            $table->unsignedTinyInteger('type')->index('notification_type');
            $table->unsignedBigInteger('user_id')->index('notification_user_id');
            $table->text('content')->nullable();
            $table->unsignedTinyInteger('is_markdown')->default(0);
            $table->unsignedTinyInteger('is_multilingual')->default(0);
            $table->unsignedTinyInteger('is_mention')->default(0);
            $table->unsignedTinyInteger('is_access_app')->default(0);
            $table->string('app_fskey', 64)->nullable();
            $table->unsignedBigInteger('action_user_id')->nullable();
            $table->unsignedTinyInteger('action_is_anonymous')->default(0);
            $table->unsignedSmallInteger('action_type')->nullable();
            $table->unsignedTinyInteger('action_target')->nullable();
            $table->unsignedBigInteger('action_id')->nullable();
            $table->unsignedBigInteger('action_content_id')->nullable();
            $table->unsignedTinyInteger('is_read')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('a_user_id')->index('conversation_a_user_id');
            $table->unsignedBigInteger('b_user_id')->index('conversation_b_user_id');
            $table->unsignedTinyInteger('a_is_display')->default(1);
            $table->unsignedTinyInteger('b_is_display')->default(1);
            $table->unsignedTinyInteger('a_is_pin')->default(0);
            $table->unsignedTinyInteger('b_is_pin')->default(0);
            $table->timestamp('latest_message_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cmid', 32)->unique('cmid');
            $table->unsignedBigInteger('conversation_id')->index('conversation_message_conversation_id');
            $table->unsignedBigInteger('send_user_id');
            $table->timestamp('send_deleted_at')->nullable();
            $table->unsignedTinyInteger('message_type')->default(1);
            $table->text('message_text')->nullable();
            $table->unsignedBigInteger('message_file_id')->nullable();
            $table->unsignedBigInteger('receive_user_id');
            $table->timestamp('receive_read_at')->nullable();
            $table->timestamp('receive_deleted_at')->nullable();
            $table->unsignedTinyInteger('is_enabled')->default(1);
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
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('conversation_messages');
    }
}
