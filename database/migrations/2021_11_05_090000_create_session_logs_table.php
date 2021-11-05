<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('plugin_unikey', 64)->default('Fresns');
            $table->unsignedTinyInteger('platform_id');
            $table->string('version', 16);
            $table->unsignedInteger('version_int');
            $table->char('lang_tag', 16)->nullable();
            $table->unsignedTinyInteger('object_type')->default('1')->index('object_type');
            $table->string('object_name', 128);
            $table->string('object_action', 128)->nullable();
            $table->unsignedTinyInteger('object_result');
            $table->unsignedBigInteger('object_order_id')->nullable();
            $table->json('device_info')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->json('more_json')->nullable();
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
        Schema::dropIfExists('session_logs');
    }
}
