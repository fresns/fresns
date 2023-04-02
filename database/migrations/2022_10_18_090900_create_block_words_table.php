<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockWordsTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('block_words', function (Blueprint $table) {
            $table->increments('id');
            $table->string('word', 32)->unique('block_word');
            $table->unsignedTinyInteger('content_mode')->default(1);
            $table->unsignedTinyInteger('user_mode')->default(1);
            $table->unsignedTinyInteger('conversation_mode')->default(1);
            $table->string('replace_word', 64)->nullable();
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
        Schema::dropIfExists('block_words');
    }
}
