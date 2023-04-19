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
            $table->unsignedBigInteger('user_id');
            $table->string('plugin_unikey', 64);
            $table->unsignedTinyInteger('type')->default(1);
            $table->text('text_content')->nullable();
            $table->unsignedTinyInteger('text_is_markdown')->default(0);
            $table->unsignedTinyInteger('info_type')->nullable();
            $table->unsignedBigInteger('cover_file_id')->nullable();
            $table->string('cover_file_url')->nullable();
            $table->string('title')->nullable();
            $table->string('title_color', 6)->nullable();
            $table->string('desc_primary')->nullable();
            $table->string('desc_primary_color', 6)->nullable();
            $table->string('desc_secondary')->nullable();
            $table->string('desc_secondary_color', 6)->nullable();
            $table->string('button_name', 64)->nullable();
            $table->string('button_color', 6)->nullable();
            $table->string('parameter', 128)->nullable();
            $table->unsignedTinyInteger('position')->default(2);
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('more_json')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('more_json', 'max')->nullable();
                    break;

                default:
                    $table->json('more_json')->nullable();
            }
            $table->unsignedTinyInteger('is_enable')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('extend_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('usage_type');
            $table->unsignedBigInteger('usage_id');
            $table->unsignedBigInteger('extend_id')->index('usage_extend_id');
            $table->unsignedTinyInteger('can_delete')->default(1);
            $table->unsignedSmallInteger('rating')->default(9);
            $table->string('plugin_unikey', 64);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['usage_type', 'usage_id'], 'extend_usages');
        });
    }

    /**
     * Reverse fresns migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extends');
        Schema::dropIfExists('extend_usages');
    }
}
