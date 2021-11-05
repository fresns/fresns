<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePluginUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plugin_usages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('plugin_unikey', 64);
            $table->unsignedTinyInteger('type');
            $table->string('name', 128);
            $table->unsignedBigInteger('icon_file_id')->nullable();
            $table->string('icon_file_url')->nullable();
            $table->string('scene', 16)->nullable();
            $table->unsignedTinyInteger('editor_number')->nullable();
            $table->json('data_sources')->nullable();
            $table->unsignedTinyInteger('is_group_admin')->nullable()->default('0');
            $table->unsignedInteger('group_id')->nullable();
            $table->string('member_roles', 128)->nullable();
            $table->string('parameter')->nullable();
            $table->unsignedSmallInteger('rank_num')->default('99');
            $table->unsignedTinyInteger('can_delete')->default('0');
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
        Schema::dropIfExists('plugin_usages');
    }
}
