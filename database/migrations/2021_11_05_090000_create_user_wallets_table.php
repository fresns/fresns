<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique('user_id');
            $table->unsignedInteger('balance')->default('0');
            $table->unsignedInteger('freeze_amount')->default('0');
            $table->char('password', 64)->nullable();
            $table->string('bank_name', 64)->nullable();
            $table->string('swift_code', 32)->nullable();
            $table->string('bank_address')->nullable();
            $table->string('bank_account', 128)->nullable();
            $table->unsignedTinyInteger('bank_status')->default('1');
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
        Schema::dropIfExists('user_wallets');
    }
}
