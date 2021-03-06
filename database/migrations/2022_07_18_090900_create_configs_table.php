<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('item_key', 64)->unique('item_key');
            $table->longText('item_value')->nullable();
            $table->string('item_type', 16)->default('string');
            $table->string('item_tag', 32)->index('item_tag');
            $table->unsignedTinyInteger('is_multilingual')->default(0);
            $table->unsignedTinyInteger('is_custom')->default(1);
            $table->unsignedTinyInteger('is_api')->default(0);
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
        Schema::dropIfExists('configs');
    }
}
