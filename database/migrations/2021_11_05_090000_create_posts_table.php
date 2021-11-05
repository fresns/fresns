<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 12)->unique('uuid');
            $table->unsignedBigInteger('member_id');
            $table->unsignedInteger('group_id')->nullable();
            $table->string('types', 128)->index('types');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->unsignedTinyInteger('is_brief')->default('0');
            $table->unsignedTinyInteger('sticky_state')->default('1');
            $table->unsignedTinyInteger('essence_state')->default('1');
            $table->unsignedTinyInteger('is_anonymous')->default('0');
            $table->unsignedTinyInteger('is_allow')->default('0');
            $table->unsignedTinyInteger('is_lbs')->default('0');
            $table->unsignedTinyInteger('map_id')->nullable();
            $table->string('map_latitude', 32)->nullable()->index('map_latitude');
            $table->string('map_longitude', 32)->nullable()->index('map_longitude');
            $table->json('more_json')->nullable();
            $table->unsignedInteger('view_count')->default('0');
            $table->unsignedInteger('like_count')->default('0');
            $table->unsignedInteger('follow_count')->default('0');
            $table->unsignedInteger('shield_count')->default('0');
            $table->unsignedInteger('comment_count')->default('0');
            $table->unsignedInteger('comment_like_count')->default('0');
            $table->timestamp('latest_edit_at')->nullable();
            $table->timestamp('latest_comment_at')->nullable();
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
        Schema::dropIfExists('posts');
    }
}
