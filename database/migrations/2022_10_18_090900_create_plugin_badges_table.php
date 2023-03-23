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
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('plugin_badges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('plugin_unikey', 64);
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('display_type')->default(1);
            $table->string('value_text', 8)->nullable();
            $table->unsignedSmallInteger('value_number')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->unique(['plugin_unikey', 'user_id'], 'unikey_user');
        });
    }

    /**
     * Reverse fresns migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_badges');
    }
}
