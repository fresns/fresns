<?php
/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\Center\Common;

use App\Base\Config\BaseConfig;

class GlobalConfig extends BaseConfig
{
    const CONFIGS_LIST = 'configs_list';
    const CONFIGS_LIST_API = 'configs_list_api';

    const URI_CONVERSION_OBJECT_TYPE = [
        'Unknown' => [
            '/api/fresns/info/configs',
            '/api/fresns/info/extensions',
            '/api/fresns/info/overview',
            '/api/fresns/info/emojis',
            '/api/fresns/info/stopWords',
            '/api/fresns/info/sendVerifyCode',
            '/api/fresns/info/inputTips',
            '/api/fresns/info/uploadLog',
            '/api/fresns/info/downloadFile',
            '/api/fresns/user/logout',
            '/api/fresns/user/restore',
            '/api/fresns/user/detail',
            '/api/fresns/user/walletLogs',
            '/api/fresns/member/mark',
            '/api/fresns/member/delete',
            '/api/fresns/member/detail',
            '/api/fresns/member/lists',
            '/api/fresns/member/interactions',
            '/api/fresns/member/markLists',
            '/api/fresns/notify/lists',
            '/api/fresns/notify/read',
            '/api/fresns/notify/delete',
            '/api/fresns/dialog/lists',
            '/api/fresns/dialog/messages',
            '/api/fresns/dialog/read',
            '/api/fresns/dialog/send',
            '/api/fresns/dialog/delete',
            '/api/fresns/group/trees',
            '/api/fresns/group/lists',
            '/api/fresns/group/detail',
            '/api/fresns/hashtag/lists',
            '/api/fresns/hashtag/detail',
            '/api/fresns/post/lists',
            '/api/fresns/post/detail',
            '/api/fresns/post/follows',
            '/api/fresns/post/nearbys',
            '/api/fresns/comment/lists',
            '/api/fresns/comment/detail',
            '/api/fresns/editor/lists',
            '/api/fresns/editor/detail',
            // '/api/fresns/editor/create',
            '/api/fresns/editor/uploadToken',
            '/api/fresns/editor/upload',
            '/api/fresns/editor/update',
            '/api/fresns/editor/delete',
            // '/api/fresns/editor/publish',
            // '/api/fresns/editor/submit',
            '/api/fresns/editor/revoke',
        ],
        'Register' => [
            '/api/fresns/user/register',
        ],
        'Login' => [
            '/api/fresns/user/login',
        ],
        'Delete User' => [
            '/api/fresns/user/delete',
        ],
        'Reset Password' => [
            '/api/fresns/user/reset',
        ],
        'Modify User Info' => [
            '/api/fresns/user/edit',
        ],
        'Member Login' => [
            '/api/fresns/member/auth',
        ],
        'Modify Member Info' => [
            '/api/fresns/member/edit',
        ],
    ];

    const URI_CONVERSION_OBJECT_TYPE_NO = [
        1 => [
            '/api/fresns/info/configs',
            '/api/fresns/info/extensions',
            '/api/fresns/info/summary',
            '/api/fresns/info/emojis',
            '/api/fresns/info/stopWords',
            '/api/fresns/info/sendVerifyCode',
            '/api/fresns/info/inputtips',
            '/api/fresns/info/uploadLog',
            '/api/fresns/info/downloadFile',
            '/api/fresns/user/logout',
            '/api/fresns/user/restore',
            '/api/fresns/user/detail',
            '/api/fresns/user/walletLogs',
            '/api/fresns/member/mark',
            '/api/fresns/member/delete',
            '/api/fresns/member/detail',
            '/api/fresns/member/lists',
            '/api/fresns/member/interactions',
            '/api/fresns/member/markLists',
            '/api/fresns/notify/lists',
            '/api/fresns/notify/read',
            '/api/fresns/notify/delete',
            '/api/fresns/dialog/lists',
            '/api/fresns/dialog/messages',
            '/api/fresns/dialog/read',
            '/api/fresns/dialog/send',
            '/api/fresns/dialog/delete',
            '/api/fresns/group/trees',
            '/api/fresns/group/lists',
            '/api/fresns/group/detail',
            '/api/fresns/hashtag/lists',
            '/api/fresns/hashtag/detail',
            '/api/fresns/post/lists',
            '/api/fresns/post/detail',
            '/api/fresns/post/follows',
            '/api/fresns/post/nearbys',
            '/api/fresns/comment/lists',
            '/api/fresns/comment/detail',
            '/api/fresns/editor/lists',
            '/api/fresns/editor/detail',
            // '/api/fresns/editor/create',
            '/api/fresns/editor/uploadToken',
            '/api/fresns/editor/upload',
            '/api/fresns/editor/update',
            '/api/fresns/editor/delete',
            // '/api/fresns/editor/publish',
            // '/api/fresns/editor/submit',
            '/api/fresns/editor/revoke',
        ],
        2 => [
            '/api/fresns/user/register',
        ],
        3 => [
            '/api/fresns/user/login',
        ],
        4 => [
            '/api/fresns/user/delete',
        ],
        5 => [
            '/api/fresns/user/reset',
        ],
        6 => [
            '/api/fresns/user/edit',
        ],
        7 => [
            '/api/fresns/member/auth',
        ],
        8 => [
            '/api/fresns/member/edit',
        ],
    ];

