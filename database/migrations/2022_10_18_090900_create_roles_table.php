<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('rid', 32)->unique('rid');
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('name');
                    break;

                case 'sqlsrv':
                    $table->nvarchar('name', 'max');
                    break;

                default:
                    $table->json('name');
            }
            $table->unsignedBigInteger('icon_file_id')->nullable();
            $table->string('icon_file_url')->nullable();
            $table->unsignedTinyInteger('is_display_name')->default(0);
            $table->unsignedTinyInteger('is_display_icon')->default(0);
            $table->string('nickname_color', 7)->nullable();
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('permissions')->nullable();
                    $table->jsonb('more_info')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('permissions', 'max')->nullable();
                    $table->nvarchar('more_info', 'max')->nullable();
                    break;

                default:
                    $table->json('permissions')->nullable();
                    $table->json('more_info')->nullable();
            }
            $table->unsignedTinyInteger('rank_state')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(9);
            $table->unsignedTinyInteger('is_enabled')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse fresns migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
}
