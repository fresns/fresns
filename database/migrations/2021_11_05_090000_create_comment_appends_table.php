<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentAppendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment_appends', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('comment_id')->unique('comment_id');
            $table->unsignedTinyInteger('platform_id');
            $table->unsignedTinyInteger('is_plugin_editor')->default('0');
            $table->string('editor_unikey', 64)->nullable();
            $table->longText('content')->nullable()->index('content');
            $table->unsignedTinyInteger('is_markdown')->default('0');
            $table->unsignedTinyInteger('can_delete')->default('1');
            $table->unsignedTinyInteger('map_id')->nullable();
            $table->string('map_latitude', 32)->nullable();
            $table->string('map_longitude', 32)->nullable();
            $table->string('map_scale', 8)->nullable();
            $table->string('map_poi', 128)->nullable();
            $table->string('map_poi_id', 64)->nullable();
            $table->string('map_nation', 128)->nullable();
            $table->string('map_province', 128)->nullable();
            $table->string('map_city', 128)->nullable();
            $table->string('map_district', 128)->nullable();
            $table->string('map_adcode', 32)->nullable();
            $table->string('map_address', 128)->nullable();
            $table->unsignedSmallInteger('edit_count')->default('0');
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
        Schema::dropIfExists('comment_appends');
    }
}
