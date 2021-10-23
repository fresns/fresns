<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Base;

use App\Base\Controllers\BaseApiController;
use App\Helpers\SignHelper;
use App\Helpers\StrHelper;
use App\Http\Center\Common\ErrorCodeService;
use App\Http\Center\Common\LogService;
use App\Http\Center\Common\ValidateService;
use App\Http\Center\Helper\CmdRpcHelper;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsCmd\FresnsCmdWords;
use App\Http\FresnsCmd\FresnsCmdWordsConfig;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigs;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRelsService;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRolesService;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsSessionKeys\FresnsSessionKeys;
use App\Http\FresnsDb\FresnsSessionTokens\FresnsSessionTokensConfig;
use App\Http\FresnsDb\FresnsUsers\FresnsUsers;
use App\Http\FresnsDb\FresnsUsers\FresnsUsersConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class FresnsBaseApiController extends BaseApiController
{
    public $platform;
    public $version;
    public $versionInt;
    public $langTag;
    public $appId;
    public $sign;
    public $uid;
    public $mid;
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
    }

    // header data initialization
    public function initData()
    {
        $this->platform = request()->header('platform');
        $this->langTag = request()->header('langTag');
        $this->mid = request()->header('mid');
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
        $uid = request()->header('uid');
        $mid = request()->header('mid');
        $token = request()->header('token');
        $deviceInfo = request()->header('deviceInfo');
        $platform = request()->header('platform');
        if ($siteMode == 'public') {
            if (empty($uid)) {
                if (in_array($uri, FsConfig::PUBLIC_UID_URI_ARR)) {
                    $info = [
                        'missing header' => 'uid',
                    ];
                    $this->error(ErrorCodeService::UID_REQUIRED_ERROR, $info);
                }
            }

            if (empty($mid)) {
                if (in_array($uri, FsConfig::PUBLIC_MID_URI_ARR)) {
                    $info = [
                        'missing header' => 'mid',
                    ];
                    $this->error(ErrorCodeService::MID_REQUIRED_ERROR, $info);
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
            if (empty($uid)) {
                if (in_array($uri, FsConfig::PRIVATE_UID_URI_ARR)) {
                    $info = [
                        'missing header' => 'uid',
                    ];

                    $this->error(ErrorCodeService::UID_REQUIRED_ERROR, $info);
                }
            }

            if (empty($mid)) {
                if (in_array($uri, FsConfig::PRIVATE_MID_URI_ARR)) {
                    $info = [
                        'missing header' => 'mid',
                    ];

                    $this->error(ErrorCodeService::MID_REQUIRED_ERROR, $info);
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
        // If uid is not empty then token must be passed
        // If mid is not empty, then all three parameters must be passed
        if (empty($mid)) {
            if (! empty($uid)) {
                if (empty($token)) {
                    $info = [
                        'missing header' => 'token',
                    ];

                    $this->error(ErrorCodeService::TOKEN_REQUIRED_ERROR, $info);
                }
                if (in_array($uri, FsConfig::CHECK_USER_DELETE_URI)) {
                    $user = DB::table(FresnsUsersConfig::CFG_TABLE)->where('uuid', $uid)->first();
                } else {
                    $user = FresnsUsers::where('uuid', $uid)->first();
                }

                if (empty($user)) {
                    $info = [
                        'null user' => 'uid',
                    ];
                    $this->error(ErrorCodeService::USER_CHECK_ERROR, $info);
                }
                // Verify the existence of deleted_at
                if (! empty($user->phone)) {
                    $str = strstr($user->phone, 'deleted');
                    if ($str != false) {
                        $info = [
                            'null user' => 'uid',
                        ];
                        $this->error(ErrorCodeService::USER_CHECK_ERROR, $info);
                    }
                }
                if (! empty($user->email)) {
                    $str = strstr($user->phone, 'deleted');
                    if ($str != false) {
                        $info = [
                            'null user' => 'uid',
                        ];
                        $this->error(ErrorCodeService::USER_CHECK_ERROR, $info);
                    }
                }
                if ($user->is_enable == 0) {
                    if (! in_array($uri, FsConfig::CHECK_USER_IS_ENABLE_URI)) {
                        $this->error(ErrorCodeService::USER_IS_ENABLE_ERROR);
                    }
                }
                $userId = $user->id;

                // Verify token
                $cmd = FresnsCmdWordsConfig::FRESNS_CMD_VERIFY_SESSION_TOKEN;
                $input = [];
                $input['uid'] = request()->header('uid');
                $input['platform'] = request()->header('platform');
                $input['token'] = $token;
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $this->errorCheckInfo($resp);
                }
            }
        } else {
            if (empty($uid) || empty($mid) || empty($token)) {
                $info = [
                    'missing header' => 'uid or mid or token',
                ];

                $this->error(ErrorCodeService::ACCOUNT_CHECK_ERROR, $info);

                $this->error(ErrorCodeService::HEADER_ERROR, $info);
            }
            // Check if mid belongs to uid
            if (in_array($uri, FsConfig::CHECK_USER_DELETE_URI)) {
                $user = DB::table(FresnsUsersConfig::CFG_TABLE)->where('uuid', $uid)->first();
            } else {
                $user = FresnsUsers::where('uuid', $uid)->first();
            }
            if (empty($user)) {
                $info = [
                    'null user' => 'uid',
                ];
                $this->error(ErrorCodeService::USER_CHECK_ERROR, $info);
            }
            // Check if the uid is deleted_at
            if (! empty($user->phone)) {
                $str = strstr($user->phone, 'deleted');
                if ($str != false) {
                    $info = [
                        'null user' => 'uid',
                    ];
                    $this->error(ErrorCodeService::USER_CHECK_ERROR, $info);
                }
            }
            if (! empty($user->email)) {
                $str = strstr($user->phone, 'deleted');
                if ($str != false) {
                    $info = [
                        'null user' => 'uid',
                    ];
                    $this->error(ErrorCodeService::USER_CHECK_ERROR, $info);
                }
            }

            if ($user->is_enable == 0) {
                if (! in_array($uri, FsConfig::CHECK_USER_IS_ENABLE_URI)) {
                    $this->error(ErrorCodeService::USER_IS_ENABLE_ERROR);
                }
            }

            $userId = $user->id;
            $member = FresnsMembers::where('uuid', $mid)->first();

            if (empty($member)) {
                $info = [
                    'null member' => 'mid',
                ];
                $this->error(ErrorCodeService::MEMBER_CHECK_ERROR, $info);
            }
            if ($member['is_enable'] == 0) {
                $this->error(ErrorCodeService::TOKEN_IS_ENABLE_ERROR);
            }
            $memberId = $member['id'];

            $count = FresnsMembers::where('user_id', $userId)->where('id', $memberId)->count();
            if ($count == 0) {
                $this->error(ErrorCodeService::MEMBER_FAIL);
            }

            // Verify token
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_VERIFY_SESSION_TOKEN;
            $input = [];
            $input['uid'] = request()->header('uid');
            $input['platform'] = request()->header('platform');
            $input['mid'] = request()->header('mid');
            $input['token'] = $token;
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                $this->errorCheckInfo($resp);
            }

            // Querying Role Permissions
            if (in_array($uri, FsConfig::NOTICE_CONTENT_URI)) {
                /*
                 * Member Master Role Permission
                 * https://fresns.org/api/header.html
                 * member master role permission member_roles > permission > content_view whether to allow the view, if the view is prohibited, the "content class" and "message class" interfaces cannot be requested.
                 * If the primary role has an expiration time and has expired, then the inherited role permission is primary.
                 * If there is no inherited role (or the inherited ID cannot be found for the role), then the role permissions of the configuration table default_role key name key value prevails.
                 * If the configuration table key value is empty (or the role cannot be found), it is treated as no authority.
                 */
                $roleId = FresnsMemberRoleRelsService::getMemberRoleRels($memberId);

                if (empty($roleId)) {
                    $this->error(ErrorCodeService::ROLE_NO_PERMISSION);
                }

                $memberRole = FresnsMemberRoles::where('id', $roleId)->first();
                if (! empty($memberRole)) {
                    $permission = $memberRole['permission'];
                    $permissionArr = json_decode($permission, true);
                    if (! empty($permissionArr)) {
                        $permissionMap = FresnsMemberRolesService::getPermissionMap($permissionArr);
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
