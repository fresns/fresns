<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Base;

class FsApiConfig
{
    const VIEW_MODE_PUBLIC = 1;
    const VIEW_MODE_PRIVATE = 2;

    // Account deleted, list of requestable APIs
    // accounts > deleted_at
    const CHECK_ACCOUNT_DELETE_URI = [
        '/api/v1/account/login',
        '/api/v1/account/restore',
        '/api/v1/account/detail',
        '/api/v1/account/logout',
    ];

    // Account disabled status, list of requestable APIs
    // accounts > is_enable
    const CHECK_ACCOUNT_IS_ENABLE_URI = [
        '/api/v1/account/detail',
        '/api/v1/account/logout',
    ];

    // Content class APIs
    const NOTICE_CONTENT_URI = [
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
    ];

    const CONFIGS_LIST = 'configs_list';
    const CONFIGS_LIST_API = 'configs_list_api';

    // Not logged in
    const HEADER_FIELD_ARR = [
        'platform',
        'version',
        'timestamp',
        'appId',
        'sign',
    ];

    // Logged in
    const SIGN_FIELD_ARR = [
        'platform',
        'version',
        'timestamp',
        'aid',
        'uid',
        'token',
        'appId',
    ];

    // Site Mode = public
    // aid required
    const PUBLIC_AID_URI_ARR = [
        '/api/v1/info/downloadFile',
        '/api/v1/account/logout',
        '/api/v1/account/delete',
        '/api/v1/account/restore',
        '/api/v1/account/verification',
        '/api/v1/account/detail',
        '/api/v1/account/edit',
        '/api/v1/account/walletLogs',
        '/api/v1/user/auth',
        '/api/v1/user/edit',
        '/api/v1/user/mark',
        '/api/v1/user/delete',
        '/api/v1/notify/unread',
        '/api/v1/notify/lists',
        '/api/v1/notify/read',
        '/api/v1/notify/delete',
        '/api/v1/dialog/lists',
        '/api/v1/dialog/messages',
        '/api/v1/dialog/read',
        '/api/v1/dialog/send',
        '/api/v1/dialog/delete',
        '/api/v1/post/follows',
        '/api/v1/editor/lists',
        '/api/v1/editor/detail',
        '/api/v1/editor/create',
        '/api/v1/editor/uploadToken',
        '/api/v1/editor/upload',
        '/api/v1/editor/update',
        '/api/v1/editor/delete',
        '/api/v1/editor/publish',
        '/api/v1/editor/submit',
        '/api/v1/editor/revoke',
        '/api/v1/editor/configs',
    ];

    // Site Mode = public
    // uid required
    const PUBLIC_UID_URI_ARR = [
        '/api/v1/info/downloadFile',
        '/api/v1/user/edit',
        '/api/v1/user/mark',
        '/api/v1/user/delete',
        '/api/v1/notify/unread',
        '/api/v1/notify/lists',
        '/api/v1/notify/delete',
        '/api/v1/dialog/lists',
        '/api/v1/dialog/messages',
        '/api/v1/dialog/read',
        '/api/v1/dialog/send',
        '/api/v1/dialog/delete',
        '/api/v1/post/follows',
        '/api/v1/editor/lists',
        '/api/v1/editor/detail',
        '/api/v1/editor/create',
        '/api/v1/editor/uploadToken',
        '/api/v1/editor/upload',
        '/api/v1/editor/update',
        '/api/v1/editor/delete',
        '/api/v1/editor/publish',
        '/api/v1/editor/submit',
        '/api/v1/editor/revoke',
        '/api/v1/editor/configs',
    ];

    // Site Mode = public
    // token required
    const PUBLIC_TOKEN_URI_ARR = [
        '/api/v1/info/downloadFile',
        '/api/v1/account/logout',
        '/api/v1/account/delete',
        '/api/v1/account/restore',
        '/api/v1/account/verification',
        '/api/v1/account/detail',
        '/api/v1/account/edit',
        '/api/v1/account/walletLogs',
        '/api/v1/user/auth',
        '/api/v1/user/edit',
        '/api/v1/user/mark',
        '/api/v1/user/delete',
        '/api/v1/notify/unread',
        '/api/v1/notify/lists',
        '/api/v1/notify/read',
        '/api/v1/notify/delete',
        '/api/v1/dialog/lists',
        '/api/v1/dialog/messages',
        '/api/v1/dialog/read',
        '/api/v1/dialog/send',
        '/api/v1/dialog/delete',
        '/api/v1/post/follows',
        '/api/v1/editor/lists',
        '/api/v1/editor/detail',
        '/api/v1/editor/create',
        '/api/v1/editor/uploadToken',
        '/api/v1/editor/upload',
        '/api/v1/editor/update',
        '/api/v1/editor/delete',
        '/api/v1/editor/publish',
        '/api/v1/editor/submit',
        '/api/v1/editor/revoke',
        '/api/v1/editor/configs',
    ];

