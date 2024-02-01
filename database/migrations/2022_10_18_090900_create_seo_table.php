<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeoTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('seo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('usage_type');
            $table->unsignedBigInteger('usage_id');
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('title')->nullable();
                    $table->jsonb('keywords')->nullable();
                    $table->jsonb('description')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('title', 'max')->nullable();
                    $table->nvarchar('keywords', 'max')->nullable();
                    $table->nvarchar('description', 'max')->nullable();
                    break;

                default:
                    $table->json('title')->nullable();
                    $table->json('keywords')->nullable();
                    $table->json('description')->nullable();
            }
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['usage_type', 'usage_id'], 'seo_usage_type_id');
        });
    }

    /**
     * Reverse fresns migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo');
    }
}
