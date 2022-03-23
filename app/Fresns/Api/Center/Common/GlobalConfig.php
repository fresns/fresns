<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Center\Common;

use App\Fresns\Api\Base\Config\BaseConfig;

class GlobalConfig extends BaseConfig
{
    const CONFIGS_LIST = 'configs_list';
    const CONFIGS_LIST_API = 'configs_list_api';

    const URI_CONVERSION_OBJECT_TYPE = [
        'Unknown' => [
            '/api/v1/info/configs',
            '/api/v1/info/extensions',
            '/api/v1/info/overview',
            '/api/v1/info/stickers',
            '/api/v1/info/blockWords',
            '/api/v1/info/sendVerifyCode',
            '/api/v1/info/inputTips',
            '/api/v1/info/uploadLog',
            '/api/v1/info/downloadFile',
            '/api/v1/account/logout',
            '/api/v1/account/restore',
            '/api/v1/account/verification',
            '/api/v1/account/detail',
            '/api/v1/account/walletLogs',
            '/api/v1/user/mark',
            '/api/v1/user/delete',
            '/api/v1/user/detail',
            '/api/v1/user/lists',
            '/api/v1/user/interactions',
            '/api/v1/user/markLists',
            '/api/v1/notify/lists',
            '/api/v1/notify/read',
            '/api/v1/notify/delete',
            '/api/v1/dialog/lists',
            '/api/v1/dialog/messages',
            '/api/v1/dialog/read',
            '/api/v1/dialog/send',
            '/api/v1/dialog/delete',
            '/api/v1/group/trees',
            '/api/v1/group/lists',
            '/api/v1/group/detail',
            '/api/v1/hashtag/lists',
            '/api/v1/hashtag/detail',
            '/api/v1/post/lists',
            '/api/v1/post/detail',
            '/api/v1/post/follows',
            '/api/v1/post/nearbys',
            '/api/v1/comment/lists',
            '/api/v1/comment/detail',
            '/api/v1/editor/lists',
            '/api/v1/editor/detail',
            // '/api/v1/editor/create',
            '/api/v1/editor/uploadToken',
            '/api/v1/editor/upload',
            '/api/v1/editor/update',
            '/api/v1/editor/delete',
            // '/api/v1/editor/publish',
            // '/api/v1/editor/submit',
            '/api/v1/editor/revoke',
        ],
        'Register' => [
            '/api/v1/account/register',
        ],
        'Login' => [
            '/api/v1/account/login',
        ],
        'Delete Account' => [
            '/api/v1/account/delete',
        ],
        'Reset Password' => [
            '/api/v1/account/reset',
        ],
        'Modify Account Info' => [
            '/api/v1/account/edit',
        ],
        'User Login' => [
            '/api/v1/user/auth',
        ],
        'Modify User Info' => [
            '/api/v1/user/edit',
        ],
    ];

    const URI_CONVERSION_OBJECT_TYPE_NO = [
        1 => [
            '/api/v1/info/configs',
            '/api/v1/info/extensions',
            '/api/v1/info/summary',
            '/api/v1/info/stickers',
            '/api/v1/info/blockWords',
            '/api/v1/info/sendVerifyCode',
            '/api/v1/info/inputtips',
            '/api/v1/info/uploadLog',
            '/api/v1/info/downloadFile',
            '/api/v1/account/logout',
            '/api/v1/account/restore',
            '/api/v1/account/verification',
            '/api/v1/account/detail',
            '/api/v1/account/walletLogs',
            '/api/v1/user/mark',
            '/api/v1/user/delete',
            '/api/v1/user/detail',
            '/api/v1/user/lists',
            '/api/v1/user/interactions',
            '/api/v1/user/markLists',
            '/api/v1/notify/lists',
            '/api/v1/notify/read',
            '/api/v1/notify/delete',
            '/api/v1/dialog/lists',
            '/api/v1/dialog/messages',
            '/api/v1/dialog/read',
            '/api/v1/dialog/send',
            '/api/v1/dialog/delete',
            '/api/v1/group/trees',
            '/api/v1/group/lists',
            '/api/v1/group/detail',
            '/api/v1/hashtag/lists',
            '/api/v1/hashtag/detail',
            '/api/v1/post/lists',
            '/api/v1/post/detail',
            '/api/v1/post/follows',
            '/api/v1/post/nearbys',
            '/api/v1/comment/lists',
            '/api/v1/comment/detail',
            '/api/v1/editor/lists',
            '/api/v1/editor/detail',
            // '/api/v1/editor/create',
            '/api/v1/editor/uploadToken',
            '/api/v1/editor/upload',
            '/api/v1/editor/update',
            '/api/v1/editor/delete',
            // '/api/v1/editor/publish',
            // '/api/v1/editor/submit',
            '/api/v1/editor/revoke',
        ],
        2 => [
            '/api/v1/account/register',
        ],
        3 => [
            '/api/v1/account/login',
        ],
        4 => [
            '/api/v1/account/delete',
        ],
        5 => [
            '/api/v1/account/reset',
        ],
        6 => [
            '/api/v1/account/edit',
        ],
        7 => [
            '/api/v1/user/auth',
        ],
        8 => [
            '/api/v1/user/edit',
        ],
    ];

