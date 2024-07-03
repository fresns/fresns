<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Install\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Account;
use App\Utilities\AppUtility;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class WebController extends Controller
{
    public function index(Request $request)
    {
        $langs = config('install.langs');

        $phpVersion = version_compare(PHP_VERSION, '8.2', '>=');
        $isHttps = $request->getScheme() == 'https';

        // composerVersion
        $composerVersion = AppHelper::getComposerVersionInfo()['version'];
        $isComposerVersion2 = version_compare($composerVersion, '2.5.0', '>=');

        // folderOwnership
        $folderOwnership = $this->folderOwnership();

        // phpExtensions
        $phpExtensions = $this->phpExtensions();

        // phpFunctions
        $phpFunctions = $this->phpFunctions();

        $checkServer = [
            'phpVersion' => $phpVersion,
            'composerVersion' => $isComposerVersion2,
            'ssl' => $isHttps,
            'folderOwnership' => $folderOwnership['status'],
            'phpExtensions' => $phpExtensions['status'],
            'phpFunctions' => $phpFunctions['status'],
        ];

        $serverMessages = [
            'composerVersion' => $composerVersion,
            'folderOwnership' => $folderOwnership['message'],
            'phpExtensions' => $phpExtensions['message'],
            'phpFunctions' => $phpFunctions['message'],
        ];

        $allServer = true;
        if (! $phpVersion) {
            $allServer = false;
        }
        if (! $folderOwnership['status']) {
            $allServer = false;
        }
        if (! $phpExtensions['status']) {
            $allServer = false;
        }
        if (! $phpFunctions['status']) {
            $allServer = false;
        }

        return view('Install::index', compact('langs', 'checkServer', 'serverMessages', 'allServer'));
    }

    public function checkServer(Request $request)
    {
        $type = $request->type;

        $code = 0;
        $message = 'ok';

        switch ($type) {
            case 'phpVersion':
                $phpVersion = version_compare(PHP_VERSION, '8.2', '>=');

                $code = $phpVersion ? 0 : 10000;
                break;

            case 'composerVersion':
                $composerVersion = AppHelper::getComposerVersionInfo()['version'];
                $isComposerVersion2 = version_compare($composerVersion, '2.5.0', '>=');

                $code = $isComposerVersion2 ? 0 : 10000;
                $message = $composerVersion;
                break;

            case 'ssl':
                $isHttps = $request->getScheme() == 'https';

                $code = $isHttps ? 0 : 10000;
                break;

            case 'folderOwnership':
                $folderOwnership = $this->folderOwnership();

                $code = $folderOwnership['status'] ? 0 : 10000;
                $message = $folderOwnership['message'];
                break;

            case 'phpExtensions':
                $phpExtensions = $this->phpExtensions();

                $code = $phpExtensions['status'] ? 0 : 10000;
                $message = $phpExtensions['message'];
                break;

            case 'phpFunctions':
                $phpFunctions = $this->phpFunctions();

                $code = $phpFunctions['status'] ? 0 : 10000;
                $message = $phpFunctions['message'];
                break;

            case 'all':
                $phpVersion = version_compare(PHP_VERSION, '8.2', '>=');

                $folderOwnership = $this->folderOwnership();

                $phpExtensions = $this->phpExtensions();

                $phpFunctions = $this->phpFunctions();

                if (! $phpVersion) {
                    $code = 10000;
                }
                if (! $folderOwnership['status']) {
                    $code = 10000;
                }
                if (! $phpExtensions['status']) {
                    $code = 10000;
                }
                if (! $phpFunctions['status']) {
                    $code = 10000;
                }
                break;
        }

        return Response::json([
            'code' => $code,
            'message' => $message,
            'data' => null,
        ]);
    }

    public function configDatabase(Request $request)
    {
        $dbConfig = config('database');

        $connection = $request->database['DB_CONNECTION'];
        $fresnsDB = [
            'host' => $request->database['DB_HOST'],
            'port' => $request->database['DB_PORT'],
            'database' => $request->database['DB_DATABASE'],
            'username' => $request->database['DB_USERNAME'],
            'password' => $request->database['DB_PASSWORD'],
            'prefix' => $request->database['DB_PREFIX'],
        ];

        $dbConfig['default'] = $connection;
        $dbConfig['connections'][$connection] = array_merge($dbConfig['connections'][$connection], $fresnsDB);

        if ($connection == 'sqlite' && ! is_file($fresnsDB['database'])) {
            return Response::json([
                'code' => 500,
                'message' => __('Install::install.database_config_invalid'),
                'data' => null,
            ]);
        }

        try {
            config(['database' => $dbConfig]);

            $query = match ($connection) {
                'sqlite' => 'SELECT 1 LIMIT 1',
                'mysql' => 'SELECT 1 LIMIT 1',
                'mariadb' => 'SELECT 1 LIMIT 1',
                'pgsql' => 'SELECT 1 LIMIT 1',
                'sqlsrv' => 'SELECT TOP 1 1',
            };

            DB::purge();
            DB::select($query);
        } catch (QueryException $exception) {
            return \response()->json([
                'code' => 500,
                'message' => __('Install::install.database_config_invalid'),
                'data' => $exception->getMessage(),
            ]);
        }

        AppUtility::writeEnvironment($request->database);

        return Response::json([
            'code' => 0,
            'message' => 'ok',
            'data' => null,
        ]);
    }

    public function dataArtisan()
    {
        (new DatabaseServiceProvider(app()))->register();

        $commands = [
            'migrate',
            'db:seed',
            'storage:link',
            // 'plugin:migrate',
        ];

        $code = 0;
        $output = [];
        foreach ($commands as $cmd) {
            try {
                $code = Artisan::call(command: $cmd, parameters: [
                    '--force' => true,
                ]);

                if ($code != 0) {
                    $output[] = "Artisan::call('$cmd') fail";
                }
            } catch (\Throwable $e) {
                $code = 500;
                $output[] = $e->getMessage();
                $output[] = Artisan::output();
                break;
            }

            $output[] = Artisan::output();
        }

        return Response::json([
            'code' => $code,
            'message' => $code == 0 ? 'ok' : 'error',
            'data' => implode("\n", $output),
        ]);
    }

    public function addAdmin(Request $request)
    {
        $adminEmail = $request->admin_email;
        $adminPassword = $request->admin_password;
        $adminPasswordConfirm = $request->admin_password_confirm;

        if (empty($adminEmail)) {
            return Response::json([
                'code' => 30000,
                'message' => 'Cannot be empty',
                'data' => __('Install::install.register_account_email'),
            ]);
        }

        if (empty($adminPassword)) {
            return Response::json([
                'code' => 30000,
                'message' => 'Cannot be empty',
                'data' => __('Install::install.register_account_password'),
            ]);
        }

        if (empty($adminPasswordConfirm)) {
            return Response::json([
                'code' => 30000,
                'message' => 'Cannot be empty',
                'data' => __('Install::install.register_account_password_confirm'),
            ]);
        }

        if ($adminPassword != $adminPasswordConfirm) {
            return Response::json([
                'code' => 30000,
                'message' => 'The new password entered twice does not match',
                'data' => __('Install::install.register_account_password').' != '.__('Install::install.register_account_password_confirm'),
            ]);
        }

        try {
            AppUtility::makeAdminAccount($adminEmail, $adminPassword);
        } catch (\Throwable $exception) {
            return Response::json([
                'code' => 500,
                'message' => $exception->getMessage(),
                'data' => null,
            ]);
        }

        $email = Account::first()?->value('email');

        if (empty($email)) {
            return Response::json([
                'code' => 500,
                'message' => 'Registration exception, account does not exist',
                'data' => null,
            ]);
        }

        AppUtility::writeInstallTime();

        return Response::json([
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'email' => $email,
            ],
        ]);
    }

    protected function folderOwnership()
    {
        $dir = [
            'bootstrap/cache/',
            'storage/',
        ];

        $dirPermissions = [];
        foreach ($dir as $item) {
            $path = base_path($item);

            $permission = is_writable($path);

            $dirPermissions[$item]['dir'] = $item;
            $dirPermissions[$item]['writable'] = $permission;
        }

        $checkOwnership = ! in_array(false, array_column($dirPermissions, 'writable'));
        $folderMessage = sprintf('%s: %s', __('Install::install.server_status_not_writable'), implode(', ', array_column(array_filter($dirPermissions, function ($item) {
            return ! $item['writable'];
        }), 'dir')));

        return [
            'status' => $checkOwnership,
            'message' => $folderMessage,
        ];
    }

    protected function phpExtensions()
    {
        $extensions = [
            'mbstring',
            'fileinfo',
        ];

        $extensionsCheckResult = [];
        foreach ($extensions as $item) {
            $extensionsCheckResult[$item]['extension'] = $item;
            $extensionsCheckResult[$item]['loaded'] = extension_loaded($item);
        }

        $checkExtensions = ! in_array(false, array_column($extensionsCheckResult, 'loaded'));
        $phpExtensionsMessage = sprintf('%s: %s', __('Install::install.server_status_not_installed'), implode(', ', array_column(array_filter($extensionsCheckResult, function ($item) {
            return ! $item['loaded'];
        }), 'extension')));

        return [
            'status' => $checkExtensions,
            'message' => $phpExtensionsMessage,
        ];
    }

    protected function phpFunctions()
    {
        $functions = [
            'symlink', // storage:link
            'putenv', // composer
            'proc_open', // symfony/process
            'shell_exec', // symfony/console
            'pcntl_signal', // illuminate/queue
            'pcntl_alarm', // illuminate/queue
            'pcntl_async_signals', // illuminate/queue
            'passthru', // fresns upgrade
        ];

        if (windows_os()) {
            $functions[] = 'exec'; // windows install fresns
        }

        $disableFunction = explode(',', ini_get('disable_functions'));

        $functionsCheckResult = [];
        foreach ($functions as $item) {
            $functionsCheckResult[$item]['function'] = $item;
            $functionsCheckResult[$item]['function_disabled'] = in_array($item, $disableFunction);
        }

        $checkFunctions = ! in_array(true, array_column($functionsCheckResult, 'function_disabled'));
        $phpFunctionsMessage = sprintf('%s: %s', __('Install::install.server_status_not_enabled'), implode(', ', array_column(array_filter($functionsCheckResult, function ($item) {
            return $item['function_disabled'];
        }), 'function')));

        return [
            'status' => $checkFunctions,
            'message' => $phpFunctionsMessage,
        ];
    }
}
