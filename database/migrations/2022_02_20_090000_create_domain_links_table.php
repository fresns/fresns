<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domain_links', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('linked_type');
            $table->unsignedBigInteger('linked_id');
            $table->unsignedInteger('domain_id');
            $table->string('link_url', 255)->index('link_url');
            $table->string('link_title', 255)->nullable();
            $table->unsignedTinyInteger('is_enable')->default('1');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
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
        Schema::dropIfExists('domain_links');
    }
}