    // Link conversion to api name
    const URI_API_NAME_MAP = [
        '/api/v1/info/configs'          => 'API Configs',
        '/api/v1/info/extensions'       => 'API Extensions',
        '/api/v1/info/overview'         => 'API Overview',
        '/api/v1/info/stickers'         => 'API Stickers',
        '/api/v1/info/blockWords'       => 'API Block Words',
        '/api/v1/info/sendVerifyCode'   => 'API Send VerifyCode',
        '/api/v1/info/inputTips'        => 'API Input Tips',
        '/api/v1/info/uploadLog'        => 'API Upload Log',
        '/api/v1/info/downloadFile'     => 'API Down File',
        '/api/v1/account/register'      => 'API Account Register',
        '/api/v1/account/login'         => 'API Account Login',
        '/api/v1/account/logout'        => 'API Account Logout',
        '/api/v1/account/delete'        => 'API Account Delete',
        '/api/v1/account/restore'       => 'API Account Restore',
        '/api/v1/account/reset'         => 'API Account Reset Password',
        '/api/v1/account/verification'  => 'API Account Verification',
        '/api/v1/account/detail'        => 'API Account Detail',
        '/api/v1/account/edit'          => 'API Account Edit',
        '/api/v1/account/walletLogs'    => 'API Account Wallet Logs',
        '/api/v1/user/auth'             => 'API User Login',
        '/api/v1/user/edit'             => 'API User Edit',
        '/api/v1/user/mark'             => 'API User Mark',
        '/api/v1/user/delete'           => 'API User Delete',
        '/api/v1/user/markLists'        => 'API User Mark Lists',
        '/api/v1/user/detail'           => 'API User Detail',
        '/api/v1/user/lists'            => 'API User Lists',
        '/api/v1/user/interactions'     => 'API User Interactions',
        '/api/v1/notify/lists'          => 'API Notify Lists',
        '/api/v1/notify/read'           => 'API Notify Read',
        '/api/v1/notify/delete'         => 'API Notify Delete',
        '/api/v1/dialog/lists'          => 'API Dialog Lists',
        '/api/v1/dialog/messages'       => 'API Dialog Messages',
        '/api/v1/dialog/read'           => 'API Dialog Read',
        '/api/v1/dialog/send'           => 'API Dialog Send',
        '/api/v1/dialog/delete'         => 'API Dialog Delete',
        '/api/v1/group/trees'           => 'API Group Trees',
        '/api/v1/group/lists'           => 'API Group Lists',
        '/api/v1/group/detail'          => 'API Group Detail',
        '/api/v1/hashtag/lists'         => 'API Hashtag Lists',
        '/api/v1/hashtag/detail'        => 'API Hashtag Detail',
        '/api/v1/post/lists'            => 'API Post Lists',
        '/api/v1/post/detail'           => 'API Post Detail',
        '/api/v1/post/follows'          => 'API Post Follows',
        '/api/v1/post/nearbys'          => 'API Post Nearbys',
        '/api/v1/comment/lists'         => 'API Comment Lists',
        '/api/v1/comment/detail'        => 'API Comment Detail',
        '/api/v1/editor/lists'          => 'API Editor Lists',
        '/api/v1/editor/detail'         => 'API Editor Detail',
        '/api/v1/editor/create'         => 'API Editor Create',
        '/api/v1/editor/uploadToken'    => 'API Editor Upload Token',
        '/api/v1/editor/upload'         => 'API Editor Upload',
        '/api/v1/editor/update'         => 'API Editor Update',
        '/api/v1/editor/delete'         => 'API Editor Delete',
        '/api/v1/editor/publish'        => 'API Editor Publish',
        '/api/v1/editor/submit'         => 'API Editor Submit',
        '/api/v1/editor/revoke'         => 'API Editor Revoke',
    ];

