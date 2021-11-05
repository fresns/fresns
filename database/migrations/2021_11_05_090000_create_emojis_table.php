<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmojisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emojis', function (Blueprint $table) {
            $table->increments('id');
            $table->char('code', 16)->unique('code');
            $table->string('name', 64);
            $table->unsignedBigInteger('image_file_id')->nullable();
            $table->string('image_file_url');
            $table->unsignedTinyInteger('type')->default('1');
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedSmallInteger('rank_num')->default('99');
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
        Schema::dropIfExists('emojis');
    }
}
