<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePluginUsagesTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('plugin_usages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('usage_type');
            $table->string('plugin_unikey', 64);
            $table->string('name', 128);
            $table->unsignedBigInteger('icon_file_id')->nullable();
            $table->string('icon_file_url')->nullable();
            $table->string('scene', 16)->nullable();
            $table->unsignedTinyInteger('editor_toolbar')->default(0);
            $table->unsignedTinyInteger('editor_number')->nullable();
            $table->json('data_sources')->nullable();
            $table->unsignedTinyInteger('is_group_admin')->nullable()->default(0);
            $table->unsignedInteger('group_id')->nullable();
            $table->string('roles', 128)->nullable();
            $table->string('parameter', 128)->nullable();
            $table->unsignedSmallInteger('rating')->default(9);
            $table->unsignedTinyInteger('can_delete')->default(1);
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
        Schema::dropIfExists('plugin_usages');
    }
}
