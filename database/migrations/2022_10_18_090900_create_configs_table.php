<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigsTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('item_key', 64)->unique('item_key');
            $table->longText('item_value')->nullable();
            $table->string('item_type', 16)->default('string');
            $table->unsignedTinyInteger('is_multilingual')->default(0);
            $table->unsignedTinyInteger('is_custom')->default(1);
            $table->unsignedTinyInteger('is_api')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('code_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('app_fskey', 64)->index('code_message_fskey');
            $table->unsignedInteger('code');
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('messages')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('messages', 'max')->nullable();
                    break;

                default:
                    $table->json('messages')->nullable();
            }
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->unique(['app_fskey', 'code'], 'app_code');
        });

        Schema::create('language_packs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lang_key', 64)->unique('lang_key');
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('lang_values')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('lang_values', 'max')->nullable();
                    break;

                default:
                    $table->json('lang_values')->nullable();
            }
            $table->unsignedTinyInteger('is_custom')->default(1);
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
        Schema::dropIfExists('configs');
        Schema::dropIfExists('code_messages');
        Schema::dropIfExists('language_packs');
    }
}
