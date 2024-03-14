<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtendsTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('extends', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('eid', 32)->unique('eid');
            $table->unsignedBigInteger('user_id')->index('extend_user_id');
            $table->unsignedTinyInteger('type')->default(1);
            $table->unsignedTinyInteger('view_type')->default(1);
            $table->string('app_fskey', 64);
            $table->string('url_parameter', 128)->nullable();
            $table->unsignedBigInteger('image_file_id')->nullable();
            $table->string('image_file_url')->nullable();
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('content')->nullable();
                    $table->jsonb('action_items')->nullable();
                    break;

                default:
                    $table->json('content')->nullable();
                    $table->json('action_items')->nullable();
            }
            $table->unsignedTinyInteger('position')->default(2);
            $table->boolean('is_enabled')->default(1);
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('extend_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('usage_type');
            $table->unsignedBigInteger('usage_id');
            $table->unsignedBigInteger('extend_id')->index('extend_usage_extend_id');
            $table->boolean('can_delete')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(9);
            $table->string('app_fskey', 64);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['usage_type', 'usage_id'], 'extend_usage_type_id');
        });

        Schema::create('extend_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('extend_id')->index('extend_id');
            $table->unsignedBigInteger('user_id')->index('extend_user_id');
            $table->string('action_key', 64)->nullable()->index('extend_action_key');
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
        Schema::dropIfExists('extends');
        Schema::dropIfExists('extend_usages');
        Schema::dropIfExists('extend_users');
    }
}
