<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 128);
            $table->unsignedTinyInteger('type')->default('3');
            $table->unsignedBigInteger('icon_file_id')->nullable();
            $table->string('icon_file_url')->nullable();
            $table->unsignedTinyInteger('is_display_name')->default('0');
            $table->unsignedTinyInteger('is_display_icon')->default('0');
            $table->char('nickname_color', 7)->nullable();
            $table->json('permission');
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
        Schema::dropIfExists('member_roles');
    }
}
