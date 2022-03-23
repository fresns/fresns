<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Base;

use App\Fresns\Api\Base\Controllers\BaseApiController;
use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\Center\Common\LogService;
use App\Fresns\Api\Center\Common\ValidateService;
use App\Fresns\Api\Center\Helper\CmdRpcHelper;
use App\Fresns\Api\FsCmd\FresnsCmdWords;
use App\Fresns\Api\FsCmd\FresnsCmdWordsConfig;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccounts;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccountsConfig;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigs;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRoles;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRolesService;
use App\Fresns\Api\FsDb\FresnsSessionKeys\FresnsSessionKeys;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRolesService;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\Helpers\StrHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class FsApiController extends BaseApiController
{
    public $platform;
    public $version;
    public $versionInt;
    public $langTag;
    public $appId;
    public $sign;
    public $aid;
    public $uid;
    public $token;

    // Site Mode: Default Private
    public $viewMode = FsConfig::VIEW_MODE_PRIVATE;

    // Check Info: Header and Sign (true or false)
    public $checkHeader = true;
    public $checkSign = true;

    public function __construct()
    {
        $this->checkRequest();
        $this->initData();
        GlobalService::loadData();
    }

    // header data initialization
    public function initData()
    {
        $this->platform = request()->header('platform');
        $this->langTag = request()->header('langTag');
        $this->aid = request()->header('aid');
        $this->uid = request()->header('uid');
    }

    public function checkRequest()
    {
        $uri = Request::getRequestUri();

        if ($this->checkHeader) {
            $this->checkHeaderParams();
        }

        if ($this->checkSign) {
            $this->checkSign();
        }

        $this->checkAccessPerm();

        $this->checkPagination();

        return true;
    }

    public function checkAccessPerm()
    {
        $uri = Request::getRequestUri();
        // Site Mode: public or private
        $siteMode = FresnsConfigs::where('item_key', 'site_mode')->value('item_value');
        $aid = request()->header('aid');
        $uid = request()->header('uid');
        $token = request()->header('token');
        $deviceInfo = request()->header('deviceInfo');
        $platform = request()->header('platform');
        if ($siteMode == 'public') {
            if (empty($aid)) {
                if (in_array($uri, FsConfig::PUBLIC_UID_URI_ARR)) {
                    $info = [
                        'missing header' => 'aid',
                    ];
                    $this->error(ErrorCodeService::AID_REQUIRED_ERROR, $info);
                }
            }

            if (empty($uid)) {
                if (in_array($uri, FsConfig::PUBLIC_MID_URI_ARR)) {
                    $info = [
                        'missing header' => 'uid',
                    ];
                    $this->error(ErrorCodeService::UID_REQUIRED_ERROR, $info);
                }
            }
            if (empty($token)) {
                if (in_array($uri, FsConfig::PUBLIC_TOKEN_URI_ARR)) {
                    $info = [
                        'missing header' => 'token',
                    ];

                    $this->error(ErrorCodeService::TOKEN_REQUIRED_ERROR, $info);
                }
            }
            if (empty($deviceInfo)) {
                if (in_array($uri, FsConfig::PUBLIC_DEVICEINFO_URI_ARR)) {
                    $info = [
                        'missing header' => 'deviceInfo',
                    ];
                    $this->error(ErrorCodeService::DEVICE_INFO_REQUIRED_ERROR, $info);
                }
            }
        } else {
            if (empty($aid)) {
                if (in_array($uri, FsConfig::PRIVATE_UID_URI_ARR)) {
                    $info = [
                        'missing header' => 'aid',
                    ];

                    $this->error(ErrorCodeService::AID_REQUIRED_ERROR, $info);
                }
            }

            if (empty($uid)) {
                if (in_array($uri, FsConfig::PRIVATE_MID_URI_ARR)) {
                    $info = [
                        'missing header' => 'uid',
                    ];

                    $this->error(ErrorCodeService::UID_REQUIRED_ERROR, $info);
                }
            }
            if (empty($token)) {
                if (in_array($uri, FsConfig::PRIVATE_TOKEN_URI_ARR)) {
                    $info = [
                        'missing header' => 'token',
                    ];

                    $this->error(ErrorCodeService::TOKEN_REQUIRED_ERROR, $info);
                }
            }
            if (empty($deviceInfo)) {
                if (in_array($uri, FsConfig::PRIVATE_DEVICEINFO_URI_ARR)) {
                    $info = [
                        'missing header' => 'deviceInfo',
                    ];

                    $this->error(ErrorCodeService::DEVICE_INFO_REQUIRED_ERROR, $info);
                }
            }
        }

        if ($deviceInfo) {
            // Verify if it is json
            $isJson = StrHelper::isJson($deviceInfo);
            if ($isJson === false) {
                $info = [
                    'deviceInfo' => 'Please pass the reference in json format',
                ];
                $this->error(ErrorCodeService::DEVICE_INFO_ERROR, $info);
            }
        }

        $time = date('Y-m-d H:i:s', time());
        // If aid is not empty then token must be passed
        // If uid is not empty, then all three parameters must be passed
        if (empty($uid)) {
            if (! empty($aid)) {
                if (empty($token)) {
                    $info = [
                        'missing header' => 'token',
                    ];

                    $this->error(ErrorCodeService::TOKEN_REQUIRED_ERROR, $info);
                }
                if (in_array($uri, FsConfig::CHECK_ACCOUNT_DELETE_URI)) {
                    $account = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('aid', $aid)->first();
                } else {
                    $account = FresnsAccounts::where('aid', $aid)->first();
                }

                if (empty($account)) {
                    $info = [
                        'null account' => 'aid',
                    ];
                    $this->error(ErrorCodeService::ACCOUNT_CHECK_ERROR, $info);
                }
                // Verify the existence of deleted_at
                if (! empty($account->phone)) {
                    $str = strstr($account->phone, 'deleted');
                    if ($str != false) {
                        $info = [
                            'null account' => 'aid',
                        ];
                        $this->error(ErrorCodeService::ACCOUNT_CHECK_ERROR, $info);
                    }
                }
                if (! empty($account->email)) {
                    $str = strstr($account->phone, 'deleted');
                    if ($str != false) {
                        $info = [
                            'null account' => 'aid',
                        ];
                        $this->error(ErrorCodeService::ACCOUNT_CHECK_ERROR, $info);
                    }
                }
                if ($account->is_enable == 0) {
                    if (! in_array($uri, FsConfig::CHECK_ACCOUNT_IS_ENABLE_URI)) {
                        $this->error(ErrorCodeService::ACCOUNT_IS_ENABLE_ERROR);
                    }
                }
                $accountId = $account->id;

                // Verify token
                $cmd = FresnsCmdWordsConfig::FRESNS_CMD_VERIFY_SESSION_TOKEN;
                $input = [];
                $input['aid'] = request()->header('aid');
                $input['platform'] = request()->header('platform');
                $input['token'] = $token;
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $this->errorCheckInfo($resp);
                }
            }
        } else {
            if (empty($aid) || empty($uid) || empty($token)) {
                $info = [
                    'missing header' => 'aid or uid or token',
                ];

                $this->error(ErrorCodeService::ACCOUNT_VERIFY_ERROR, $info);

                $this->error(ErrorCodeService::HEADER_ERROR, $info);
            }
            // Check if uid belongs to aid
            if (in_array($uri, FsConfig::CHECK_ACCOUNT_DELETE_URI)) {
                $account = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('aid', $aid)->first();
            } else {
                $account = FresnsAccounts::where('aid', $aid)->first();
            }
            if (empty($account)) {
                $info = [
                    'null account' => 'aid',
                ];
                $this->error(ErrorCodeService::ACCOUNT_CHECK_ERROR, $info);
            }
            // Check if the aid is deleted_at
            if (! empty($account->phone)) {
                $str = strstr($account->phone, 'deleted');
                if ($str != false) {
                    $info = [
                        'null account' => 'aid',
                    ];
                    $this->error(ErrorCodeService::ACCOUNT_CHECK_ERROR, $info);
                }
            }
            if (! empty($account->email)) {
                $str = strstr($account->phone, 'deleted');
                if ($str != false) {
                    $info = [
                        'null account' => 'aid',
                    ];
                    $this->error(ErrorCodeService::ACCOUNT_CHECK_ERROR, $info);
                }
            }

            if ($account->is_enable == 0) {
                if (! in_array($uri, FsConfig::CHECK_ACCOUNT_IS_ENABLE_URI)) {
                    $this->error(ErrorCodeService::ACCOUNT_IS_ENABLE_ERROR);
                }
            }

            $accountId = $account->id;
            $user = FresnsUsers::where('uid', $uid)->first();

            if (empty($user)) {
                $info = [
                    'null user' => 'uid',
                ];
                $this->error(ErrorCodeService::USER_CHECK_ERROR, $info);
            }
            if ($user['is_enable'] == 0) {
                $this->error(ErrorCodeService::TOKEN_IS_ENABLE_ERROR);
            }
            $userId = $user['id'];

            $count = FresnsUsers::where('account_id', $accountId)->where('id', $userId)->count();
            if ($count == 0) {
                $this->error(ErrorCodeService::USER_FAIL);
            }

            // Verify token
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_VERIFY_SESSION_TOKEN;
            $input = [];
            $input['aid'] = request()->header('aid');
            $input['platform'] = request()->header('platform');
            $input['uid'] = request()->header('uid');
            $input['token'] = $token;
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                $this->errorCheckInfo($resp);
            }

            // Querying Role Permissions
            if (in_array($uri, FsConfig::NOTICE_CONTENT_URI)) {
                /*
                 * User Master Role Permission
                 * https://fresns.org/api/header.html
                 * user master role permission roles > permission > content_view whether to allow the view, if the view is prohibited, the "content class" and "message class" interfaces cannot be requested.
                 * If the primary role has an expiration time and has expired, then the inherited role permission is primary.
                 * If there is no inherited role (or the inherited ID cannot be found for the role), then the role permissions of the configuration table default_role key name key value prevails.
                 * If the configuration table key value is empty (or the role cannot be found), it is treated as no authority.
                 */
                $roleId = FresnsUserRolesService::getUserRoles($userId);

                if (empty($roleId)) {
                    $this->error(ErrorCodeService::ROLE_NO_PERMISSION);
                }

                $userRole = FresnsRoles::where('id', $roleId)->first();
                if (! empty($userRole)) {
                    $permission = $userRole['permission'];
                    $permissionArr = json_decode($permission, true);
                    if (! empty($permissionArr)) {
                        $permissionMap = FresnsRolesService::getPermissionMap($permissionArr);
                        if (empty($permissionMap)) {
                            $this->error(ErrorCodeService::ROLE_NO_PERMISSION);
                        }
                        if ($permissionMap['content_view'] == false) {
                            $this->error(ErrorCodeService::ROLE_NO_PERMISSION_BROWSE);
                        }
                    }
                }
            }
        }

        return true;
    }

    public function checkPagination()
    {
        $request = request();
        $rule = [
            'pageSize' => 'numeric',
            'page' => 'numeric',
        ];
        ValidateService::validateRule($request, $rule);
    }

    public function checkHeaderParams()
    {
        if ($this->viewMode == FsConfig::VIEW_MODE_PRIVATE) {
            return $this->checkPrivateModeHeaders();
        } else {
            return $this->checkPublicModeHeaders();
        }

        return true;
    }

    // Public mode header checksum
    public function checkPublicModeHeaders()
    {
        return true;
    }

    // Private mode header checksum
    public function checkPrivateModeHeaders()
    {
        $headerFieldArr = FsConfig::HEADER_FIELD_ARR;
        foreach ($headerFieldArr as $headerField) {
            $headerContent = request()->header($headerField);
            if (empty($headerContent)) {
                $info = [
                    'missing header' => $headerField,
                ];

                $this->error(ErrorCodeService::HEADER_ERROR, $info);
            }
        }

        return true;
    }

    /*
     * Verify Signature
     * https://fresns.org/api/header.html
     */
    public function checkSign()
    {
        $appId = request()->header('appId');
        $platform = request()->header('platform');
        $versionInt = request()->header('versionInt');
        if (! is_numeric($platform)) {
            $info = [
                'platform' => 'Please enter an integer',
            ];
            $this->error(ErrorCodeService::HEADER_INFO_ERROR, $info);
        }
        if (! is_numeric($versionInt)) {
            $info = [
                'versionInt' => 'Please enter an integer',
            ];
            $this->error(ErrorCodeService::HEADER_INFO_ERROR, $info);
        }

        /*
         * Verify the appId and platform parameters
         * https://fresns.org/api/header.html
         *
         * Does session_keys > app_id exist
         * Does it match session_keys > platform_id
         * Whether session_keys > is_enable
         */
        $sessionKeys = FresnsSessionKeys::where('app_id', $appId)->first();
        if (empty($sessionKeys)) {
            $info = [
                'appId' => 'App ID does not exist',
            ];

            $this->error(ErrorCodeService::HEADER_APP_ID_ERROR, $info);
        }
        if ($sessionKeys['platform_id'] != $platform) {
            $info = [
                'platform' => 'Platform ID does not exist',
            ];

            $this->error(ErrorCodeService::HEADER_PLATFORM_ERROR, $info);
        }
        if ($sessionKeys['is_enable'] == 0) {
            $this->error(ErrorCodeService::TOKEN_IS_ENABLE_ERROR);
        }
        if ($sessionKeys['type'] == 2) {
            $this->error(ErrorCodeService::HEADER_KEY_ERROR);
        }
        $signKey = $sessionKeys['app_secret'];
        $dataMap = [];
        foreach (FsConfig::SIGN_FIELD_ARR as $signField) {
            $signFieldValue = request()->header($signField);
            if (! empty($signFieldValue)) {
                $dataMap[$signField] = $signFieldValue;
            }
        }

        $dataMap['sign'] = request()->header('sign');
        LogService::info('Verify Info: ', $dataMap);

        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_VERIFY_SIGN;
        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $dataMap);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            $this->errorCheckInfo($resp, [], $resp['output']);
        }

        return true;
    }
}
