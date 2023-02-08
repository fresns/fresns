<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Utilities\AppUtility;
use App\Utilities\UpgradeUtility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpgradeBetaToStable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $currentVersionInt = AppUtility::currentVersion()['versionInt'] ?? 0;

        if ($currentVersionInt < 2) {
            UpgradeUtility::upgradeTo2();
        }

        if ($currentVersionInt < 3) {
            UpgradeUtility::upgradeTo3();
        }

        if ($currentVersionInt < 4) {
            UpgradeUtility::upgradeTo4();
        }

        if ($currentVersionInt < 5) {
            UpgradeUtility::upgradeTo5();
        }

        if ($currentVersionInt < 6) {
            UpgradeUtility::upgradeTo6();
        }

        if ($currentVersionInt < 7) {
            UpgradeUtility::upgradeTo7();
        }

        if ($currentVersionInt < 8) {
            UpgradeUtility::upgradeTo8();
        }

        if ($currentVersionInt < 9) {
            UpgradeUtility::upgradeTo9();
        }

        if (! Schema::hasColumn('session_logs', 'app_id')) {
            Schema::table('session_logs', function (Blueprint $table) {
                $table->char('app_id', 8)->after('version')->nullable();
            });
        }

        if (! Schema::hasColumn('user_follows', 'is_enable')) {
            Schema::table('user_follows', function (Blueprint $table) {
                $table->unsignedTinyInteger('is_enable')->after('is_mutual')->default(1);
            });
        }

        if (Schema::hasColumn('session_tokens', 'token')) {
            Schema::table('session_tokens', function (Blueprint $table) {
                $table->dropUnique('account_token');

                $table->renameColumn('token', 'account_token');
            });
        }

        if (! Schema::hasColumn('session_tokens', 'user_token')) {
            Schema::table('session_tokens', function (Blueprint $table) {
                $table->string('version', 16)->nullable()->after('platform_id');
                $table->char('app_id', 8)->nullable()->after('version');
                $table->char('user_token', 32)->nullable()->after('account_token');

                $table->unique(['user_id', 'user_token'], 'user_id_token');
            });
        }

        if (Schema::hasColumn('post_appends', 'is_allow')) {
            Schema::table('post_appends', function (Blueprint $table) {
                $table->unsignedSmallInteger('is_allow')->default(1)->change();
            });
        }

        if (! Schema::hasColumn('files', 'disk')) {
            Schema::table('files', function (Blueprint $table) {
                $table->string('disk', 32)->after('sha_type')->default('remote');
            });
        }

        if (Schema::hasColumn('plugin_callbacks', 'uuid')) {
            Schema::table('plugin_callbacks', function (Blueprint $table) {
                $table->renameColumn('uuid', 'ulid');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
