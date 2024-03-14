<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStickersTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('stickers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 32)->unique('sticker_code');
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('name');
                    break;

                default:
                    $table->json('name');
            }
            $table->unsignedBigInteger('image_file_id')->nullable();
            $table->string('image_file_url')->nullable();
            $table->unsignedTinyInteger('type')->default(1);
            $table->unsignedInteger('parent_id')->nullable()->index('sticker_parent_id');
            $table->unsignedSmallInteger('sort_order')->default(9);
            $table->boolean('is_enabled')->default(1);
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
        Schema::dropIfExists('stickers');
    }
}
