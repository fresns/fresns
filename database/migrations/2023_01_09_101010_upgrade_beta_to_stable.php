<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Utilities\AppUtility;
use App\Utilities\UpgradeUtility;
use Illuminate\Database\Migrations\Migration;

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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
