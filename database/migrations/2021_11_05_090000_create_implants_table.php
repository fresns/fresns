<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImplantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('implants', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('implant_type');
            $table->unsignedBigInteger('implant_id');
            $table->json('implant_template');
            $table->string('implant_name', 64);
            $table->string('plugin_unikey', 64);
            $table->unsignedTinyInteger('type');
            $table->unsignedTinyInteger('target');
            $table->string('value');
            $table->unsignedTinyInteger('support')->nullable();
            $table->unsignedTinyInteger('position')->default('5');
            $table->timestamp('starting_at')->nullable();
            $table->timestamp('expired_at')->nullable();
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
        Schema::dropIfExists('implants');
    }
}
