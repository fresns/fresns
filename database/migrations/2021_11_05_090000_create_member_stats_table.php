<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('member_id')->unique('member_id');
            $table->unsignedInteger('like_member_count')->default('0');
            $table->unsignedInteger('like_group_count')->default('0');
            $table->unsignedInteger('like_hashtag_count')->default('0');
            $table->unsignedInteger('like_post_count')->default('0');
            $table->unsignedInteger('like_comment_count')->default('0');
            $table->unsignedInteger('follow_member_count')->default('0');
            $table->unsignedInteger('follow_group_count')->default('0');
            $table->unsignedInteger('follow_hashtag_count')->default('0');
            $table->unsignedInteger('follow_post_count')->default('0');
            $table->unsignedInteger('follow_comment_count')->default('0');
            $table->unsignedInteger('shield_member_count')->default('0');
            $table->unsignedInteger('shield_group_count')->default('0');
            $table->unsignedInteger('shield_hashtag_count')->default('0');
            $table->unsignedInteger('shield_post_count')->default('0');
            $table->unsignedInteger('shield_comment_count')->default('0');
            $table->unsignedInteger('like_me_count')->default('0');
            $table->unsignedInteger('follow_me_count')->default('0');
            $table->unsignedInteger('shield_me_count')->default('0');
            $table->unsignedInteger('post_publish_count')->default('0');
            $table->unsignedInteger('post_like_count')->default('0');
            $table->unsignedInteger('comment_publish_count')->default('0');
            $table->unsignedInteger('comment_like_count')->default('0');
            $table->unsignedInteger('extcredits1')->default('0');
            $table->unsignedInteger('extcredits2')->default('0');
            $table->unsignedInteger('extcredits3')->default('0');
            $table->unsignedInteger('extcredits4')->default('0');
            $table->unsignedInteger('extcredits5')->default('0');
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
        Schema::dropIfExists('member_stats');
    }
}
