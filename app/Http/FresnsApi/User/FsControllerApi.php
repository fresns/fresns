<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\User;

use App\Helpers\DateHelper;
use App\Helpers\StrHelper;
use App\Http\Center\Common\ErrorCodeService;
use App\Http\Center\Common\GlobalService;
use App\Http\Center\Common\ValidateService;
use App\Http\Center\Helper\CmdRpcHelper;
use App\Http\FresnsApi\Base\FresnsBaseApiController;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsCmd\FresnsCmdWords;
use App\Http\FresnsCmd\FresnsCmdWordsConfig;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigs;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsMembers\FresnsMembersConfig;
use App\Http\FresnsDb\FresnsMemberStats\FresnsMemberStats;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogs;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogsConfig;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogsService;
use App\Http\FresnsDb\FresnsSessionTokens\FresnsSessionTokensConfig;
use App\Http\FresnsDb\FresnsUserConnects\FresnsUserConnects;
use App\Http\FresnsDb\FresnsUserConnects\FresnsUserConnectsConfig;
use App\Http\FresnsDb\FresnsUsers\FresnsUsers;
use App\Http\FresnsDb\FresnsUsers\FresnsUsersConfig;
use App\Http\FresnsDb\FresnsUsers\FresnsUsersService;
use App\Http\FresnsDb\FresnsUserWalletLogs\FresnsUserWalletLogsService;
use App\Http\FresnsDb\FresnsUserWallets\FresnsUserWallets;
use App\Http\FresnsDb\FresnsVerifyCodes\FresnsVerifyCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FsControllerApi extends FresnsBaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->service = new FsService();
        $this->initData();
    }

    // User Register
    public function register(Request $request)
    {
        $rule = [
            'type' => 'required|numeric|in:1,2',
            // 'account' => 'required',
            'nickname' => 'required',
        ];
        // Verify Parameters
        $type = $request->input('type');
        switch ($type) {
            case 1:
                $rule = [
                    'type' => 'required|numeric|in:1,2',
                    'account' => 'required|email',
                    'nickname' => 'required',
                ];
                break;
            case 2:
                $rule = [
                    'type' => 'required|numeric|in:1,2',
                    'account' => 'required|numeric',
                    'nickname' => 'required',
                    'countryCode' => 'required|numeric',
                ];
                break;
            case 3:
                break;
        }
        ValidateService::validateRule($request, $rule);

        $account = $request->input('account');
        $countryCode = $request->input('countryCode');
        $verifyCode = $request->input('verifyCode');
        $connectInfo = $request->input('connectInfo');
        $password = $request->input('password');
        $nickname = $request->input('nickname');
        $avatarFid = $request->input('avatarFid');
        $avatarUrl = $request->input('avatarUrl');
        $gender = $request->input('gender');
        $birthday = $request->input('birthday');
        $timezone = $request->input('timezone');
        $language = $request->input('language');

        $siteMode = ApiConfigHelper::getConfigByItemKey('site_mode');
        if ($siteMode == 'private') {
            $this->error(ErrorCodeService::PRIVATE_MODE_ERROR);
        }

        $sitePublicClose = ApiConfigHelper::getConfigByItemKey('site_public_close');
        if ($sitePublicClose === false) {
            $this->error(ErrorCodeService::PRIVATE_MODE_ERROR);
        }
        $sitePublicService = ApiConfigHelper::getConfigByItemKey('site_public_service');
        if (! empty($sitePublicService)) {
            $this->error(ErrorCodeService::PRIVATE_MODE_ERROR);
        }
        if ($type == 1) {
            $codeAccount = $account;
            $siteRegisterEmail = ApiConfigHelper::getConfigByItemKey('site_register_email');
            if ($siteRegisterEmail === false) {
                $this->error(ErrorCodeService::REGISTER_EMAIL_ERROR);
            }
        }
        if ($type == 2) {
            $codeAccount = $countryCode.$account;
            $siteRegisterPhone = ApiConfigHelper::getConfigByItemKey('site_register_phone');
            if ($siteRegisterPhone === false) {
                $this->error(ErrorCodeService::REGISTER_PHONE_ERROR);
            }
        }

        // Verify Password
        if ($password) {
            $password = base64_decode($password, true);

            $passwordLength = ApiConfigHelper::getConfigByItemKey('password_length');
            if ($passwordLength > 0) {
                if ($passwordLength > strlen($password)) {
                    $this->error(ErrorCodeService::PASSWORD_LENGTH_ERROR);
                }
            }
            $passwordStrength = ApiConfigHelper::getConfigByItemKey('password_strength');

            // Verify Password Rules
            if (! empty($passwordStrength)) {
                $passwordStrengthArr = explode(',', $passwordStrength);

                if (in_array(FsConfig::PASSWORD_NUMBER, $passwordStrengthArr)) {
                    $isError = preg_match('/\d/is', $password);
                    if ($isError == 0) {
                        $this->error(ErrorCodeService::PASSWORD_NUMBER_ERROR);
                    }
                }
                if (in_array(FsConfig::PASSWORD_LOWERCASE_LETTERS, $passwordStrengthArr)) {
                    $isError = preg_match('/[a-z]/', $password);
                    if ($isError == 0) {
                        $this->error(ErrorCodeService::PASSWORD_LOWERCASE_ERROR);
                    }
                }
                if (in_array(FsConfig::PASSWORD_CAPITAL_LETTERS, $passwordStrengthArr)) {
                    $isError = preg_match('/[A-Z]/', $password);
                    if ($isError == 0) {
                        $this->error(ErrorCodeService::PASSWORD_CAPITAL_ERROR);
                    }
                }
                if (in_array(FsConfig::PASSWORD_SYMBOL, $passwordStrengthArr)) {
                    $isError = preg_match('/^[A-Za-z0-9]+$/', $password);
                    if ($isError == 1) {
                        $this->error(ErrorCodeService::PASSWORD_SYMBOL_ERROR);
                    }
                }
            }
        }

        $time = date('Y-m-d H:i:s', time());
        $verifyCodeArr = FresnsVerifyCodes::where('type', $type)->where('account', $codeAccount)->where('expired_at', '>', $time)->pluck('code')->toArray();
        if (! in_array($verifyCode, $verifyCodeArr)) {
            $this->error(ErrorCodeService::VERIFY_CODE_CHECK_ERROR);
        }

        // Check if a user has registered
        switch ($type) {
            case 1:
                $count = FresnsUsers::where('email', $account)->count();
                if ($count > 0) {
                    $this->error(ErrorCodeService::REGISTER_USER_ERROR);
                }
                break;
            case 2:
                $count = FresnsUsers::where('pure_phone', $account)->count();
                if ($count > 0) {
                    $this->error(ErrorCodeService::REGISTER_USER_ERROR);
                }
                break;
            default:

                break;
        }

        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_USER_REGISTER;
        $input = [
            'type' => $type,
            'account' => $account,
            'countryCode' => $countryCode,
            'connectInfo' => $connectInfo,
            'password' => $password,
            'nickname' => $nickname,
            'avatarFid' => $avatarFid,
            'avatarUrl' => $avatarUrl,
            'gender' => $gender,
            'birthday' => $birthday,
            'timezone' => $timezone,
            'language' => $language,
        ];
        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            return $this->pluginError($resp);
        }
        $data = $resp['output'];
        if ($data) {
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_CREATE_SESSION_TOKEN;
            $input['uid'] = $data['uid'];
            $input['platform'] = $request->header('platform');
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                $this->errorCheckInfo($resp);
            }

            $output = $resp['output'];
            $data['token'] = $output['token'] ?? '';
            $data['tokenExpiredTime'] = $output['tokenExpiredTime'] ?? '';
        }

        $this->success($data);
    }

    // User Login
    public function login(Request $request)
    {
        // Verify Parameters
        $rule = [
            'type' => 'required|numeric|in:1,2,3',
            'account' => 'required',
        ];

        $type = $request->input('type');
        $account = $request->input('account');
        $countryCode = $request->input('countryCode');
        $verifyCode = $request->input('verifyCode');
        $passwordBase64 = $request->input('password');

        if ($passwordBase64) {
            $password = base64_decode($passwordBase64, true);
            if ($password == false) {
                $password = $passwordBase64;
            }
        } else {
            $password = null;
        }

        switch ($type) {
            case 1:
                $rule = [
                    'type' => 'required|numeric|in:1,2,3',
                    'account' => 'required|email',
                ];
                $user = DB::table(FresnsUsersConfig::CFG_TABLE)->where('email', $account)->first();
                break;
            case 2:
                $rule = [
                    'type' => 'required|numeric|in:1,2,3',
                    'account' => 'required|numeric|regex:/^1[^0-2]\d{9}$/',
                    'countryCode' => 'required|numeric',
                ];
                $user = DB::table(FresnsUsersConfig::CFG_TABLE)->where('phone', $countryCode.$account)->first();
                break;
            default:
                // code...
                break;
        }

        ValidateService::validateRule($request, $rule);

        if (empty($user)) {
            $this->error(ErrorCodeService::USER_CHECK_ERROR);
        }

        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_USER_LOGIN;
        $input = [
            'type' => $type,
            'account' => $account,
            'countryCode' => $countryCode,
            'password' => $passwordBase64,
            'verifyCode' => $verifyCode,
        ];
        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            return $this->errorCheckInfo($resp);
        }

        $data = $resp['output'];
        if ($data) {
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_CREATE_SESSION_TOKEN;
            $input['uid'] = $user->uuid;
            $input['platform'] = $request->header('platform');
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                $this->errorCheckInfo($resp);
            }
            $output = $resp['output'];
            $data['token'] = $output['token'] ?? '';
            $data['tokenExpiredTime'] = $output['tokenExpiredTime'] ?? '';
        }

        $this->success($data);
    }

    // User Logout
    public function logout(Request $request)
    {
        $uid = GlobalService::getGlobalKey('user_id');
        $mid = GlobalService::getGlobalKey('member_id');

        DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('user_id', $uid)->where('member_id', null)->delete();
        DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('member_id', $mid)->delete();
        $this->success();
    }

    // Delete User
    public function delete(Request $request)
    {
        $uid = GlobalService::getGlobalKey('user_id');

        $user = FresnsUsers::where('id', $uid)->first();
        if (empty($user)) {
            $this->error(ErrorCodeService::MEMBER_CHECK_ERROR);
        }

        FresnsUsers::where('id', $user['id'])->delete();
        FresnsMembers::where('user_id', $user['id'])->delete();

        // Return config parameter
        $itemValue = ApiConfigHelper::getConfigByItemKey('delete_account_todo');

        $data['days'] = $itemValue ?? 0;

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $user['id'], null, $user['id']);
        }

        DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('user_id', $uid)->where('member_id', null)->delete();

        $this->success($data);
    }

    // Restore User
    public function restore(Request $request)
    {
        $uid = $request->header('uid');

        $user = FresnsUsers::where('uuid', $uid)->first();
        if ($user) {
            $this->error(ErrorCodeService::MEMBER_CHECK_ERROR);
        }

        $user = DB::table(FresnsUsersConfig::CFG_TABLE)->where('uuid', $uid)->first();

        if (empty($user)) {
            $this->error(ErrorCodeService::MEMBER_CHECK_ERROR);
        }

        $input['deleted_at'] = null;

        $memberInput = [
            'deleted_at' => null,
        ];

        DB::table(FresnsUsersConfig::CFG_TABLE)->where('id', $user->id)->update($input);
        DB::table(FresnsMembersConfig::CFG_TABLE)->where('user_id', $user->id)->update($memberInput);

        $langTag = $this->langTag;
        $service = new FresnsUsersService();
        $data = $service->getUserDetail($user->id, $langTag);
        $this->success($data);
    }

    // Reset Password
    public function reset(Request $request)
    {
        // Verify Parameters
        $rule = [
            'type' => 'required|numeric|in:1,2',
            'account' => 'required',
            'verifyCode' => 'required',
            'newPassword' => 'required',
        ];

        $type = $request->input('type');
        $account = $request->input('account');
        $verifyCode = $request->input('verifyCode');
        $newPassword = $request->input('newPassword');
        $countryCode = $request->input('countryCode');

        switch ($type) {
            case 1:
                $rule = [
                    'type' => 'required|numeric|in:1,2',
                    'account' => 'required|email',
                    'newPassword' => 'required',
                    'verifyCode' => 'required',
                ];
                break;
            case 2:
                $rule = [
                    'type' => 'required|numeric|in:1,2',
                    'account' => 'required|numeric|regex:/^1[^0-2]\d{9}$/',
                    'countryCode' => 'required|numeric',
                    'newPassword' => 'required',
                    'verifyCode' => 'required',
                ];
                break;
            default:
                // code...
                break;
        }

        ValidateService::validateRule($request, $rule);

        $time = date('Y-m-d H:i:s', time());
        switch ($type) {
            case 1:
                $verifyCodeArr = FresnsVerifyCodes::where('type', $type)->where('account', $account)->where('expired_at', '>', $time)->pluck('code')->toArray();
                break;
            case 2:
                $verifyCodeArr = FresnsVerifyCodes::where('type', $type)->where('account', $countryCode.$account)->where('expired_at', '>', $time)->pluck('code')->toArray();
                break;
            default:
                // code...
                break;
        }

        if (! in_array($verifyCode, $verifyCodeArr)) {
            $this->error(ErrorCodeService::VERIFY_CODE_CHECK_ERROR);
        }

        switch ($type) {
            case 1:
                $user = FresnsUsers::where('email', $account)->first();
                break;

            default:
                $user = FresnsUsers::where('pure_phone', $account)->first();
                break;
        }

        if (empty($user)) {
            $this->error(ErrorCodeService::USER_CHECK_ERROR);
        }
        $newPassword = base64_decode($newPassword, true);
        $password = str_replace(' ', '', $newPassword);
        $passwordLength = ApiConfigHelper::getConfigByItemKey('password_length');
        if ($passwordLength > 0) {
            if ($passwordLength > strlen($password)) {
                $this->error(ErrorCodeService::PASSWORD_LENGTH_ERROR);
            }
        }
        $passwordStrength = ApiConfigHelper::getConfigByItemKey('password_strength');

        // Verify Password Rules
        if (! empty($passwordStrength)) {
            $passwordStrengthArr = explode(',', $passwordStrength);

            if (in_array(FsConfig::PASSWORD_NUMBER, $passwordStrengthArr)) {
                $isError = preg_match('/\d/is', $password);
                if ($isError == 0) {
                    $this->error(ErrorCodeService::PASSWORD_NUMBER_ERROR);
                }
            }
            if (in_array(FsConfig::PASSWORD_LOWERCASE_LETTERS, $passwordStrengthArr)) {
                $isError = preg_match('/[a-z]/', $password);
                if ($isError == 0) {
                    $this->error(ErrorCodeService::PASSWORD_LOWERCASE_ERROR);
                }
            }
            if (in_array(FsConfig::PASSWORD_CAPITAL_LETTERS, $passwordStrengthArr)) {
                $isError = preg_match('/[A-Z]/', $password);
                if ($isError == 0) {
                    $this->error(ErrorCodeService::PASSWORD_CAPITAL_ERROR);
                }
            }
            if (in_array(FsConfig::PASSWORD_SYMBOL, $passwordStrengthArr)) {
                $isError = preg_match('/^[A-Za-z0-9]+$/', $password);
                if ($isError == 1) {
                    $this->error(ErrorCodeService::PASSWORD_SYMBOL_ERROR);
                }
            }
        }

        $input = [
            'password' => StrHelper::createPassword($newPassword),
        ];

        FresnsUsers::where('id', $user['id'])->update($input);

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $user['id'], null, $user['id']);
        }

        $this->success();
    }

    // User Detail
    public function detail(Request $request)
    {
        $uid = $request->header('uid');
        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_USER_DETAIL;
        $input = [
            'uid' => $uid,
        ];
        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            return $this->pluginError($resp);
        }
        $data = $resp['output'];
        $this->success($data);
    }

    public function walletLogs(Request $request)
    {
        // Verify Parameters
        $rule = [
            'type' => 'numeric',
            'status' => 'in:1,0',
            'pageSize' => 'numeric',
            'page' => 'numeric',
        ];
        ValidateService::validateRule($request, $rule);
        $currentPage = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 30);
        if ($currentPage < 0 || $pageSize < 0) {
            $this->error(ErrorCodeService::CODE_PARAM_ERROR);
        }

        $uid = GlobalService::getGlobalKey('user_id');

        $langTag = ApiLanguageHelper::getLangTagByHeader();

        $fresnsUserWalletLogsService = new FresnsUserWalletLogsService();

        $request->offsetSet('currentPage', $currentPage);
        $request->offsetSet('pageSize', $pageSize);
        $request->offsetSet('user_id', $uid);
        $request->offsetSet('langTag', $langTag);

        $fresnsUserWalletLogsService->setResource(FresnsWalletLogsResource::class);
        $data = $fresnsUserWalletLogsService->searchData();

        $this->success($data);
    }

    // User Verification
    public function verification(Request $request)
    {
        $rule = [
            'codeType' => 'numeric|in:1,2',
            'verifyCode' => 'required',
        ];
        ValidateService::validateRule($request, $rule);
        $codeType = $request->input('codeType');
        $verifyCode = $request->input('verifyCode');

        $user_id = GlobalService::getGlobalKey('user_id');
        $userInfo = FresnsUsers::find($user_id);
        if (empty($userInfo)) {
            $this->error(ErrorCodeService::USER_CHECK_ERROR);
        }
        if ($codeType == 1) {
            $account = $userInfo['email'];
        } else {
            $account = $userInfo['pure_phone'];
            $countryCode = $userInfo['country_code'];
        }

        // Check the verify code, but do not modify the verify code status(is_enable)
        $time = date('Y-m-d H:i:s', time());
        switch ($codeType) {
            case 1:
                $verifyCodeArr = FresnsVerifyCodes::where('type', $codeType)->where('account', $account)->where('expired_at', '>', $time)->pluck('code')->toArray();
                break;
            case 2:
                $verifyCodeArr = FresnsVerifyCodes::where('type', $codeType)->where('account', $countryCode.$account)->where('expired_at', '>', $time)->pluck('code')->toArray();
                break;
            default:
                // code...
                break;
        }

        if (! in_array($verifyCode, $verifyCodeArr)) {
            $this->error(ErrorCodeService::VERIFY_CODE_CHECK_ERROR);
        }

        $this->success();
    }

    // Edit User Info
    public function edit(Request $request)
    {
        // Verify Parameters
        $rule = [
            'codeType' => 'numeric|in:1,2',
            'editCountryCode' => 'numeric',
        ];
        ValidateService::validateRule($request, $rule);
        $uid = GlobalService::getGlobalKey('user_id');

        $codeType = $request->input('codeType');
        $verifyCode = $request->input('verifyCode');
        $editEmail = $request->input('editEmail');
        $editPhone = $request->input('editPhone');
        $editCountryCode = $request->input('editCountryCode');
        $newVerifyCode = $request->input('newVerifyCode');
        $password = $request->input('password');
        $editPassword = $request->input('editPassword');
        $walletPassword = $request->input('walletPassword');
        $editWalletPassword = $request->input('editWalletPassword');
        $deleteConnectId = $request->input('deleteConnectId');
        $editLastLoginTime = $request->input('editLastLoginTime');

        if ($password) {
            $password = base64_decode($password, true);
        }
        if ($walletPassword) {
            $walletPassword = base64_decode($walletPassword, true);
        }
        if ($editPassword) {
            $editPassword = base64_decode($editPassword, true);
        }
        if ($editWalletPassword) {
            $editWalletPassword = base64_decode($editWalletPassword, true);
        }
        $user = FresnsUsers::where('id', $uid)->first();

        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_CHECK_CODE;

        if ($codeType == 1) {
            $verify_input = [
                'type' => 1,
                'account' => $user['email'],
                'countryCode' => null,
                'verifyCode' => $verifyCode
            ];
            $verify_input_new = [
                'type' => 1,
                'account' => $editEmail,
                'countryCode' => null,
                'verifyCode' => $newVerifyCode
            ];
        } else {
            $verify_input = [
                'type' => 2,
                'account' => $user['pure_phone'],
                'countryCode' => $user['country_code'],
                'verifyCode' => $verifyCode
            ];
            $verify_input_new = [
                'type' => 2,
                'account' => $editPhone,
                'countryCode' => $editCountryCode,
                'verifyCode' => $newVerifyCode
            ];
        }

        if($editEmail){
            // Verify Parameters
            $rule = [
                'newVerifyCode' => 'required',
            ];
            ValidateService::validateRule($request, $rule);
            if($user['email']){
                // verify old email
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $verify_input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $this->errorCheckInfo($resp);
                }
            }
            // verify new email
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $verify_input_new);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                $this->errorCheckInfo($resp);
            }
            //update userinfo
            FresnsUsers::where('id', $user['id'])->update(['email' => $editEmail]);
        }

        if($editPhone){
            // Verify Parameters
            $rule = [
                'newVerifyCode' => 'required',
                'editCountryCode' => 'required|numeric',
            ];
            ValidateService::validateRule($request, $rule);
            if ($user['phone']) {
                // verify old phone
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $verify_input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $this->errorCheckInfo($resp);
                }
            }
            //verify new phone
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $verify_input_new);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                $this->errorCheckInfo($resp);
            }
            //update userinfo
            $input = [
                'country_code' => $editCountryCode,
                'pure_phone' => $editPhone,
                'phone' => $editCountryCode.$editPhone,
            ];
            FresnsUsers::where('id', $user['id'])->update($input);
        }

        if ($editPassword) {
            if (!empty($password)) {
                //password check type
                if (! Hash::check($password, $user['password'])) {
                    $this->error(ErrorCodeService::ACCOUNT_PASSWORD_INVALID);
                }
            }elseif ($codeType && $verifyCode){
                //verify code check type
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $verify_input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $this->errorCheckInfo($resp);
                }
            }
            FresnsUsers::where('id', $user['id'])->update(['password' => bcrypt($editPassword)]);
        }

        if ($editWalletPassword) {
            $wallet = FresnsUserWallets::where('user_id', $user['id'])->first();
            if (empty($wallet)) {
                $this->error(ErrorCodeService::MEMBER_CHECK_ERROR);
            }
            if(!empty($walletPassword)){
                //password check type
                if (! Hash::check($walletPassword, $wallet['password'])) {
                    $this->error(ErrorCodeService::ACCOUNT_PASSWORD_INVALID);
                }
            }elseif ($codeType && $verifyCode){
                //verify code check type
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $verify_input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $this->errorCheckInfo($resp);
                }
            }
            FresnsUserWallets::where('id', $wallet['id'])->update(['password' => bcrypt($editWalletPassword)]);
        }

        if ($editLastLoginTime) {
            $rule = [
                'editLastLoginTime' => 'date_format:Y-m-d H:i:s',
            ];
            ValidateService::validateRule($request, $rule);
            FresnsUsers::where('id', $user['id'])->update(['last_login_at' => DateHelper::fresnsInputTimeToTimezone($editLastLoginTime)]);
        }

        if ($deleteConnectId) {
            DB::table(FresnsUserConnectsConfig::CFG_TABLE)->where('user_id', $user['id'])->where('connect_id', $deleteConnectId)->delete();
        }

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $user['id'], null, $user['id']);
        }

        $this->success();
    }
}
