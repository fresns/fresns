<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDialogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dialogs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('a_member_id');
            $table->unsignedBigInteger('b_member_id');
            $table->unsignedBigInteger('latest_message_id')->nullable();
            $table->timestamp('latest_message_time')->nullable();
            $table->string('latest_message_brief')->nullable();
            $table->unsignedTinyInteger('a_status')->default('1');
            $table->unsignedTinyInteger('b_status')->default('1');
            $table->unsignedTinyInteger('a_is_display')->default('1');
            $table->unsignedTinyInteger('b_is_display')->default('1');
            $table->unsignedTinyInteger('a_is_deactivate')->default('1');
            $table->unsignedTinyInteger('b_is_deactivate')->default('1');
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
        Schema::dropIfExists('dialogs');
    }
}