    // Link conversion to api name
    const URI_API_NAME_MAP = [
        '/api/fresns/info/configs'          => 'API Configs',
        '/api/fresns/info/extensions'       => 'API Extensions',
        '/api/fresns/info/overview'         => 'API Overview',
        '/api/fresns/info/emojis'           => 'API Emojis',
        '/api/fresns/info/stopWords'        => 'API Stop Words',
        '/api/fresns/info/sendVerifyCode'   => 'API Send VerifyCode',
        '/api/fresns/info/inputTips'        => 'API Input Tips',
        '/api/fresns/info/uploadLog'        => 'API Upload Log',
        '/api/fresns/info/downloadFile'     => 'API Down File',
        '/api/fresns/user/register'         => 'API User Register',
        '/api/fresns/user/login'            => 'API User Login',
        '/api/fresns/user/logout'           => 'API User Logout',
        '/api/fresns/user/delete'           => 'API User Delete',
        '/api/fresns/user/restore'          => 'API User Restore',
        '/api/fresns/user/reset'            => 'API User Reset Password',
        '/api/fresns/user/detail'           => 'API User Detail',
        '/api/fresns/user/edit'             => 'API User Edit',
        '/api/fresns/user/walletLogs'       => 'API User Wallet Logs',
        '/api/fresns/member/auth'           => 'API Member Login',
        '/api/fresns/member/edit'           => 'API Member Edit',
        '/api/fresns/member/mark'           => 'API Member Mark',
        '/api/fresns/member/delete'         => 'API Member Delete',
        '/api/fresns/member/markLists'      => 'API Member Mark Lists',
        '/api/fresns/member/detail'         => 'API Member Detail',
        '/api/fresns/member/lists'          => 'API Member Lists',
        '/api/fresns/member/interactions'   => 'API Member Interactions',
        '/api/fresns/notify/lists'          => 'API Notify Lists',
        '/api/fresns/notify/read'           => 'API Notify Read',
        '/api/fresns/notify/delete'         => 'API Notify Delete',
        '/api/fresns/dialog/lists'          => 'API Dialog Lists',
        '/api/fresns/dialog/messages'       => 'API Dialog Messages',
        '/api/fresns/dialog/read'           => 'API Dialog Read',
        '/api/fresns/dialog/send'           => 'API Dialog Send',
        '/api/fresns/dialog/delete'         => 'API Dialog Delete',
        '/api/fresns/group/trees'           => 'API Group Trees',
        '/api/fresns/group/lists'           => 'API Group Lists',
        '/api/fresns/group/detail'          => 'API Group Detail',
        '/api/fresns/hashtag/lists'         => 'API Hashtag Lists',
        '/api/fresns/hashtag/detail'        => 'API Hashtag Detail',
        '/api/fresns/post/lists'            => 'API Post Lists',
        '/api/fresns/post/detail'           => 'API Post Detail',
        '/api/fresns/post/follows'          => 'API Post Follows',
        '/api/fresns/post/nearbys'          => 'API Post Nearbys',
        '/api/fresns/comment/lists'         => 'API Comment Lists',
        '/api/fresns/comment/detail'        => 'API Comment Detail',
        '/api/fresns/editor/lists'          => 'API Editor Lists',
        '/api/fresns/editor/detail'         => 'API Editor Detail',
        '/api/fresns/editor/create'         => 'API Editor Create',
        '/api/fresns/editor/uploadToken'    => 'API Editor Upload Token',
        '/api/fresns/editor/upload'         => 'API Editor Upload',
        '/api/fresns/editor/update'         => 'API Editor Update',
        '/api/fresns/editor/delete'         => 'API Editor Delete',
        '/api/fresns/editor/publish'        => 'API Editor Publish',
        '/api/fresns/editor/submit'         => 'API Editor Submit',
        '/api/fresns/editor/revoke'         => 'API Editor Revoke',
    ];

