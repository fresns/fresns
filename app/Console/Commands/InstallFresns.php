<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Console\Commands;

use App\Utilities\AppUtility;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InstallFresns extends Command
{
    protected $signature = 'fresns:install';

    protected $description = 'install fresns';

    public function __construct()
    {
        parent::__construct();
    }

    // execute the console command
    public function handle()
    {
        // 1. Check if you can install
        if (is_file(base_path('install.lock'))) {
            $this->error('Fresns is already installed and cannot be reinstalled.');

            return Command::FAILURE;
        }

        // 2. Select database type
        do {
            $dbTypeArr = ['MySQL', 'MariaDB', 'PostgreSQL', 'SQL Server', 'SQLite'];
            $dbType = $this->choice('Please select the database type', $dbTypeArr, 'MySQL');

            $dbType = match ($dbType) {
                'SQLite' => 'sqlite',
                'MySQL' => 'mysql',
                'MariaDB' => 'mariadb',
                'PostgreSQL' => 'pgsql',
                'SQL Server' => 'sqlsrv',
                'SQLServer' => 'sqlsrv',
                default => null,
            };

            if (empty($dbType)) {
                $this->error('Wrong database type');
            }
        } while (empty($dbType));

        // 3. Configuring database information
        $port = match ($dbType) {
            'mysql' => '3306',
            'mariadb' => '3306',
            'pgsql' => '5432',
            'sqlsrv' => '1433',
            default => '',
        };

        if ($dbType == 'sqlite') {
            $this->info('Database Path: '.database_path());

            do {
                $dbName = $this->ask('Please enter the SQLite file path');
                $dbName = trim($dbName);

                if (empty($dbName)) {
                    $this->error('SQLite file path cannot be empty');
                }

                if ($dbName && ! is_file($dbName)) {
                    $this->error('SQLite file does not exist');
                }
            } while (empty($dbName) || ! is_file($dbName));

            $dbHost = '';
            $dbPort = '';
            $dbUser = '';
            $dbPass = '';
        } else {
            $dbHost = $this->ask('Please enter the database host', '127.0.0.1');
            $dbPort = $this->ask('Please enter the database port', "{$port}");
            $dbName = $this->ask('Please enter the database name', 'fresns');
            $dbUser = $this->ask('Please enter the database username', 'fresns');
            $dbPass = $this->ask('Please enter the database password');
        }
        $dbUtc = $this->choice('Please select database timezone', self::utcArr(), 'UTC+8');
        $dbPrefix = $this->ask('Please enter the database table prefix', 'fs_');

        $dbConfig = [
            'DB_CONNECTION' => trim($dbType),
            'DB_HOST' => trim($dbHost),
            'DB_PORT' => trim($dbPort),
            'DB_DATABASE' => trim($dbName),
            'DB_USERNAME' => trim($dbUser),
            'DB_PASSWORD' => trim($dbPass),
            'DB_TIMEZONE' => self::timezoneIdentifier(trim($dbUtc)),
            'DB_PREFIX' => trim($dbPrefix),
        ];

        // 4. Configuring app url
        $appUrl = $this->ask('Please enter the website host(APP_URL)', 'http://localhost');

        // 5. Write to configuration
        AppUtility::writeEnvironment($dbConfig, $appUrl);

        $laravelDbConfig = config('database');
        $laravelDbConnection = $dbConfig['DB_CONNECTION'];
        $fresnsDB = [
            'host' => $dbConfig['DB_HOST'],
            'port' => $dbConfig['DB_PORT'],
            'database' => $dbConfig['DB_DATABASE'],
            'username' => $dbConfig['DB_USERNAME'],
            'password' => $dbConfig['DB_PASSWORD'],
            'prefix' => $dbConfig['DB_PREFIX'],
        ];
        $laravelDbConfig['default'] = $laravelDbConnection;
        $laravelDbConfig['connections'][$laravelDbConnection] = array_merge($laravelDbConfig['connections'][$laravelDbConnection], $fresnsDB);

        config(['database' => $laravelDbConfig]);
        DB::purge();

        // 6. Execution of installation data
        $this->call('migrate');
        $this->call('db:seed');
        $this->call('storage:link');
        // $this->call('plugin:migrate');

        // 7. Register for an administrator account
        do {
            $adminEmail = $this->ask('Please enter administrator email');
            $adminEmail = trim($adminEmail);

            if (empty($adminEmail)) {
                $this->error('Email cannot be empty');
            }
        } while (empty($adminEmail));

        do {
            $adminPassword = $this->ask('Please enter administrator password');
            $adminPassword = trim($adminPassword);

            if (empty($adminPassword)) {
                $this->error('Password cannot be empty');
            }
        } while (empty($adminPassword));

        AppUtility::makeAdminAccount($adminEmail, $adminPassword);

        // 8. Write to installation time
        AppUtility::writeInstallTime();

        // 9. Installation completion message
        $this->info("Fresns installed successfully\n");
        $this->info("Admin Panel: {$appUrl}/fresns/admin");
        $this->info("Admin Account: {$adminEmail}");
        $this->info("Admin Password: {$adminPassword}\n");
        $this->warn('Tip: Don\'t forget to reset folder ownership');

        return Command::SUCCESS;
    }

    private static function utcArr(): array
    {
        return [
            'UTC-11',
            'UTC-10',
            'UTC-9:30',
            'UTC-9',
            'UTC-8',
            'UTC-7',
            'UTC-6',
            'UTC-5',
            'UTC-4',
            'UTC-3:30',
            'UTC-3',
            'UTC-2',
            'UTC-1',
            'UTC+0',
            'UTC+1',
            'UTC+2',
            'UTC+3',
            'UTC+3:30',
            'UTC+4',
            'UTC+4:30',
            'UTC+5',
            'UTC+5:30',
            'UTC+5:45',
            'UTC+6',
            'UTC+6:30',
            'UTC+7',
            'UTC+8',
            'UTC+8:45',
            'UTC+9',
            'UTC+9:30',
            'UTC+10',
            'UTC+10:30',
            'UTC+11',
            'UTC+12',
            'UTC+12:45',
            'UTC+13',
            'UTC+14',
        ];
    }

    private static function timezoneIdentifier(string $utc): string
    {
        $timezoneArr = [
            'UTC-11' => 'Pacific/Niue',
            'UTC-10' => 'Pacific/Rarotonga',
            'UTC-9:30' => 'Pacific/Marquesas',
            'UTC-9' => 'America/Anchorage',
            'UTC-8' => 'America/Los_Angeles',
            'UTC-7' => 'America/Denver',
            'UTC-6' => 'America/Chicago',
            'UTC-5' => 'America/New_York',
            'UTC-4' => 'America/Moncton',
            'UTC-3:30' => 'America/St_Johns',
            'UTC-3' => 'America/Bahia',
            'UTC-2' => 'America/Noronha',
            'UTC-1' => 'Atlantic/Azores',
            'UTC+0' => 'Europe/London',
            'UTC+1' => 'Europe/Paris',
            'UTC+2' => 'Asia/Jerusalem',
            'UTC+3' => 'Europe/Moscow',
            'UTC+3:30' => 'Asia/Tehran',
            'UTC+4' => 'Asia/Dubai',
            'UTC+4:30' => 'Asia/Kabul',
            'UTC+5' => 'Indian/Maldives',
            'UTC+5:30' => 'Asia/Kolkata',
            'UTC+5:45' => 'Asia/Kathmandu',
            'UTC+6' => 'Asia/Urumqi',
            'UTC+6:30' => 'Asia/Yangon',
            'UTC+7' => 'Asia/Ho_Chi_Minh',
            'UTC+8' => 'Asia/Singapore',
            'UTC+8:45' => 'Australia/Eucla',
            'UTC+9' => 'Asia/Tokyo',
            'UTC+9:30' => 'Australia/Broken_Hill',
            'UTC+10' => 'Australia/Melbourne',
            'UTC+10:30' => 'Australia/Lord_Howe',
            'UTC+11' => 'Asia/Sakhalin',
            'UTC+12' => 'Pacific/Auckland',
            'UTC+12:45' => 'Pacific/Chatham',
            'UTC+13' => 'Pacific/Apia',
            'UTC+14' => 'Pacific/Kiritimati',
        ];

        return $timezoneArr[$utc];
    }
}
