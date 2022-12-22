<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Install\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Account;
use App\Models\Config;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class InstallController extends Controller
{
    public function showInstallForm()
    {
        $step = (int) \request()->input('step', 1);
        $step = $step ?: $step + 1;

        $langs = config('install.langs');

        $basicCheckResult = [];
        $basicCheckPass = false;
        if ($step === 3) {
            $basicCheckResult = $this->basicCheck();

            // https skip check
            $basicCheckPass = true;
            foreach ($basicCheckResult as $item) {
                if ($item['type'] === 'https') {
                    continue;
                }

                if ($item['type'] === 'composer_version') {
                    continue;
                }

                if ($item['check_result'] === false) {
                    $basicCheckPass = false;
                    break;
                }
            }
        }

        $email = null;
        if ($step === 5) {
            $email = Account::first()?->value('email');
        }

        return view('Install::install', [
            'langs' => $langs,
            'step' => $step++,
            'basicCheckResult' => $basicCheckResult,
            'basicCheckPass' => $basicCheckPass,
            'database' => [],
            'admin_info' => [],
            'email' => $email,
        ]);
    }

    public function install()
    {
        try {
            \request()->validate([
                'step' => 'required|integer',
                'install_lang' => 'required_if:step,1',
                'admin_info.password' => 'required_if:step,5|confirmed',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return \response()->json([
                'step' => \request('step') ?? 1,
                'code' => 500,
                'message' => __('Install::install.server_status_failure').' '.$exception->validator->errors()->first(),
                'data' => [],
            ]);
        }

        $step = (int) \request()->input('step');

        $data = \request()->all();

        switch ($step) {
            case 1:
            case 2:
                break;
            case 3:
                $basicCheckResult = $this->basicCheck();

                $checkResult = false;
                foreach ($basicCheckResult as $item) {
                    if ($item['type'] === 'https') {
                        continue;
                    }

                    if ($item['type'] === 'composer_version') {
                        continue;
                    }

                    if ($item['check_result'] === false) {
                        $checkResult = false;
                        break;
                    }

                    $checkResult = true;
                }

                if (! $checkResult) {
                    return \response()->json([
                        'step' => $step,
                        'code' => 500,
                        'message' => 'basic environment check fail.',
                        'data' => $this->basicCheck(),
                    ]);
                }
                break;
            case 4:
                try {
                    $dbConfig = config('database');
                    $mysqlDB = [
                        'host' => \request()->input('database.DB_HOST'),
                        'port' => \request()->input('database.DB_PORT'),
                        'database' => \request()->input('database.DB_DATABASE'),
                        'username' => \request()->input('database.DB_USERNAME'),
                        'password' => \request()->input('database.DB_PASSWORD'),
                        'prefix' => \request()->input('database.DB_PREFIX'),
                    ];
                    $dbConfig['connections']['mysql'] = array_merge($dbConfig['connections']['mysql'], $mysqlDB);

                    config(['database' => $dbConfig]);
                    \DB::purge();
                    \DB::select('select 1 limit 1');
                } catch (\Illuminate\Database\QueryException $exception) {
                    return \response()->json([
                        'step' => $step,
                        'code' => 500,
                        'message' => __('Install::install.database_config_invalid').$exception->getMessage(),
                        'data' => [],
                    ]);
                }
                break;
            case 5:
                break;
        }

        $cacheKey = "install_{$step}";
        $prevStep = $step - 1;
        $prevCacheKey = "install_{$prevStep}";

        $cacheData = match ($step) {
            default => [],
            0, 1 => [],
            2 => json_decode(Cache::pull($prevCacheKey), true) ?? [],
            3 => json_decode(Cache::pull($prevCacheKey), true) ?? [],
            4 => json_decode(Cache::pull($prevCacheKey), true) ?? [],
            5 => json_decode(Cache::pull($prevCacheKey), true) ?? [],
        };

        $data = array_merge($cacheData, $data);
        Cache::put($cacheKey, json_encode($data), now()->addHour());

        $result = [];
        if ($step === 4) {
            $this->writeEnvironment($data);

            $result = $this->installMysqlDatabase();
        }

        if ($step === 5) {
            try {
                $this->generateAdminInfo($data);
            } catch (\Throwable $exception) {
                return \response()->json([
                    'step' => $step,
                    'code' => 500,
                    'message' => $exception->getMessage(),
                    'data' => [],
                ]);
            }

            $result['email'] = Account::first()?->value('email');

            if (! empty($result['email'])) {
                $this->writeInstallTime();
            }
        }

        return \response()->json([
            'step' => $step,
            'code' => 0,
            'message' => 'success',
            'data' => array_merge(['step' => $step], $result),
        ]);
    }

    protected function basicCheck()
    {
        $isSupportInstall = version_compare(PHP_VERSION, '8.0.2', '>=');

        $isComposerVersion2 = $this->checkComposerVersion();

        $isHttps = \request()->getScheme() === 'https';

        $dirPermissions = $this->getDirPermission();
        $dirPermissionsCheckResult = ! in_array(false, array_column($dirPermissions, 'writable'));

        $extensionsCheck = $this->extensionCheck();
        $extensionsCheckResult = ! in_array(false, array_column($extensionsCheck, 'loaded'));

        $functionsCheck = $this->functionCheck();
        $functionsCheckResult = ! in_array(true, array_column($functionsCheck, 'function_disabled'));

        $data = [
            [
                'type' => 'version',
                'title' => __('Install::install.server_check_php_version'),
                'check_result' => $isSupportInstall,
                'tips' => $isSupportInstall ? __('Install::install.server_status_success') : __('Install::install.server_status_failure'),
                'class' => $isSupportInstall ? 'bg-success' : 'bg-danger',
                'message' => '',
            ],
            [
                'type' => 'composer_version',
                'title' => __('Install::install.server_check_composer_version'),
                'check_result' => $isComposerVersion2,
                'tips' => $isComposerVersion2 ? __('Install::install.server_status_success') : __('Install::install.server_status_warning'),
                'class' => $isComposerVersion2 ? 'bg-success' : 'bg-warning',
                'message' => '',
            ],
            [
                'type' => 'https',
                'title' => __('Install::install.server_check_https'),
                'check_result' => $isHttps,
                'tips' => $isHttps ? __('Install::install.server_status_success') : __('Install::install.server_status_warning'),
                'class' => $isHttps ? 'bg-success' : 'bg-warning',
                'message' => '',
            ],
            [
                'type' => 'dir',
                'title' => __('Install::install.server_check_folder_ownership'),
                'check_result' => $dirPermissionsCheckResult,
                'tips' => $dirPermissionsCheckResult ? __('Install::install.server_status_success') : __('Install::install.server_status_failure'),
                'class' => $dirPermissionsCheckResult ? 'bg-success' : 'bg-danger',
                'message' => $dirPermissionsCheckResult ? '' : sprintf('%s: %s', __('Install::install.server_status_not_writable'), implode(', ', array_column(array_filter($dirPermissions, function ($item) {
                    return ! $item['writable'];
                }), 'dir'))),
            ],
            [
                'type' => 'extension',
                'title' => __('Install::install.server_check_php_extensions'),
                'check_result' => $extensionsCheckResult,
                'tips' => $extensionsCheckResult ? __('Install::install.server_status_success') : __('Install::install.server_status_failure'),
                'class' => $extensionsCheckResult ? 'bg-success' : 'bg-danger',
                'message' => $extensionsCheckResult ? '' : sprintf('%s: %s', __('Install::install.server_status_not_installed'), implode(', ', array_column(array_filter($extensionsCheck, function ($item) {
                    return ! $item['loaded'];
                }), 'extension'))),
            ],
            [
                'type' => 'function',
                'title' => __('Install::install.server_check_php_functions'),
                'check_result' => $functionsCheckResult,
                'tips' => $functionsCheckResult ? __('Install::install.server_status_success') : __('Install::install.server_status_failure'),
                'class' => $functionsCheckResult ? 'bg-success' : 'bg-danger',
                'message' => $functionsCheckResult ? '' : sprintf('%s: %s', __('Install::install.server_status_not_enabled'), implode(', ', array_column(array_filter($functionsCheck, function ($item) {
                    return $item['function_disabled'];
                }), 'function'))),
            ],
        ];

        return $data;
    }

    public function checkComposerVersion()
    {
        $composerVersion = AppHelper::getComposerVersionInfo()['version'];

        return version_compare($composerVersion, '2.5.0', '>=');
    }

    protected function getDirPermission()
    {
        $dir = [
            'bootstrap/cache',
            'storage/',
        ];

        $dirPermissions = [];
        foreach ($dir as $item) {
            $path = base_path($item);

            $permission = is_writable($path);

            $dirPermissions[$item]['dir'] = $item;
            $dirPermissions[$item]['writable'] = $permission;
        }

        return $dirPermissions;
    }

    protected function extensionCheck()
    {
        $extensions = [
            'fileinfo',
            'exif',
        ];

        $extensionsCheckResult = [];
        foreach ($extensions as $item) {
            $extensionsCheckResult[$item]['extension'] = $item;
            $extensionsCheckResult[$item]['loaded'] = extension_loaded($item);
        }

        return $extensionsCheckResult;
    }

    protected function functionCheck()
    {
        $functions = [
            'putenv',
            'symlink',
            'readlink',
            'proc_open',
            'passthru',
        ];

        $disableFunction = explode(',', ini_get('disable_functions'));

        $functionsCheckResult = [];
        foreach ($functions as $item) {
            $functionsCheckResult[$item]['function'] = $item;
            $functionsCheckResult[$item]['function_disabled'] = in_array($item, $disableFunction);
        }

        return $functionsCheckResult;
    }

    protected function writeEnvironment(array $data)
    {
        // Get the config file template
        $envExamplePath = __DIR__.'/../../.env.template';
        $envPath = base_path('.env');

        $envTemp = file_get_contents($envExamplePath);

        $appKey = \Illuminate\Encryption\Encrypter::generateKey(config('app.cipher'));
        $appKey = sprintf('base64:%s', base64_encode($appKey));

        $appUrl = str_replace(\request()->getRequestUri(), '', \request()->getUri());

        // Temp write key
        $template['APP_KEY'] = $appKey;
        $template['APP_URL'] = $appUrl;
        $template['DB_HOST'] = $data['database']['DB_HOST'];
        $template['DB_PORT'] = $data['database']['DB_PORT'];
        $template['DB_DATABASE'] = $data['database']['DB_DATABASE'];
        $template['DB_USERNAME'] = $data['database']['DB_USERNAME'];
        $template['DB_PASSWORD'] = $data['database']['DB_PASSWORD'];
        $template['DB_TIMEZONE'] = $data['database']['DB_TIMEZONE'];
        $template['DB_PREFIX'] = $data['database']['DB_PREFIX'];

        foreach ($template as $key => $value) {
            $envTemp = str_replace('{'.$key.'}', $value, $envTemp);
        }

        // Write config
        file_put_contents($envPath, $envTemp);
    }

    protected function installMysqlDatabase()
    {
        (new \Illuminate\Database\DatabaseServiceProvider(app()))->register();

        $cmds = [
            'key:generate',
            'migrate',
            'db:seed',
            'storage:link',

            'plugin:migrate',
        ];

        $code = 0;
        $output = [];
        foreach ($cmds as $cmd) {
            try {
                $code = \Illuminate\Support\Facades\Artisan::call(command: $cmd, parameters: [
                    '--force' => true,
                ]);
            } catch (\Throwable $e) {
                $output[] = \Artisan::output();
                $output[] = $e->getMessage();
                break;
            }

            $output[] = \Artisan::output();
        }

        return [
            'code' => $code,
            'output' => implode('', $output),
        ];
    }

    protected function generateAdminInfo(array $data)
    {
        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->addAccount([
            'type' => 1,
            'account' => $data['admin_info']['email'],
            'password' => $data['admin_info']['password'],
        ]);

        $aid = $fresnsResp->getData('aid');

        \FresnsCmdWord::plugin('Fresns')->addUser([
            'aid' => $aid,
            'nickname' => 'Admin',
            'username' => 'admin',
        ]);

        // set account to admin
        $result = Account::whereAid($aid)->update([
            'type' => 1,
        ]);

        \Artisan::call('plugin:install', [
            'path' => realpath(base_path('extensions/plugins/FresnsEngine')),
        ]);
        \Artisan::call('theme:install', [
            'path' => realpath(base_path('extensions/themes/ThemeFrame')),
        ]);

        AppHelper::setInitialConfiguration();

        info('update type', [$result, $aid]);
    }

    protected function writeInstallTime()
    {
        $item = [
            'item_key' => 'install_datetime',
            'item_value' => Carbon::createFromFormat('Y-m-d H:i:s', now()),
        ];

        Config::updateOrCreate([
            'item_key' => $item['item_key'],
        ], $item);

        // install.lock
        $installLock = base_path('install.lock');

        file_put_contents($installLock, now()->toDateTimeString());
    }
}
