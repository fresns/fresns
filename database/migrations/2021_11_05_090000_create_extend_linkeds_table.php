<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtendLinkedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extend_linkeds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('linked_type');
            $table->unsignedBigInteger('linked_id');
            $table->unsignedBigInteger('extend_id');
            $table->string('plugin_unikey', 64);
            $table->unsignedSmallInteger('rank_num')->default('9');
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
        Schema::dropIfExists('extend_linkeds');
    }
}
