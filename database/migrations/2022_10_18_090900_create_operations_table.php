<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperationsTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('operations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('type')->default(1);
            $table->string('code', 32);
            $table->string('style', 64);
            $table->string('name', 128)->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('image_file_id')->nullable();
            $table->string('image_file_url')->nullable();
            $table->unsignedBigInteger('image_active_file_id')->nullable();
            $table->string('image_active_file_url')->nullable();
            $table->unsignedTinyInteger('display_type')->default(1);
            $table->string('plugin_fskey', 64);
            $table->unsignedTinyInteger('is_enabled')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('operation_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('usage_type');
            $table->unsignedBigInteger('usage_id');
            $table->unsignedInteger('operation_id')->index('usage_operation_id');
            $table->string('plugin_fskey', 64)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['usage_type', 'usage_id'], 'operation_usages');
        });
    }

    /**
     * Reverse fresns migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operations');
        Schema::dropIfExists('operation_usages');
    }
}
