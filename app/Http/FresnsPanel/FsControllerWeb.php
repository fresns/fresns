<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsPanel;

use App\Base\Controllers\BaseFrontendController;
use App\Helpers\HttpHelper;
use App\Helpers\StrHelper;
use App\Http\Auth\User;
use App\Http\Center\Base\PluginConst;
use App\Http\Center\Common\ErrorCodeService;
use App\Http\Center\Common\LogService;
use App\Http\Center\Helper\InstallHelper;
use App\Http\Center\Helper\PluginHelper;
use App\Http\FresnsApi\Helpers\ApiCommonHelper;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsDb\FresnsComments\FresnsComments;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigs;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Http\FresnsDb\FresnsGroups\FresnsGroups;
use App\Http\FresnsDb\FresnsHashtags\FresnsHashtags;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsPlugins\FresnsPlugins;
use App\Http\FresnsDb\FresnsPlugins\FresnsPluginsService as FresnsPluginFresnsPluginsService;
use App\Http\FresnsDb\FresnsPosts\FresnsPosts;
use App\Http\FresnsDb\FresnsSessionKeys\FresnsSessionKeys;
use App\Http\FresnsDb\FresnsSessionKeys\FresnsSessionKeysService;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogs;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogsConfig;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogsService;
use App\Http\FresnsDb\FresnsUsers\FresnsUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class FsControllerWeb extends BaseFrontendController
{
    // Version Info
    public function __construct()
    {
        $fresnsVersion = ApiConfigHelper::getConfigByItemKey('fresns_version');

        View::share('version', $fresnsVersion ?? '');

        request()->offsetSet('is_control_api', 1);
    }

    // Login Page
    public function index()
    {
        $lang = request()->input('lang', 'en');
        $data = [
            'lang' => $lang,
            'location' => 'action',
            'choose' => 'index',
            'title' => 'Home',
        ];

        return view('fresns.index', $data);
    }

    // After Login Status Expires
    public function loginIndex()
    {
        $lang = request()->input('lang', 'en');
        $data = [
            'lang' => $lang,
        ];

        return view('fresns.login', $data);
    }

    // Login Request
    public function loginAcc(Request $request)
    {
        $account = $request->input('account');
        $password = $request->input('password');

        $user = FresnsUsers::where('is_enable', 1)->where('user_type', FsConfig::USER_TYPE_ADMIN)->where('phone', $account)->first();
        if (empty($user)) {
            $user = FresnsUsers::where('is_enable', 1)->where('user_type', FsConfig::USER_TYPE_ADMIN)->where('email', $account)->first();
        }

        if (empty($user)) {
            $this->error(ErrorCodeService::ACCOUNT_ERROR);
        }

        $password = base64_decode($password, true);
        $credentials = [
            'login_name' => $account,
            'password' => $password,
        ];

        $result = $this->attemptLogin($credentials);
        if ($result == false) {
            $this->error(ErrorCodeService::ACCOUNT_ERROR);
        }

        $user = User::find($user['id']);

        Auth::login($user);

        $lang = $request->input('lang', 'en');
        if (empty($lang)) {
            $lang = 'en';
        }
        Cache::forever('lang_tag_'.$user['id'], $lang);

        App::setLocale($lang);

        return redirect('/fresns/dashboard');
    }

    // Check Login
    public function checkLogin(Request $request)
    {
        $account = $request->input('account');
        $password = $request->input('password');

        $password = base64_decode($password, true);

        $user = FresnsUsers::where('is_enable', 1)->where('user_type', FsConfig::USER_TYPE_ADMIN)->where('phone', $account)->first();

        if (empty($user)) {
            $user = FresnsUsers::where('is_enable', 1)->where('user_type', FsConfig::USER_TYPE_ADMIN)->where('email', $account)->first();
        }

        if (empty($user)) {
            $this->error(ErrorCodeService::ACCOUNT_ERROR);
        }

        $sessionLogId = FresnsSessionLogsService::addConsoleSessionLogs(3, 'Console Login Check', $user->id);

        if ($sessionLogId) {
            $sessionInput = [
                'object_order_id' => $user->id,
                'user_id' => $user->id,
            ];
            FresnsSessionLogs::where('id', $sessionLogId)->update($sessionInput);
        }

        // If the number of times of wrong login password in the past 1 hour reaches 5, the login will be restricted.
        // session_logs = 3
        $startTime = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $sessionCount = FresnsSessionLogs::where('created_at', '>=', $startTime)
        ->where('user_id', $user->id)
        ->where('object_result', FresnsSessionLogsConfig::OBJECT_RESULT_ERROR)
        ->where('object_type', FresnsSessionLogsConfig::OBJECT_TYPE_USER_LOGIN)
        ->count();

        if ($sessionCount >= 5) {
            FresnsSessionLogsService::updateSessionLogs($sessionLogId, 1);
            $this->error(ErrorCodeService::ACCOUNT_COUNT_ERROR);
        }

        $credentials = [
            'login_name' => $account,
            'password' => $password,
        ];

        $result = $this->attemptLogin($credentials);

        if ($result == false) {
            FresnsSessionLogsService::updateSessionLogs($sessionLogId, 1);
            $this->error(ErrorCodeService::ACCOUNT_ERROR);
        }

        FresnsSessionLogsService::updateSessionLogs($sessionLogId, 2);

        return $this->success();
    }

    // Logout
    public function logout(Request $request)
    {
        $userId = Auth::id();

        Auth::logout();
        $request->session()->flush();
        $adminPath = ApiConfigHelper::getConfigByItemKey(FresnsConfigsConfig::BACKEND_PATH) ?? 'admin';
        $lang = Cache::get('lang_tag_'.$userId);

        $adminPath = '/fresns'."/$adminPath"."?lang=$lang";

        return redirect("$adminPath");
    }

    // Setting Language
    public function setLanguage(Request $request)
    {
        $lang = $request->input('lang', 'en');
        $userId = Auth::id();

        Cache::forever('lang_tag_'.$userId, $lang);

        $this->success();
    }

    // Dashboard Page
    public function dashboard(Request $request)
    {
        $userId = Auth::id();
        $langTag = Cache::get('lang_tag_'.$userId);
        $FresnsPluginsService = new FresnsPluginFresnsPluginsService();
        $request->offsetSet('type', FsConfig::PLUGIN_TYPE2);
        $pluginList = $FresnsPluginsService->searchData();
        $pluginArr = FresnsPluginsResource::collection($pluginList['list'])->toArray($pluginList['list']);
        $newVision = [];
        if ($pluginArr) {
            foreach ($pluginArr as $key => $p) {
                if ($key == 5) {
                    break;
                }
                $arr = [];
                if ($p['isDownload'] == 1 && $p['isNewVision'] == 1) {
                    $arr = $p;
                    $newVision[] = $arr;
                }
            }
        }

        // Overview
        $userCount = FresnsUsers::count();
        $memberCount = FresnsMembers::count();
        $groupCount = FresnsGroups::count();
        $hashtagCount = FresnsHashtags::count();
        $postCount = FresnsPosts::count();
        $commentCount = FresnsComments::count();

        // Extensions
        $plugin1 = FresnsPlugins::where('type', 1)->count();
        $plugin2 = FresnsPlugins::where('type', 2)->count();
        $plugin3 = FresnsPlugins::where('type', 3)->count();
        $plugin4 = FresnsPlugins::where('type', 4)->count();
        $plugin5 = FresnsPlugins::where('type', 5)->count();
        $keysCount = FresnsSessionKeys::count();

        $total['user_count'] = $userCount;
        $total['member_count'] = $memberCount;
        $total['group_count'] = $groupCount;
        $total['hashtag_count'] = $hashtagCount;
        $total['post_count'] = $postCount;
        $total['comment_count'] = $commentCount;
        $total['plugin_1'] = $plugin1;
        $total['plugin_2'] = $plugin2;
        $total['plugin_3'] = $plugin3;
        $total['plugin_4'] = $plugin4;
        $total['plugin_5'] = $plugin5;
        $total['keys_count'] = $keysCount;

        // Fresns Events and News
        $url = FsConfig::NOTICE_URL;

        $userId = Auth::id();

        App::setLocale($langTag);

        $json = HttpHelper::curlRequest($url);
        $noticeArr = [];
        if (! empty($json)) {
            $jsonArr = json_decode($json, true);
            if (! empty($jsonArr)) {
                foreach ($jsonArr as $v) {
                    if ($v['langTag'] == $langTag) {
                        $noticeArr[] = $v['content'];
                        break;
                    }
                }
            }
        }

        $data = [
            'lang' => $langTag,
            'location' => 'dashboard',
            'choose' => 'dashboard',
            'newVisionPlugin' => $newVision,
            'title' => 'Dashboard',
            'total' => $total,
            'notice_arr' => $noticeArr,
            'lang_desc' => FsService::getLanguage($langTag),
        ];

        return view('fresns.dashboard', $data);
    }

    // Settings Page
    public function settings()
    {
        $userArr = FresnsUsers::where('is_enable', 1)->where('user_type', FsConfig::USER_TYPE_ADMIN)->get([
            'id',
            'uuid',
            'phone',
            'email',
            'country_code',
            'pure_phone',
        ])->toArray();
        foreach ($userArr as &$v) {
            $v['phone_desc'] = 'null';
            $v['email_desc'] = 'null';
            if (! empty($v['pure_phone'])) {
                $v['phone_desc'] = '+'.$v['country_code'].ApiCommonHelper::encryptPhone($v['pure_phone']);
            }
            if (! empty($v['email'])) {
                $v['email_desc'] = ApiCommonHelper::encryptPhone($v['email']);
            }
        }

        $backend_url = ApiConfigHelper::getConfigByItemKey(FresnsConfigsConfig::BACKEND_DOMAIN);

        $admin_path = ApiConfigHelper::getConfigByItemKey(FresnsConfigsConfig::BACKEND_PATH) ?? 'admin';
        $site_url = ApiConfigHelper::getConfigByItemKey(FresnsConfigsConfig::SITE_DOMAIN);
        $path = '';
        if ($backend_url) {
            $path = $backend_url.'/fresns'."/$admin_path";
        }

        $userId = Auth::id();
        $lang = Cache::get('lang_tag_'.$userId);

        App::setLocale($lang);

        $data = [
            'lang' => $lang,
            'choose' => 'settings',
            'location' => 'settings',
            'title' => 'Settings',
            'user_arr' => $userArr,
            'backend_url' => $backend_url,
            'admin_path' => $admin_path,
            'site_url' => $site_url,
            'path' => $path,
            'lang_desc' => FsService::getLanguage($lang),
        ];

        return view('fresns.settings', $data);
    }

    // Settings Page: Fresns Console
    public function updateSetting(Request $request)
    {
        $backend_url = $request->input('backend_url');
        $backend_url_end = substr($backend_url, -1);
        if ($backend_url_end == '/') {
            $backend_url = substr($backend_url, 0, -1);
        }

        $admin_path = $request->input('admin_path');

        $pathNot = FsConfig::BACKEND_PATH_NOT;
        if (in_array($admin_path, $pathNot)) {
            $this->error(ErrorCodeService::BACKEND_PATH_ERROR);
        }
        $site_url = $request->input('site_url');
        $site_url_end = substr($site_url, -1);
        if ($site_url_end == '/') {
            $site_url = substr($site_url, 0, -1);
        }
        $backend_url_config = FresnsConfigs::where('item_key', FresnsConfigsConfig::BACKEND_DOMAIN)->first();
        if ($backend_url_config) {
            FresnsConfigs::where('item_key', FresnsConfigsConfig::BACKEND_DOMAIN)->update(['item_value' => $backend_url]);
        } else {
            $input = [
                'item_key' => FresnsConfigsConfig::BACKEND_DOMAIN,
                'item_tag' => 'backends',
                'item_value' => $backend_url,
                'item_type' => 'string',
            ];

            FresnsConfigs::insert($input);
        }
        $admin_path_config = FresnsConfigs::where('item_key', FresnsConfigsConfig::BACKEND_PATH)->first();
        if ($admin_path_config) {
            FresnsConfigs::where('item_key', FresnsConfigsConfig::BACKEND_PATH)->update(['item_value' => $admin_path]);
        } else {
            $input = [
                'item_key' => FresnsConfigsConfig::BACKEND_PATH,
                'item_tag' => 'backends',
                'item_value' => $admin_path,
                'item_type' => 'string',

            ];
            FresnsConfigs::insert($input);
        }
        $site_url_config = FresnsConfigs::where('item_key', FresnsConfigsConfig::SITE_DOMAIN)->first();
        if ($site_url_config) {
            FresnsConfigs::where('item_key', FresnsConfigsConfig::SITE_DOMAIN)->update(['item_value' => $site_url]);
        } else {
            $input = [
                'item_key' => FresnsConfigsConfig::SITE_DOMAIN,
                'item_tag' => 'sites',
                'item_value' => $site_url,
                'item_type' => 'string',
            ];
            FresnsConfigs::insert($input);
        }

        return $this->success();
    }

    // Settings Page: System Administrator (add admin)
    public function addAdmin(Request $request)
    {
        $account = $request->input('account');
        if (empty($account)) {
            $this->error(ErrorCodeService::ACCOUNT_IS_EMPTY_ERROR);
        }

        $user = FresnsUsers::where('is_enable', 1)->where('user_type', '!=', FsConfig::USER_TYPE_ADMIN)->where(function ($query) {
            $account = request()->input('account');
            $query->where('phone', $account)->orWhere('email', $account);
        })->first();

        if (empty($user)) {
            $this->error(ErrorCodeService::ACCOUNT_CHECK_ERROR);
        }

        FresnsUsers::where('id', $user['id'])->update(['user_type' => FsConfig::USER_TYPE_ADMIN]);

        $this->success();
    }

    // Settings Page: System Administrator (delete admin)
    public function delAdmin(Request $request)
    {
        $uuid = $request->input('uuid');
        $user = Auth::user();
        if ($uuid == $user['uuid']) {
            $this->error(ErrorCodeService::DELETE_ADMIN_ERROR);
        }
        FresnsUsers::where('uuid', $uuid)->update(['user_type' => FsConfig::USER_TYPE_USER]);

        $this->success();
    }

    // Keys Page
    public function keys(Request $request)
    {
        $current = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $request->offsetSet('currentPage', $current);
        $request->offsetSet('pageSize', $pageSize);
        $FresnsSessionKeysService = new FresnsSessionKeysService();
        $keyLists = $FresnsSessionKeysService->searchData();

        $pluginArr = FresnsKeysResource::collection($keyLists['list'])->toArray($keyLists['list']);
        $clientData = FresnsSessionKeys::getByStaticWithCond()->toArray();
        $platforms = FresnsConfigs::where('item_key', 'platforms')->first(['item_value']);
        $platforms = json_decode($platforms['item_value'], true);
        $cond = [
            ['type', '!=', 5],
        ];
        $plugin = FresnsPlugins::getByStaticWithCond($cond)->toArray();

        $userId = Auth::id();
        $lang = Cache::get('lang_tag_'.$userId);

        App::setLocale($lang);
        $data = [
            'lang' => $lang,
            'data' => $keyLists,
            'page' => $current,
            'location' => $pluginArr,
            'choose' => 'keys',
            'platform' => $platforms,
            'plugin' => $plugin,
            'title' => 'Keys',
            'lang_desc' => FsService::getLanguage($lang),
        ];

        return view('fresns.keys', $data);
    }

    // Keys Page: add key
    public function submitKey(Request $request)
    {
        $platformId = $request->input('platformId');
        $keyName = $request->input('keyName');
        $type = $request->input('type');
        $plugin = $type == 2 ? $request->input('plugin') : null;
        $app_id = strtolower('tw'.StrHelper::randString(14));
        $app_secret = strtolower(StrHelper::randString(32));
        $enAbleStatus = $request->input('enAbleStatus');
        if (! $keyName) {
            $this->error(ErrorCodeService::KEY_NAME_ERROR);
        }
        if ($platformId == 'Select a key application platform') {
            $this->error(ErrorCodeService::KEY_PLATFORM_ERROR);
        }
        if ($type == 2) {
            if (! $plugin || $plugin == 'Select which plugin to use the key for') {
                $this->error(ErrorCodeService::KEY_PLUGIN_ERROR);
            }
        }
        $input = [
            'platform_id' => $platformId,
            'name' => $keyName,
            'type' => $type,
            'plugin_unikey' => $plugin,
            'app_id' => $app_id,
            'app_secret' => $app_secret,
            'is_enable' => $enAbleStatus,
        ];
        (new FresnsSessionKeys())->store($input);
        $this->success();
    }

    // Keys Page: reset key
    public function resetKey(Request $request)
    {
        $id = $request->input('data_id');
        $app_secret = strtolower(StrHelper::randString(32));
        FresnsSessionKeys::where('id', $id)->update(['app_secret' => $app_secret]);
        $this->success();
    }

    // Keys Page: edit key
    public function updateKey(Request $request)
    {
        $id = $request->input('id');
        $platformId = $request->input('platformId');
        $keyName = $request->input('keyName');
        $type = $request->input('type');
        $plugin = ($type == 2) ? $request->input('plugin') : null;
        $enAbleStatus = $request->input('enAbleStatus');
        if (! $keyName) {
            $this->error(ErrorCodeService::KEY_NAME_ERROR);
        }
        if ($platformId == 'Select a key application platform') {
            $this->error(ErrorCodeService::KEY_PLATFORM_ERROR);
        }
        if ($type == 2) {
            if (! $plugin || $plugin == 'Select which plugin to use the key for') {
                $this->error(ErrorCodeService::KEY_PLUGIN_ERROR);
            }
        }
        $input = [
            'platform_id' => $platformId,
            'name' => $keyName,
            'type' => $type,
            'plugin_unikey' => $plugin,
            'is_enable' => $enAbleStatus,
        ];
        FresnsSessionKeys::where('id', $id)->update($input);
        $this->success();
    }

    // Keys Page: delete key
    public function delKey(Request $request)
    {
        $id = $request->input('data_id');
        FresnsSessionKeys::where('id', $id)->delete();
        $this->success();
    }

    // Admins Page (Control Panel)
    public function admins(Request $request)
    {
        $current = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 50);
        $FresnsPluginsService = new FresnsPluginFresnsPluginsService();
        $request->offsetSet('type', FsConfig::PLUGIN_TYPE4);
        $request->offsetSet('currentPage', $current);
        $request->offsetSet('pageSize', $pageSize);
        $pluginList = $FresnsPluginsService->searchData();
        $pluginArr = FresnsPluginsResource::collection($pluginList['list'])->toArray($pluginList['list']);

        $userId = Auth::id();
        $lang = Cache::get('lang_tag_'.$userId);

        App::setLocale($lang);

        $data = [
            'lang' => $lang,
            'choose' => 'admins',
            'location' => $pluginArr,
            'title' => 'Admins',
            'lang_desc' => FsService::getLanguage($lang),
        ];

        return view('fresns.admins', $data);
    }

    // Websites Page
    public function websites(Request $request)
    {
        $current = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 50);
        $FresnsPluginsService = new FresnsPluginFresnsPluginsService();
        $request->offsetSet('currentPage', $current);
        $request->offsetSet('pageSize', $pageSize);
        $pluginList = $FresnsPluginsService->searchData();
        $pluginArr = FresnsPluginsResource::collection($pluginList['list'])->toArray($pluginList['list']);
        $websitePluginArr = [];
        $subjectPluginArr = [];
        foreach ($pluginArr as $p) {
            if ($p['type'] == 1) {
                $websitePluginArr[] = $p;
            }
            if ($p['type'] == 5) {
                $subjectPluginArr[] = $p;
            }
        }

        $userId = Auth::id();
        $lang = Cache::get('lang_tag_'.$userId);

        App::setLocale($lang);

        $data = [
            'lang' => $lang,
            'location' => 'index',
            'choose' => 'websites',
            'websitePluginArr' => $websitePluginArr,
            'subjectPluginArr' => $subjectPluginArr,
            'title' => 'Websites',
            'lang_desc' => FsService::getLanguage($lang),
        ];

        return view('fresns.websites', $data);
    }

    // Apps Page(App Client Companion Plugin)
    public function apps(Request $request)
    {
        $current = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 50);
        $FresnsPluginsService = new FresnsPluginFresnsPluginsService();
        $request->offsetSet('type', FsConfig::PLUGIN_TYPE3);
        $request->offsetSet('currentPage', $current);
        $request->offsetSet('pageSize', $pageSize);
        $pluginList = $FresnsPluginsService->searchData();
        $pluginArr = FresnsPluginsResource::collection($pluginList['list'])->toArray($pluginList['list']);

        $userId = Auth::id();
        $lang = Cache::get('lang_tag_'.$userId);

        App::setLocale($lang);
        $data = [
            'lang' => $lang,
            'choose' => 'apps',
            'location' => $pluginArr,
            'title' => 'Apps',
            'lang_desc' => FsService::getLanguage($lang),
        ];

        return view('fresns.apps', $data);
    }

    // Plugins Page
    public function plugins(Request $request)
    {
        $current = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 20);
        $FresnsPluginsService = new FresnsPluginFresnsPluginsService();
        $request->offsetSet('type', FsConfig::PLUGIN_TYPE2);
        $request->offsetSet('currentPage', $current);
        $request->offsetSet('pageSize', $pageSize);
        $pluginList = $FresnsPluginsService->searchData();

        $enableCount = 0;
        $unEnableCount = 0;
        $pluginArr = FresnsPluginsResource::collection($pluginList['list'])->toArray($pluginList['list']);

        foreach ($pluginArr as $p) {
            if ($p['is_enable'] == 0) {
                $unEnableCount++;
            }
            if ($p['is_enable'] == 1) {
                $enableCount++;
            }
        }

        // Pagination
        $pagination = $pluginList['pagination'];
        if ($pagination['total'] != 0) {
            $totalPage = (int) ceil($pagination['total'] / $pageSize);
        } else {
            $totalPage = 1;
        }

        $userId = Auth::id();
        $lang = Cache::get('lang_tag_'.$userId);

        App::setLocale($lang);

        $data = [
            'lang' => $lang,
            'location' => $pluginArr,
            'unEnableCount' => $unEnableCount,
            'enableCount' => $enableCount,
            'data' => $pluginList,
            'page' => $current,
            'title' => 'Plugins',
            'choose' => 'plugins',
            'totalPage' => $totalPage,
            'lang_desc' => FsService::getLanguage($lang),
        ];

        return view('fresns.plugins', $data);
    }

    // Extensions Settings Page Iframe
    public function iframe(Request $request)
    {
        $url = $request->input('url');
        $userId = Auth::id();
        $lang = Cache::get('lang_tag_'.$userId);

        App::setLocale($lang);
        $data = [
            'lang' => $lang,
            'choose' => 'iframe',
            'location' => $url,
            'title' => 'Setting',
            'lang_desc' => FsService::getLanguage($lang),
        ];

        return view('fresns.iframe', $data);
    }

    // Uninstall Extensions
    public function uninstall(Request $request)
    {
        // Provide parameters for whether data should be deleted when the plugin is uninstalled
        // clear_plugin_data = 1 // Delete files and data
        // clear_plugin_data = 0 // Delete files only
        $clear_plugin_data = $request->input('clear_plugin_data');

        $uniKey = $request->input('unikey');

        $installer = InstallHelper::findInstaller($uniKey);
        if (empty($installer)) {
            $this->error(ErrorCodeService::HELPER_EXCEPTION_ERROR);
        }
        $installer->uninstall();

        $plugin = FresnsPlugins::where('unikey', $uniKey)->first();
        if (! $plugin) {
            $this->error(ErrorCodeService::PLUGIN_UNIKEY_ERROR);
        }
        if ($plugin['is_enable'] == 1) {
            $this->error(ErrorCodeService::UNINSTALL_EXTENSION_ERROR);
        }

        $info = PluginHelper::uninstallByUniKey($uniKey);
        InstallHelper::freshSystem();
        // Delete Extensions Data
        // FresnsPlugin::where('unikey', $uniKey)->delete();
        DB::table('plugins')->where('unikey', $uniKey)->delete();
        $this->success($info);
    }

    // Install Extensions
    public function install(Request $request)
    {
        $unikey = $request->input('unikey');
        $pathArr = [
            base_path(),
            'public',
            'storage',
            'plugins',
            $unikey,
        ];
        $downloadFileName = implode(DIRECTORY_SEPARATOR, $pathArr);
        if (! file_exists($downloadFileName)) {
            $this->error(ErrorCodeService::FILE_EXIST_ERROR);
        }

        $options = [];
        $installFileInfo = InstallHelper::installLocalPluginFile($jsonArr['uniKey'], $unikey, $downloadFileName,
            $options);
        $info = [];
        $info['downloadFileName'] = $downloadFileName;
        $info['installFileInfo'] = $installFileInfo;

        // Execute the installation function of the plugin itself
        $installer = InstallHelper::findInstaller($unikey);

        if (empty($installer)) {
            $this->error(ErrorCodeService::HELPER_EXCEPTION_ERROR);
        }

        $installInfo = $installer->install();
        $info['installInfo'] = $installInfo;

        // Install of templates and front-end files
        InstallHelper::pushPluginResourcesFiles($unikey);

        $this->success($info);
    }

    // Local installation, direct override
    public function localInstall(Request $request)
    {
        $dirName = $request->input('dirName');
        if (empty($dirName)) {
            $this->error(ErrorCodeService::FOLDER_NAME_EMPTY_ERROR);
        }

        $downloadFileName = InstallHelper::getPluginExtensionPath($dirName);
        if (! file_exists($downloadFileName)) {
            $this->error(ErrorCodeService::FILE_EXIST_ERROR);
        }

        // 1. Check if the file information is secure

        // 2. Full copy of the file to app/Plugins
        $uniKey = $dirName;
        $options = [];
        $installFileInfo = InstallHelper::installLocalPluginFile($uniKey, $dirName, $downloadFileName, $options);
        $info = [];
        $info['downloadFileName'] = $downloadFileName;
        $info['installFileInfo'] = $installFileInfo;

        // 3. Distribution of documents
        InstallHelper::pushPluginResourcesFiles($uniKey);

        // 4. Plugin Configuration
        $pluginConfig = PluginHelper::findPluginConfigClass($uniKey);
        $type = $pluginConfig->type;

        // 5. Execute the installation function of the plugin itself (the theme template does not need to perform this step)
        if ($type != PluginConst::PLUGIN_TYPE_THEME) {
            $installer = InstallHelper::findInstaller($uniKey);
            if (empty($installer)) {
                $this->error(ErrorCodeService::HELPER_EXCEPTION_ERROR);
            }

            $installInfo = $installer->install();
            $info['installInfo'] = $installInfo;
        }

        LogService::info('install info : ', $info);

        $image = PluginHelper::getPluginImageUrl($pluginConfig);

        $scene = $pluginConfig->sceneArr;
        $input = [
            'unikey' => $uniKey,
            'type' => $type,
            'name' => $pluginConfig->name,
            'image' => $image,
            'description' => $pluginConfig->description,
            'version' => $pluginConfig->currVersion,
            'version_int' => $pluginConfig->currVersionInt,
            'scene' => empty($scene) ? null : json_encode($scene),
            'author' => $pluginConfig->author,
            'author_link' => $pluginConfig->authorLink,
            'access_path' => $pluginConfig->accessPath,
            'setting_path' => $pluginConfig->settingPath,
        ];
        $plugin = FresnsPlugins::where('unikey', $uniKey)->first();
        if (empty($plugin)) {
            $res = (new FresnsPlugins())->store($input);
        } else {
            FresnsPlugins::where('unikey', $uniKey)->update($input);
        }
        $this->success($info);
    }

    // Extensions Enabled or Disabled
    public function enableUnikeyStatus(Request $request)
    {
        $id = $request->input('data_id');
        $is_enable = $request->input('is_enable');
        FresnsPlugins::where('id', $id)->update(['is_enable' => $is_enable]);
        $this->success();
    }

    // Engine Associated Theme Template
    public function websiteLinkSubject(Request $request)
    {
        $websiteUnikey = $request->input('websiteUnikey');
        $subjectUnikeyPc = $request->input('subjectUnikeyPc');
        $subjectUnikeyMobile = $request->input('subjectUnikeyMobile');
        if ($subjectUnikeyPc) {
            $websitePc = ApiConfigHelper::getConfigByItemKey($websiteUnikey.'_Pc');
            if ($websitePc) {
                FresnsConfigs::where('item_key', $websiteUnikey.'_Pc')->update(['item_value' => $subjectUnikeyPc]);
            } else {
                $input = [
                    'item_key' => $websiteUnikey.'_Pc',
                    'item_tag' => 'themes',
                    'item_value' => $subjectUnikeyPc,
                    'item_type' => 'plugin',
                ];
                FresnsConfigs::insert($input);
            }
        } else {
            FresnsConfigs::where('item_key', $websiteUnikey.'_Pc')->delete();
        }
        if ($subjectUnikeyMobile) {
            $websiteMobile = ApiConfigHelper::getConfigByItemKey($websiteUnikey.'_Mobile');
            if ($websiteMobile) {
                FresnsConfigs::where('item_key',
                    $websiteUnikey.'_Mobile')->update(['item_value' => $subjectUnikeyMobile]);
            } else {
                $input = [
                    'item_key' => $websiteUnikey.'_Mobile',
                    'item_tag' => 'themes',
                    'item_value' => $subjectUnikeyMobile,
                    'item_type' => 'plugin',
                ];
                FresnsConfigs::insert($input);
            }
        } else {
            FresnsConfigs::where('item_key', $websiteUnikey.'_Mobile')->delete();
        }
        $this->success();
    }
}