    // Link conversion(Unknown)
    const URI_CONVERSION_OBJECT_NAME = [
        'App\Http\FresnsDb\FresnsConfigs' => [
            '/api/fresns/info/configs',
        ],
        'App\Http\FresnsDb\FresnsPluginUsages' => [
            '/api/fresns/info/configs',
        ],
        'App\Http\FresnsDb\FresnsEmojis' => [
            '/api/fresns/info/emojis',
        ],
        'App\Http\FresnsDb\FresnsStopWords' => [
            '/api/fresns/info/stopWords',
        ],
        'App\Http\FresnsDb\FresnsVerifyCodes' => [
            '/api/fresns/info/sendVerifyCode',
        ],
        'App\Http\FresnsDb\FresnsSessionLogs' => [
            '/api/fresns/info/uploadLog',
        ],
        'App\Http\FresnsDb\FresnsFiles' => [
            '/api/fresns/info/downloadFile',
            '/api/fresns/editor/uploadToken',
            '/api/fresns/editor/upload',
        ],
        'App\Http\FresnsDb\FresnsUsers' => [
            '/api/fresns/user/register',
            '/api/fresns/user/login',
            '/api/fresns/user/logout',
            '/api/fresns/user/delete',
            '/api/fresns/user/restore',
            '/api/fresns/user/restore',
            '/api/fresns/user/reset',
            '/api/fresns/user/detail',
            '/api/fresns/user/edit',
        ],
        'App\Http\FresnsDb\FresnsWalletLogs' => [
            '/api/fresns/user/walletLogs',
        ],
        'App\Http\FresnsDb\FresnsMembers' => [
            '/api/fresns/member/auth',
            '/api/fresns/member/edit',
            '/api/fresns/member/mark',
            '/api/fresns/member/delete',
            '/api/fresns/member/detail',
            '/api/fresns/member/lists',
            '/api/fresns/member/interactions',
            '/api/fresns/member/markLists',
        ],
        'App\Http\FresnsDb\FresnsNotifies' => [
            '/api/fresns/notify/lists',
            '/api/fresns/notify/read',
            '/api/fresns/notify/delete',
        ],
        'App\Http\FresnsDb\FresnsDialogs' => [
            '/api/fresns/dialog/lists',
            '/api/fresns/dialog/messages',
            '/api/fresns/dialog/read',
            '/api/fresns/dialog/delete',
        ],
        'App\Http\FresnsDb\FresnsDialogMessages' => [
            '/api/fresns/dialog/send',
        ],
        'App\Http\FresnsDb\FresnsGroups' => [
            '/api/fresns/group/trees',
            '/api/fresns/group/lists',
            '/api/fresns/group/detail',
        ],
        'App\Http\FresnsDb\FresnsHashtags' => [
            '/api/fresns/hashtag/lists',
            '/api/fresns/hashtag/detail',
        ],
        'App\Http\FresnsDb\FresnsPosts' => [
            '/api/fresns/post/lists',
            '/api/fresns/post/detail',
            '/api/fresns/post/follows',
            '/api/fresns/post/nearbys',
        ],
        'App\Http\FresnsDb\FresnsComments' => [
            '/api/fresns/comment/lists',
            '/api/fresns/comment/detail',
        ],
    ];

    // This API stores the header deviceInfo parameter
    const ADD_DEVICE_INFO_URI_ARR = [
        '/api/fresns/info/sendVerifyCode',
        '/api/fresns/info/uploadLog',
        '/api/fresns/info/downloadFile',
        '/api/fresns/user/register',
        '/api/fresns/user/login',
        '/api/fresns/user/delete',
        '/api/fresns/user/restore',
        '/api/fresns/user/reset',
        '/api/fresns/user/edit',
        '/api/fresns/member/auth',
        '/api/fresns/member/edit',
        '/api/fresns/dialog/send',
        '/api/fresns/editor/create',
        '/api/fresns/editor/publish',
        '/api/fresns/editor/submit',
    ];
}
