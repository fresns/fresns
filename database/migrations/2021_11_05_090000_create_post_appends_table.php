<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostAppendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_appends', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id')->unique('post_id');
            $table->unsignedTinyInteger('platform_id');
            $table->unsignedTinyInteger('is_plugin_editor')->default('0');
            $table->string('editor_unikey', 64)->nullable();
            $table->longText('content')->nullable()->index('content');
            $table->unsignedTinyInteger('is_markdown')->default('0');
            $table->unsignedTinyInteger('can_delete')->default('1');
            $table->string('allow_plugin_unikey', 64)->nullable();
            $table->unsignedTinyInteger('allow_proportion')->nullable();
            $table->string('allow_btn_name', 64)->nullable();
            $table->unsignedTinyInteger('comment_btn_status')->default('0');
            $table->string('comment_btn_name', 64)->nullable();
            $table->string('comment_btn_plugin_unikey', 64)->nullable();
            $table->unsignedTinyInteger('member_list_status')->default('0');
            $table->string('member_list_name', 128)->nullable();
            $table->string('member_list_plugin_unikey', 64)->nullable();
            $table->string('map_scale', 8)->nullable();
            $table->string('map_nation', 128)->nullable();
            $table->string('map_province', 128)->nullable();
            $table->string('map_city', 128)->nullable();
            $table->string('map_district', 128)->nullable();
            $table->string('map_adcode', 32)->nullable();
            $table->string('map_address', 128)->nullable();
            $table->string('map_poi', 128)->nullable();
            $table->string('map_poi_id', 64)->nullable();
            $table->unsignedInteger('edit_count')->default('0');
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
        Schema::dropIfExists('post_appends');
    }
}
