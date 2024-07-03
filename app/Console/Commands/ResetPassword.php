<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands;

use App\Helpers\StrHelper;
use App\Models\Account;
use App\Models\Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetPassword extends Command
{
    protected $signature = 'fresns:reset-password';

    protected $description = 'Reset system administrator password';

    public function handle()
    {
        if (\PHP_SAPI != 'cli') {
            return $this->warn('Please execute the command in the terminal.');
        }

        $appFounder = config('app.founder');

        if (! $appFounder) {
            $this->call('config:clear');

            return $this->warn('You have not configured the system administrator. Please configure the APP_FOUNDER in the .env file, which supports either an account ID or AID.');
        }

        // account
        if (StrHelper::isPureInt($appFounder)) {
            $account = Account::where('id', $appFounder)->first();
        } else {
            $account = Account::where('aid', $appFounder)->first();
        }

        if (empty($account)) {
            return $this->warn('The administrator account was not found. Please ensure that the APP_FOUNDER in the .env file is correctly configured with the appropriate account information, which can be an account ID or AID.');
        }

        $panelConfigs = Config::where('item_key', 'panel_configs')->first();

        $appUrl = config('app.url');
        $loginPath = $panelConfigs?->item_value['path'] ?? 'admin';

        $accountInfo = $account->email ?? $account->phone;

        $this->info("\nAdmin Panel: {$appUrl}/fresns/{$loginPath}");
        $this->info("Admin Account: {$accountInfo}");

        do {
            $newPassword = $this->ask('Please enter new password');
            $newPassword = trim($newPassword);

            if (empty($newPassword)) {
                $this->error('Password cannot be empty');
            }
        } while (empty($newPassword));

        $account->update([
            'password' => Hash::make($newPassword),
        ]);

        $this->warn("Password reset successfully.\n");

        $this->info("Admin Panel: {$appUrl}/fresns/{$loginPath}");
        $this->info("Admin Account: {$accountInfo}");
        $this->info("Admin Password: {$newPassword}\n");
    }
}
