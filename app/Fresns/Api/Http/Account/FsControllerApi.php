<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Account;

use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\Center\Common\ValidateService;
use App\Fresns\Api\Center\Helper\CmdRpcHelper;
use App\Fresns\Api\FsCmd\FresnsCmdWords;
use App\Fresns\Api\FsCmd\FresnsCmdWordsConfig;
use App\Fresns\Api\FsDb\FresnsAccountConnects\FresnsAccountConnectsConfig;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccounts;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccountsConfig;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccountsService;
use App\Fresns\Api\FsDb\FresnsAccountWalletLogs\FresnsAccountWalletLogsService;
use App\Fresns\Api\FsDb\FresnsAccountWallets\FresnsAccountWallets;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogsService;
use App\Fresns\Api\FsDb\FresnsSessionTokens\FresnsSessionTokensConfig;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use App\Fresns\Api\FsDb\FresnsVerifyCodes\FresnsVerifyCodes;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\ApiLanguageHelper;
use App\Fresns\Api\Helpers\DateHelper;
use App\Fresns\Api\Helpers\StrHelper;
use App\Fresns\Api\Http\Base\FsApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FsControllerApi extends FsApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->service = new FsService();
        $this->initData();
    }

    // Account Register
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
            $this->error(ErrorCodeService::REGISTER_ERROR);
        }
        $sitePublicService = ApiConfigHelper::getConfigByItemKey('site_public_service');
        if (! empty($sitePublicService)) {
            $this->error(ErrorCodeService::REGISTER_ERROR);
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

        // Check if a account has registered
        switch ($type) {
            case 1:
                $count = FresnsAccounts::where('email', $account)->count();
                if ($count > 0) {
                    $this->error(ErrorCodeService::REGISTER_ACCOUNT_ERROR);
                }
                break;
            case 2:
                $count = FresnsAccounts::where('pure_phone', $account)->count();
                if ($count > 0) {
                    $this->error(ErrorCodeService::REGISTER_ACCOUNT_ERROR);
                }
                break;
            default:

                break;
        }

        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ACCOUNT_REGISTER;
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
            $input['aid'] = $data['aid'];
            $input['platform'] = $request->header('platform');
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                $this->errorCheckInfo($resp);
            }

            $output = $resp['output'];
            $data['token'] = $output['token'] ?? null;
            $data['tokenExpiredTime'] = $output['tokenExpiredTime'] ?? null;
        }

        $this->success($data);
    }

    // Account Login
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
                $account = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('email', $account)->first();
                break;
            case 2:
                $rule = [
                    'type' => 'required|numeric|in:1,2,3',
                    'account' => 'required|numeric|regex:/^1[^0-2]\d{9}$/',
                    'countryCode' => 'required|numeric',
                ];
                $account = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('phone', $countryCode.$account)->first();
                break;
            default:
                // code...
                break;
        }

        ValidateService::validateRule($request, $rule);

        if (empty($account)) {
            $this->error(ErrorCodeService::ACCOUNT_CHECK_ERROR);
        }

        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ACCOUNT_LOGIN;
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
            $input['aid'] = $account->aid;
            $input['platform'] = $request->header('platform');
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                $this->errorCheckInfo($resp);
            }
            $output = $resp['output'];
            $data['token'] = $output['token'] ?? null;
            $data['tokenExpiredTime'] = $output['tokenExpiredTime'] ?? null;
        }

        $this->success($data);
    }

    // Account Logout
    public function logout(Request $request)
    {
        $aid = GlobalService::getGlobalKey('account_id');
        $uid = GlobalService::getGlobalKey('user_id');

        DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('account_id', $aid)->where('user_id', null)->delete();
        DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('user_id', $uid)->delete();
        $this->success();
    }

    // Delete Account
    public function delete(Request $request)
    {
        $aid = GlobalService::getGlobalKey('account_id');

        $account = FresnsAccounts::where('id', $aid)->first();
        if (empty($account)) {
            $this->error(ErrorCodeService::USER_CHECK_ERROR);
        }

        FresnsAccounts::where('id', $account['id'])->delete();
        FresnsUsers::where('account_id', $account['id'])->delete();

        // Return config parameter
        $itemValue = ApiConfigHelper::getConfigByItemKey('delete_account_todo');

        $data['days'] = $itemValue ?? 0;

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $account['id'], null, $account['id']);
        }

        DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('account_id', $aid)->where('user_id', null)->delete();

        $this->success($data);
    }

    // Restore Account
    public function restore(Request $request)
    {
        $aid = $request->header('aid');

        $account = FresnsAccounts::where('aid', $aid)->first();
        if ($account) {
            $this->error(ErrorCodeService::USER_CHECK_ERROR);
        }

        $account = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('aid', $aid)->first();

        if (empty($account)) {
            $this->error(ErrorCodeService::USER_CHECK_ERROR);
        }

        $input['deleted_at'] = null;

        $userInput = [
            'deleted_at' => null,
        ];

        DB::table(FresnsAccountsConfig::CFG_TABLE)->where('id', $account->id)->update($input);
        DB::table(FresnsUsersConfig::CFG_TABLE)->where('account_id', $account->id)->update($userInput);

        $langTag = $this->langTag;
        $service = new FresnsAccountsService();
        $data = $service->getAccountDetail($account->id, $langTag);
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
                $account = FresnsAccounts::where('email', $account)->first();
                break;

            default:
                $account = FresnsAccounts::where('pure_phone', $account)->first();
                break;
        }

        if (empty($account)) {
            $this->error(ErrorCodeService::ACCOUNT_CHECK_ERROR);
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

        FresnsAccounts::where('id', $account['id'])->update($input);

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $account['id'], null, $account['id']);
        }

        $this->success();
    }

    // Account Detail
    public function detail(Request $request)
    {
        $aid = $request->header('aid');
        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ACCOUNT_DETAIL;
        $input = [
            'aid' => $aid,
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

        $aid = GlobalService::getGlobalKey('account_id');

        $langTag = ApiLanguageHelper::getLangTagByHeader();

        $fresnsAccountWalletLogsService = new FresnsAccountWalletLogsService();

        $request->offsetSet('currentPage', $currentPage);
        $request->offsetSet('pageSize', $pageSize);
        $request->offsetSet('account_id', $aid);
        $request->offsetSet('langTag', $langTag);

        $fresnsAccountWalletLogsService->setResource(FresnsWalletLogsResource::class);
        $data = $fresnsAccountWalletLogsService->searchData();

        $this->success($data);
    }

    // Account Verification
    public function verification(Request $request)
    {
        $rule = [
            'codeType' => 'numeric|in:1,2',
            'verifyCode' => 'required',
        ];
        ValidateService::validateRule($request, $rule);
        $codeType = $request->input('codeType');
        $verifyCode = $request->input('verifyCode');

        $account_id = GlobalService::getGlobalKey('account_id');
        $accountInfo = FresnsAccounts::find($account_id);
        if (empty($accountInfo)) {
            $this->error(ErrorCodeService::ACCOUNT_CHECK_ERROR);
        }
        if ($codeType == 1) {
            $account = $accountInfo['email'];
        } else {
            $account = $accountInfo['pure_phone'];
            $countryCode = $accountInfo['country_code'];
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

    // Edit Account Info
    public function edit(Request $request)
    {
        // Verify Parameters
        $rule = [
            'codeType' => 'numeric|in:1,2',
            'editCountryCode' => 'numeric',
        ];
        ValidateService::validateRule($request, $rule);
        $aid = GlobalService::getGlobalKey('account_id');

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
        $account = FresnsAccounts::where('id', $aid)->first();

        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_CHECK_CODE;

        if ($codeType == 1) {
            $verify_input = [
                'type' => 1,
                'account' => $account['email'],
                'countryCode' => null,
                'verifyCode' => $verifyCode,
            ];
            $verify_input_new = [
                'type' => 1,
                'account' => $editEmail,
                'countryCode' => null,
                'verifyCode' => $newVerifyCode,
            ];
        } else {
            $verify_input = [
                'type' => 2,
                'account' => $account['pure_phone'],
                'countryCode' => $account['country_code'],
                'verifyCode' => $verifyCode,
            ];
            $verify_input_new = [
                'type' => 2,
                'account' => $editPhone,
                'countryCode' => $editCountryCode,
                'verifyCode' => $newVerifyCode,
            ];
        }

        if ($editEmail) {
            // Verify Parameters
            $rule = [
                'newVerifyCode' => 'required',
            ];
            ValidateService::validateRule($request, $rule);
            if ($account['email']) {
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
            // check new email
            $countAccount = FresnsAccounts::where('email', $editEmail)->count();
            if ($countAccount) {
                $this->error(ErrorCodeService::EMAIL_ERROR);
            }
            // update accountinfo
            FresnsAccounts::where('id', $account['id'])->update(['email' => $editEmail]);
        }

        if ($editPhone) {
            // Verify Parameters
            $rule = [
                'newVerifyCode' => 'required',
                'editCountryCode' => 'required|numeric',
            ];
            ValidateService::validateRule($request, $rule);
            if ($account['phone']) {
                // verify old phone
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $verify_input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $this->errorCheckInfo($resp);
                }
            }
            // verify new phone
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $verify_input_new);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                $this->errorCheckInfo($resp);
            }
            // check new phone
            $countAccount = FresnsAccounts::where('phone', $editCountryCode.$editPhone)->count();
            if ($countAccount) {
                $this->error(ErrorCodeService::PHONE_ERROR);
            }

            //update accountinfo
            $input = [
                'country_code' => $editCountryCode,
                'pure_phone' => $editPhone,
                'phone' => $editCountryCode.$editPhone,
            ];
            FresnsAccounts::where('id', $account['id'])->update($input);
        }

        if ($editPassword) {
            if (! empty($password)) {
                //password check type
                if (! Hash::check($password, $account['password'])) {
                    $this->error(ErrorCodeService::ACCOUNT_PASSWORD_INVALID);
                }
            } elseif ($codeType && $verifyCode) {
                //verify code check type
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $verify_input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $this->errorCheckInfo($resp);
                }
            }
            FresnsAccounts::where('id', $account['id'])->update(['password' => bcrypt($editPassword)]);
        }

        if ($editWalletPassword) {
            $wallet = FresnsAccountWallets::where('account_id', $account['id'])->first();
            if (empty($wallet)) {
                $this->error(ErrorCodeService::USER_CHECK_ERROR);
            }
            if (! empty($walletPassword)) {
                //password check type
                if (! Hash::check($walletPassword, $wallet['password'])) {
                    $this->error(ErrorCodeService::ACCOUNT_PASSWORD_INVALID);
                }
            } elseif ($codeType && $verifyCode) {
                //verify code check type
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $verify_input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $this->errorCheckInfo($resp);
                }
            }
            FresnsAccountWallets::where('id', $wallet['id'])->update(['password' => bcrypt($editWalletPassword)]);
        }

        if ($editLastLoginTime) {
            $rule = [
                'editLastLoginTime' => 'date_format:Y-m-d H:i:s',
            ];
            ValidateService::validateRule($request, $rule);
            FresnsAccounts::where('id', $account['id'])->update(['last_login_at' => DateHelper::fresnsInputTimeToTimezone($editLastLoginTime)]);
        }

        if ($deleteConnectId) {
            DB::table(FresnsAccountConnectsConfig::CFG_TABLE)->where('account_id', $account['id'])->where('connect_id', $deleteConnectId)->delete();
        }

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $account['id'], null, $account['id']);
        }

        $this->success();
    }
}
