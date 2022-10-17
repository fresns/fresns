<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlacementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('placements', function (Blueprint $table) {
            $table->increments('id');
            $table->string('plugin_unikey', 64);
            $table->unsignedTinyInteger('implant_type');
            $table->unsignedBigInteger('implant_id');
            $table->json('implant_template');
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
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('placements');
    }
}
