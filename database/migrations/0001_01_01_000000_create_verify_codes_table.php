<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('verify_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('template_id');
            $table->unsignedTinyInteger('type');
            $table->string('account', 128);
            $table->string('code', 12);
            $table->boolean('is_enabled')->default(1);
            $table->timestamp('expired_at');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['type', 'account', 'code'], 'account_verify_code');
        });
    }

    /**
     * Reverse fresns migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verify_codes');
    }
};