    // Site Mode = public
    // deviceInfo required
    const PUBLIC_DEVICEINFO_URI_ARR = [
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
        '/api/v1/editor/create',
        '/api/v1/editor/publish',
        '/api/v1/editor/submit',
    ];

    // Site Mode = private
    // aid required
    const PRIVATE_AID_URI_ARR = [
        '/api/v1/info/extensions',
        '/api/v1/info/stickers',
        '/api/v1/info/blockWords',
        '/api/v1/info/inputTips',
        '/api/v1/info/downloadFile',
        '/api/v1/account/logout',
        '/api/v1/account/delete',
        '/api/v1/account/restore',
        '/api/v1/account/verification',
        '/api/v1/account/detail',
        '/api/v1/account/edit',
        '/api/v1/account/walletLogs',
        '/api/v1/user/auth',
        '/api/v1/user/edit',
        '/api/v1/user/mark',
        '/api/v1/user/delete',
        '/api/v1/user/detail',
        '/api/v1/user/lists',
        '/api/v1/user/interactions',
        '/api/v1/user/markLists',
        '/api/v1/user/roles',
        '/api/v1/notify/unread',
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
        '/api/v1/editor/create',
        '/api/v1/editor/uploadToken',
        '/api/v1/editor/upload',
        '/api/v1/editor/update',
        '/api/v1/editor/delete',
        '/api/v1/editor/publish',
        '/api/v1/editor/submit',
        '/api/v1/editor/revoke',
        '/api/v1/editor/configs',
    ];

    // Site Mode = private
    // uid required
    const PRIVATE_UID_URI_ARR = [
        '/api/v1/info/extensions',
        '/api/v1/info/stickers',
        '/api/v1/info/blockWords',
        '/api/v1/info/inputTips',
        '/api/v1/info/downloadFile',
        '/api/v1/user/edit',
        '/api/v1/user/mark',
        '/api/v1/user/delete',
        '/api/v1/user/detail',
        '/api/v1/user/lists',
        '/api/v1/user/interactions',
        '/api/v1/user/markLists',
        '/api/v1/user/roles',
        '/api/v1/notify/unread',
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
        '/api/v1/editor/create',
        '/api/v1/editor/uploadToken',
        '/api/v1/editor/upload',
        '/api/v1/editor/update',
        '/api/v1/editor/delete',
        '/api/v1/editor/publish',
        '/api/v1/editor/submit',
        '/api/v1/editor/revoke',
        '/api/v1/editor/configs',
    ];

    // Site Mode = private
    // token required
    const PRIVATE_TOKEN_URI_ARR = [
        '/api/v1/info/extensions',
        '/api/v1/info/stickers',
        '/api/v1/info/blockWords',
        '/api/v1/info/inputTips',
        '/api/v1/info/downloadFile',
        '/api/v1/account/logout',
        '/api/v1/account/delete',
        '/api/v1/account/restore',
        '/api/v1/account/verification',
        '/api/v1/account/detail',
        '/api/v1/account/edit',
        '/api/v1/account/walletLogs',
        '/api/v1/user/auth',
        '/api/v1/user/edit',
        '/api/v1/user/mark',
        '/api/v1/user/delete',
        '/api/v1/user/detail',
        '/api/v1/user/lists',
        '/api/v1/user/interactions',
        '/api/v1/user/markLists',
        '/api/v1/user/roles',
        '/api/v1/notify/unread',
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
        '/api/v1/editor/create',
        '/api/v1/editor/uploadToken',
        '/api/v1/editor/upload',
        '/api/v1/editor/update',
        '/api/v1/editor/delete',
        '/api/v1/editor/publish',
        '/api/v1/editor/submit',
        '/api/v1/editor/revoke',
        '/api/v1/editor/configs',
    ];

    // Site Mode = private
    // deviceInfo required
    const PRIVATE_DEVICEINFO_URI_ARR = [
        '/api/v1/info/uploadLog',
        '/api/v1/info/downloadFile',
        '/api/v1/account/login',
        '/api/v1/account/delete',
        '/api/v1/account/restore',
        '/api/v1/account/reset',
        '/api/v1/account/edit',
        '/api/v1/user/auth',
        '/api/v1/user/edit',
        '/api/v1/editor/create',
        '/api/v1/editor/publish',
        '/api/v1/editor/submit',
    ];
}
