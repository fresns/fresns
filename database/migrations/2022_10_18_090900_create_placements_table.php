<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlacementsTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('placements', function (Blueprint $table) {
            $table->increments('id');
            $table->string('plugin_unikey', 64);
            $table->unsignedTinyInteger('implant_type');
            $table->unsignedBigInteger('implant_id');
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('implant_template')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('implant_template', 'max')->nullable();
                    break;

                default:
                    $table->json('implant_template')->nullable();
            }
            $table->string('implant_name', 64);
            $table->unsignedTinyInteger('open_type');
            $table->string('open_value', 128);
            $table->unsignedTinyInteger('position')->default(5);
            $table->timestamp('starting_at')->nullable();
            $table->timestamp('expired_at')->nullable();
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
        Schema::dropIfExists('placements');
    }
}
