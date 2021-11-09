<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 12)->unique('uuid');
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('parent_id')->default('0');
            $table->unsignedBigInteger('member_id');
            $table->string('types', 128)->index('types');
            $table->text('content')->nullable();
            $table->unsignedTinyInteger('is_brief')->default('0');
            $table->unsignedTinyInteger('is_anonymous')->default('0');
            $table->unsignedTinyInteger('is_lbs')->default('0');
            $table->unsignedTinyInteger('is_sticky')->default('0');
            $table->json('more_json')->nullable();
            $table->unsignedInteger('like_count')->default('0');
            $table->unsignedInteger('follow_count')->default('0');
            $table->unsignedInteger('shield_count')->default('0');
            $table->unsignedInteger('comment_count')->default('0');
            $table->unsignedInteger('comment_like_count')->default('0');
            $table->timestamp('latest_edit_at')->nullable();
            $table->timestamp('latest_comment_at')->nullable();
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
        Schema::dropIfExists('comments');
    }
}
