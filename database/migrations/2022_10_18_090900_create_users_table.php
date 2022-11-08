<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('uid')->unique('uid');
            $table->string('username', 64)->unique('username');
            $table->string('nickname', 64);
            $table->char('password', 64)->nullable();
            $table->unsignedBigInteger('avatar_file_id')->nullable();
            $table->string('avatar_file_url')->nullable();
            $table->unsignedBigInteger('banner_file_id')->nullable();
            $table->string('banner_file_url')->nullable();
            $table->unsignedTinyInteger('gender')->default(1);
            $table->timestamp('birthday')->nullable();
            $table->text('bio')->nullable();
            $table->string('location', 128)->nullable();
            $table->unsignedTinyInteger('verified_status')->default(0);
            $table->string('verified_desc')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedTinyInteger('dialog_limit')->default(1);
            $table->unsignedTinyInteger('comment_limit')->default(1);
            $table->string('timezone', 16)->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('last_post_at')->nullable();
            $table->timestamp('last_comment_at')->nullable();
            $table->timestamp('last_username_at')->nullable();
            $table->timestamp('last_nickname_at')->nullable();
            $table->unsignedTinyInteger('rank_state')->default(1);
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
        Schema::dropIfExists('users');
    }
}
