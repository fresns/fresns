<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsInstall;

use App\Helpers\StrHelper;
use App\Http\FresnsApi\Helpers\ApiCommonHelper;
use App\Http\FresnsCmd\FresnsSubPluginService;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsMembers\FresnsMembersConfig;
use App\Http\FresnsDb\FresnsMemberStats\FresnsMemberStats;
use App\Http\FresnsDb\FresnsUsers\FresnsUsers;
use App\Http\FresnsDb\FresnsUsers\FresnsUsersConfig;
use App\Http\FresnsDb\FresnsUserWallets\FresnsUserWallets;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InstallService
{
    const INSTALL_EXTENSIONS = ['fileinfo'];
    const INSTALL_FUNCTIONS = ['putenv', 'symlink', 'readlink', 'proc_open'];
    const INSTALL_TABLES = [
        'code_messages', 'configs', 'languages', 'session_keys', 'session_tokens', 'session_logs', 'verify_codes', 'users', 'user_connects', 'user_wallets', 'user_wallet_logs',
        'files', 'file_appends', 'file_logs', 'plugins', 'plugin_usages', 'plugin_badges', 'plugin_callbacks', 'members', 'member_stats', 'member_roles', 'member_role_rels', 'member_icons',
        'member_likes', 'member_follows', 'member_shields', 'emojis', 'stop_words', 'dialogs', 'dialog_messages', 'notifies', 'implants', 'seo', 'groups', 'posts', 'post_appends',
        'post_allows', 'post_members', 'post_logs', 'comments', 'comment_appends', 'comment_logs', 'extends', 'extend_linkeds', 'hashtags', 'hashtag_linkeds', 'domains', 'domain_links', 'mentions',
    ];

    /**
     * install mode.
     */
    public static function mode()
    {
        $path = request()->path();
        if (in_array($path, ['install/fresns', 'install/step1', 'install/step2', 'install/step3', 'install/done', 'install/env', 'install/manage'])) {
            return true;
        }

        return false;
    }

    /**
     * check install order.
     */
    public static function checkPermission()
    {
        $path = request()->path();
        switch ($path) {
            case 'install/step1':
                if (Cache::get('install_index')) {
                    return ['code'=>'000000'];
                } else {
                    return ['code'=>'200000', 'url'=>route('install.index')];
                }
                break;
            case 'install/step2':
                if (Cache::get('install_step1')) {
                    return ['code'=>'000000'];
                } else {
                    return ['code'=>'200000', 'url'=>route('install.step1')];
                }
                break;
            case 'install/step3':
                if (Cache::get('install_step2')) {
                    return ['code'=>'000000'];
                } else {
                    return ['code'=>'200000', 'url'=>route('install.step2')];
                }
                break;
            case 'install/done':
                if (Cache::get('install_step3')) {
                    return ['code'=>'000000'];
                } else {
                    return ['code'=>'200000', 'url'=>route('install.step3')];
                }
                break;
            default:
                    return ['code'=>'000000'];
                break;
        }
    }

    /**
     * check env.
     */
    public static function envDetect($name = '')
    {
        try {
            switch ($name) {
                case 'php_version':
                    $value = PHP_VERSION;
                    if ($value !== '' && version_compare(PHP_VERSION, '7.3', '>=')) {
                        $html = '<span class="badge bg-success rounded-pill">'.trans('install.statusSuccess', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '000000', 'message' => 'Check Success', 'result'=>$html];
                    } else {
                        Cache::forget('install_step1');
                        $html = '<span class="badge bg-danger rounded-pill">'.trans('install.statusFailure', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '100000', 'message' => 'Check Failure', 'result'=>$html];
                    }
                    break;
                case 'https':
                    $value = self::isHttps();
                    if ($value) {
                        $html = '<span class="badge bg-success rounded-pill">'.trans('install.statusSuccess', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '000000', 'message' => 'Check Success', 'result'=>$html];
                    } else {
                        $html = '<span class="badge bg-warning rounded-pill">'.trans('install.statusWarning', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '000000', 'message' => 'Check Failure', 'result'=>$html];
                    }
                    break;
                case 'folder':
                    $value = self::filePerms(base_path());
                    if ($value >= 755) {
                        $html = '<span class="badge bg-success rounded-pill">'.trans('install.statusSuccess', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '000000', 'message' => 'Check Success', 'result'=>$html];
                    } else {
                        Cache::forget('install_step1');
                        $html = '<span class="badge bg-danger rounded-pill">'.trans('install.statusFailure', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '100000', 'message' => 'Check Failure', 'result'=>$html];
                    }
                    break;
                case 'extensions':
                    $value = [];
                    $extensions = get_loaded_extensions();
                    foreach (self::INSTALL_EXTENSIONS as $v) {
                        if (! in_array($v, $extensions)) {
                            $value[] = $v;
                        }
                    }
                    if (empty($value)) {
                        $html = '<span class="badge bg-success rounded-pill">'.trans('install.statusSuccess', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '000000', 'message' => 'Check Success', 'result'=>$html];
                    } else {
                        Cache::forget('install_step1');
                        $disabled = implode('&nbsp;&nbsp;', $value);
                        $html = '<span class="me-3"><small class="text-muted">'.trans('install.statusNotEnabled', [], Cache::get('install_lang')).': '.$disabled.'</small></span>';
                        $html .= '<span class="badge bg-danger rounded-pill">'.trans('install.statusFailure', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '100000', 'message' => 'Check Failure', 'result'=>$html];
                    }
                    break;
                case 'functions':
                    $value = [];
                    $disable = get_cfg_var('disable_functions');
                    $disable = explode(',', $disable);
                    foreach ($disable as $v) {
                        if (in_array($v, self::INSTALL_FUNCTIONS)) {
                            $value[] = $v;
                        }
                    }
                    if (empty($value)) {
                        $html = '<span class="badge bg-success rounded-pill">'.trans('install.statusSuccess', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '000000', 'message' => 'Check Success', 'result'=>$html];
                    } else {
                        Cache::forget('install_step1');
                        $disabled = implode('&nbsp;&nbsp;', $value);
                        $html = '<span class="me-3"><small class="text-muted">'.trans('install.statusNotEnabled', [], Cache::get('install_lang')).': '.$disabled.'</small></span>';
                        $html .= '<span class="badge bg-danger rounded-pill">'.trans('install.statusFailure', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '100000', 'message' => 'Check Failure', 'result'=>$html];
                    }
                    break;
                case 'mysql_version':
                    $versionObj = DB::selectOne('select version()  as version;');
                    $value = $versionObj->version;
                    if ($value !== '' && version_compare($value, '5.7', '>=')) {
                        $html = '<span class="badge bg-success rounded-pill">'.trans('install.statusSuccess', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '000000', 'message' => 'Check Success', 'result'=>$html];
                    } else {
                        Cache::forget('install_step2');
                        $html = '<span class="badge bg-danger rounded-pill">'.trans('install.statusFailure', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '100000', 'message' => 'Check Failure', 'result'=>$html];
                    }
                    break;
                case 'database_table_prefix':
                    set_time_limit(0);
                    defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
                    $value = config('database.connections.mysql.prefix');
                    if ($value) {
                        $html = '<span class="badge bg-info rounded-pill">'.$value.'</span>';

                        return ['code' => '000000', 'message' => 'Check Success', 'result'=>$html];
                    } else {
                        Cache::forget('install_step2');
                        $html = '<span class="badge bg-warning rounded-pill">'.$value.'</span>';

                        return ['code' => '100000', 'message' => 'Check Failure', 'result'=>$html];
                    }
                    break;
                case 'database_migrate':
                    set_time_limit(0);
                    try {
                        $value = true;
                        $database = config('database.connections.mysql.database');
                        $prefix = config('database.connections.mysql.prefix');
                        $db_tables = DB::select('show tables;');
                        $db_tables = collect($db_tables)->pluck('Tables_in_'.$database)->values()->toArray();
                        foreach (self::INSTALL_TABLES as $table) {
                            if (! in_array($prefix.$table, $db_tables)) {
                                $value = false;
                                break;
                            }
                        }

                        if (! $value) {
                            Artisan::call('migrate', ['--force' => true]);
                        }
                        $html = '<span class="badge bg-success rounded-pill">'.trans('install.statusSuccess', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '000000', 'message' => 'Check Success', 'result'=>$html];
                    } catch (\Exception $e) {
                        Cache::forget('install_step2');
                        $html = '<span class="me-3"><small class="text-muted">'.$e->getMessage().'</small></span>';
                        $html .= '<span class="badge bg-danger rounded-pill">'.trans('install.statusFailure', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '100000', 'message' => 'Check Failure', 'result'=>$html];
                    }
                    break;
                case 'database_seed':
                    set_time_limit(0);
                    try {
                        Artisan::call('db:seed', ['--force' => true]);

                        $html = '<span class="badge bg-success rounded-pill">'.trans('install.statusSuccess', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '000000', 'message' => 'Check Success', 'result'=>$html];
                    } catch (\Exception $e) {
                        Cache::forget('install_step2');
                        $html = '<span class="me-3"><small class="text-muted">'.$e->getMessage().'</small></span>';
                        $html .= '<span class="badge bg-danger rounded-pill">'.trans('install.statusFailure', [], Cache::get('install_lang')).'</span>';

                        return ['code' => '100000', 'message' => 'Check Failure', 'result'=>$html];
                    }
                    break;
                default:
                    return ['code' => '200000', 'message' => 'Name parameter error'];
            }
        } catch (\Exception $e) {
            return ['code' => '999999', 'message' => $e->getMessage()];
        }
    }

    /**
     * init manager user.
     */
    public static function registerUser($params = [])
    {
        try {
            $input = [
                'email' => $params['email'] ?: null,
                'country_code' => $params['purePhone'] ? $params['countryCode'] : null,
                'pure_phone' => $params['purePhone'] ?: null,
                'phone' => $params['purePhone'] ? $params['countryCode'].$params['purePhone'] : null,
                'api_token' => StrHelper::createToken(),
                'uuid' => StrHelper::createUuid(),
                'last_login_at' => date('Y-m-d H:i:s'),
                'user_type' => 1,
                'password' => StrHelper::createPassword($params['password']),
            ];
            $uid = FresnsUsers::insertGetId($input);
            FresnsSubPluginService::addSubTablePluginItem(FresnsUsersConfig::CFG_TABLE, $uid);
            $memberInput = [
                'user_id' => $uid,
                'name' => StrHelper::createToken(rand(6, 8)),
                'nickname' => $params['nickname'],
                'uuid' => ApiCommonHelper::createMemberUuid(),
            ];
            $mid = FresnsMembers::insertGetId($memberInput);

            FresnsSubPluginService::addSubTablePluginItem(FresnsMembersConfig::CFG_TABLE, $mid);
            //成员总数
            self::updateOrInsertConfig('user_counts', 1, 'number', 'stats');
            self::updateOrInsertConfig('member_counts', 1, 'number', 'stats');

            // Register successfully to add records to the table
            $memberStatsInput = ['member_id' => $mid];
            FresnsMemberStats::insert($memberStatsInput);

            $userWalletsInput = ['user_id' => $uid, 'balance' => 0];
            FresnsUserWallets::insert($userWalletsInput);

            $memberRoleRelsInput = ['member_id' => $mid, 'role_id' => 1, 'type' => 2];
            FresnsMemberRoleRels::insert($memberRoleRelsInput);

            return ['code' => '000000', 'message' => 'success'];
        } catch (\Exception $e) {
            return ['code' => $e->getCode(), 'message' => $e->getMessage()];
        }
    }

    /**
     * set configs.
     */
    public static function updateOrInsertConfig($itemKey, $itemValue = '', $item_type = 'string', $item_tag = 'systems')
    {
        try {
            $cond = ['item_key'   => $itemKey];
            $upInfo = ['item_value'   => $itemValue, 'item_type'=>$item_type, 'item_tag'=>$item_tag];
            DB::table('configs')->updateOrInsert($cond, $upInfo);

            return ['code' => '000000', 'message' => 'success'];
        } catch (\Exception $e) {
            return ['code' => $e->getCode(), 'message' => $e->getMessage()];
        }
    }

    /**
     * permission.
     */
    public static function filePerms($file)
    {
        clearstatcache();
        if (! file_exists($file)) {
            return false;
        }
        $perms = fileperms($file);

        return substr(decoct($perms), -3);
    }

    public static function isHttps()
    {
        if (! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        } elseif (! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        }

        return false;
    }
}