    // Link conversion(Unknown)
    const URI_CONVERSION_OBJECT_NAME = [
        'App\Fresns\Api\FsDb\FresnsConfigs' => [
            '/api/v1/info/configs',
        ],
        'App\Fresns\Api\FsDb\FresnsPluginUsages' => [
            '/api/v1/info/configs',
        ],
        'App\Fresns\Api\FsDb\FresnsStickers' => [
            '/api/v1/info/stickers',
        ],
        'App\Fresns\Api\FsDb\FresnsBlockWords' => [
            '/api/v1/info/blockWords',
        ],
        'App\Fresns\Api\FsDb\FresnsVerifyCodes' => [
            '/api/v1/info/sendVerifyCode',
        ],
        'App\Fresns\Api\FsDb\FresnsSessionLogs' => [
            '/api/v1/info/uploadLog',
        ],
        'App\Fresns\Api\FsDb\FresnsFiles' => [
            '/api/v1/info/downloadFile',
            '/api/v1/editor/uploadToken',
            '/api/v1/editor/upload',
        ],
        'App\Fresns\Api\FsDb\FresnsAccounts' => [
            '/api/v1/account/register',
            '/api/v1/account/login',
            '/api/v1/account/logout',
            '/api/v1/account/delete',
            '/api/v1/account/restore',
            '/api/v1/account/restore',
            '/api/v1/account/reset',
            '/api/v1/account/verification',
            '/api/v1/account/detail',
            '/api/v1/account/edit',
        ],
        'App\Fresns\Api\FsDb\FresnsWalletLogs' => [
            '/api/v1/account/walletLogs',
        ],
        'App\Fresns\Api\FsDb\FresnsUsers' => [
            '/api/v1/user/auth',
            '/api/v1/user/edit',
            '/api/v1/user/mark',
            '/api/v1/user/delete',
            '/api/v1/user/detail',
            '/api/v1/user/lists',
            '/api/v1/user/interactions',
            '/api/v1/user/markLists',
        ],
        'App\Fresns\Api\FsDb\FresnsNotifies' => [
            '/api/v1/notify/lists',
            '/api/v1/notify/read',
            '/api/v1/notify/delete',
        ],
        'App\Fresns\Api\FsDb\FresnsDialogs' => [
            '/api/v1/dialog/lists',
            '/api/v1/dialog/messages',
            '/api/v1/dialog/read',
            '/api/v1/dialog/delete',
        ],
        'App\Fresns\Api\FsDb\FresnsDialogMessages' => [
            '/api/v1/dialog/send',
        ],
        'App\Fresns\Api\FsDb\FresnsGroups' => [
            '/api/v1/group/trees',
            '/api/v1/group/lists',
            '/api/v1/group/detail',
        ],
        'App\Fresns\Api\FsDb\FresnsHashtags' => [
            '/api/v1/hashtag/lists',
            '/api/v1/hashtag/detail',
        ],
        'App\Fresns\Api\FsDb\FresnsPosts' => [
            '/api/v1/post/lists',
            '/api/v1/post/detail',
            '/api/v1/post/follows',
            '/api/v1/post/nearbys',
        ],
        'App\Fresns\Api\FsDb\FresnsComments' => [
            '/api/v1/comment/lists',
            '/api/v1/comment/detail',
        ],
    ];

    // This API stores the header deviceInfo parameter
    const ADD_DEVICE_INFO_URI_ARR = [
        '/api/v1/info/sendVerifyCode',
        '/api/v1/info/uploadLog',
        '/api/v1/info/downloadFile',
        '/api/v1/account/register',
        '/api/v1/account/login',
        '/api/v1/account/delete',
        '/api/v1/account/restore',
        '/api/v1/account/reset',
        '/api/v1/account/edit',
        '/api/v1/user/auth',
        '/api/v1/user/edit',
        '/api/v1/dialog/send',
        '/api/v1/editor/create',
        '/api/v1/editor/publish',
        '/api/v1/editor/submit',
    ];
}
