<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Base;

class FsConfig
{
    const VIEW_MODE_PUBLIC = 1;
    const VIEW_MODE_PRIVATE = 2;

    // User deleted, list of requestable APIs
    // users > deleted_at
    const CHECK_USER_DELETE_URI = [
        '/api/fresns/user/login',
        '/api/fresns/user/restore',
        '/api/fresns/user/detail',
        '/api/fresns/user/logout',
    ];

    // User disabled status, list of requestable APIs
    // users > is_enable
    const CHECK_USER_IS_ENABLE_URI = [
        '/api/fresns/user/detail',
        '/api/fresns/user/logout',
    ];

    // Content class APIs
    const NOTICE_CONTENT_URI = [
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
    ];

    const CONFIGS_LIST = 'configs_list';
    const CONFIGS_LIST_API = 'configs_list_api';

    // Not logged in
    const HEADER_FIELD_ARR = [
        'platform',
        'version',
        'versionInt',
        'timestamp',
        'appId',
        'sign',
    ];

    // Logged in
    const SIGN_FIELD_ARR = [
        'platform',
        'version',
        'versionInt',
        'timestamp',
        'uid',
        'mid',
        'token',
        'appId',
    ];

    // Site Mode = public
    // uid required
    const PUBLIC_UID_URI_ARR = [
        '/api/fresns/info/downloadFile',
        '/api/fresns/user/logout',
        '/api/fresns/user/delete',
        '/api/fresns/user/restore',
        '/api/fresns/user/verification',
        '/api/fresns/user/detail',
        '/api/fresns/user/edit',
        '/api/fresns/user/walletLogs',
        '/api/fresns/member/auth',
        '/api/fresns/member/edit',
        '/api/fresns/member/mark',
        '/api/fresns/member/delete',
        '/api/fresns/notify/unread',
        '/api/fresns/notify/lists',
        '/api/fresns/notify/read',
        '/api/fresns/notify/delete',
        '/api/fresns/dialog/lists',
        '/api/fresns/dialog/messages',
        '/api/fresns/dialog/read',
        '/api/fresns/dialog/send',
        '/api/fresns/dialog/delete',
        '/api/fresns/post/follows',
        '/api/fresns/editor/lists',
        '/api/fresns/editor/detail',
        '/api/fresns/editor/create',
        '/api/fresns/editor/uploadToken',
        '/api/fresns/editor/upload',
        '/api/fresns/editor/update',
        '/api/fresns/editor/delete',
        '/api/fresns/editor/publish',
        '/api/fresns/editor/submit',
        '/api/fresns/editor/revoke',
        '/api/fresns/editor/configs',
    ];

    // Site Mode = public
    // mid required
    const PUBLIC_MID_URI_ARR = [
        '/api/fresns/info/downloadFile',
        '/api/fresns/member/edit',
        '/api/fresns/member/mark',
        '/api/fresns/member/delete',
        '/api/fresns/notify/unread',
        '/api/fresns/notify/lists',
        '/api/fresns/notify/delete',
        '/api/fresns/dialog/lists',
        '/api/fresns/dialog/messages',
        '/api/fresns/dialog/read',
        '/api/fresns/dialog/send',
        '/api/fresns/dialog/delete',
        '/api/fresns/post/follows',
        '/api/fresns/editor/lists',
        '/api/fresns/editor/detail',
        '/api/fresns/editor/create',
        '/api/fresns/editor/uploadToken',
        '/api/fresns/editor/upload',
        '/api/fresns/editor/update',
        '/api/fresns/editor/delete',
        '/api/fresns/editor/publish',
        '/api/fresns/editor/submit',
        '/api/fresns/editor/revoke',
        '/api/fresns/editor/configs',
    ];

    // Site Mode = public
    // token required
    const PUBLIC_TOKEN_URI_ARR = [
        '/api/fresns/info/downloadFile',
        '/api/fresns/user/logout',
        '/api/fresns/user/delete',
        '/api/fresns/user/restore',
        '/api/fresns/user/verification',
        '/api/fresns/user/detail',
        '/api/fresns/user/edit',
        '/api/fresns/user/walletLogs',
        '/api/fresns/member/auth',
        '/api/fresns/member/edit',
        '/api/fresns/member/mark',
        '/api/fresns/member/delete',
        '/api/fresns/notify/unread',
        '/api/fresns/notify/lists',
        '/api/fresns/notify/read',
        '/api/fresns/notify/delete',
        '/api/fresns/dialog/lists',
        '/api/fresns/dialog/messages',
        '/api/fresns/dialog/read',
        '/api/fresns/dialog/send',
        '/api/fresns/dialog/delete',
        '/api/fresns/post/follows',
        '/api/fresns/editor/lists',
        '/api/fresns/editor/detail',
        '/api/fresns/editor/create',
        '/api/fresns/editor/uploadToken',
        '/api/fresns/editor/upload',
        '/api/fresns/editor/update',
        '/api/fresns/editor/delete',
        '/api/fresns/editor/publish',
        '/api/fresns/editor/submit',
        '/api/fresns/editor/revoke',
        '/api/fresns/editor/configs',
    ];

    // Site Mode = public
    // deviceInfo required
    const PUBLIC_DEVICEINFO_URI_ARR = [
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
        '/api/fresns/editor/create',
        '/api/fresns/editor/publish',
        '/api/fresns/editor/submit',
    ];

    // Site Mode = private
    // uid required
    const PRIVATE_UID_URI_ARR = [
        '/api/fresns/info/extensions',
        '/api/fresns/info/emojis',
        '/api/fresns/info/stopWords',
        '/api/fresns/info/inputTips',
        '/api/fresns/info/downloadFile',
        '/api/fresns/user/logout',
        '/api/fresns/user/delete',
        '/api/fresns/user/restore',
        '/api/fresns/user/verification',
        '/api/fresns/user/detail',
        '/api/fresns/user/edit',
        '/api/fresns/user/walletLogs',
        '/api/fresns/member/auth',
        '/api/fresns/member/edit',
        '/api/fresns/member/mark',
        '/api/fresns/member/delete',
        '/api/fresns/member/detail',
        '/api/fresns/member/lists',
        '/api/fresns/member/interactions',
        '/api/fresns/member/markLists',
        '/api/fresns/member/roles',
        '/api/fresns/notify/unread',
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
        '/api/fresns/editor/create',
        '/api/fresns/editor/uploadToken',
        '/api/fresns/editor/upload',
        '/api/fresns/editor/update',
        '/api/fresns/editor/delete',
        '/api/fresns/editor/publish',
        '/api/fresns/editor/submit',
        '/api/fresns/editor/revoke',
        '/api/fresns/editor/configs',
    ];

    // Site Mode = private
    // mid required
    const PRIVATE_MID_URI_ARR = [
        '/api/fresns/info/extensions',
        '/api/fresns/info/emojis',
        '/api/fresns/info/stopWords',
        '/api/fresns/info/inputTips',
        '/api/fresns/info/downloadFile',
        '/api/fresns/member/edit',
        '/api/fresns/member/mark',
        '/api/fresns/member/delete',
        '/api/fresns/member/detail',
        '/api/fresns/member/lists',
        '/api/fresns/member/interactions',
        '/api/fresns/member/markLists',
        '/api/fresns/member/roles',
        '/api/fresns/notify/unread',
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
        '/api/fresns/editor/create',
        '/api/fresns/editor/uploadToken',
        '/api/fresns/editor/upload',
        '/api/fresns/editor/update',
        '/api/fresns/editor/delete',
        '/api/fresns/editor/publish',
        '/api/fresns/editor/submit',
        '/api/fresns/editor/revoke',
        '/api/fresns/editor/configs',
    ];

    // Site Mode = private
    // token required
    const PRIVATE_TOKEN_URI_ARR = [
        '/api/fresns/info/extensions',
        '/api/fresns/info/emojis',
        '/api/fresns/info/stopWords',
        '/api/fresns/info/inputTips',
        '/api/fresns/info/downloadFile',
        '/api/fresns/user/logout',
        '/api/fresns/user/delete',
        '/api/fresns/user/restore',
        '/api/fresns/user/verification',
        '/api/fresns/user/detail',
        '/api/fresns/user/edit',
        '/api/fresns/user/walletLogs',
        '/api/fresns/member/auth',
        '/api/fresns/member/edit',
        '/api/fresns/member/mark',
        '/api/fresns/member/delete',
        '/api/fresns/member/detail',
        '/api/fresns/member/lists',
        '/api/fresns/member/interactions',
        '/api/fresns/member/markLists',
        '/api/fresns/member/roles',
        '/api/fresns/notify/unread',
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
        '/api/fresns/editor/create',
        '/api/fresns/editor/uploadToken',
        '/api/fresns/editor/upload',
        '/api/fresns/editor/update',
        '/api/fresns/editor/delete',
        '/api/fresns/editor/publish',
        '/api/fresns/editor/submit',
        '/api/fresns/editor/revoke',
        '/api/fresns/editor/configs',
    ];

    // Site Mode = private
    // deviceInfo required
    const PRIVATE_DEVICEINFO_URI_ARR = [
        '/api/fresns/info/uploadLog',
        '/api/fresns/info/downloadFile',
        '/api/fresns/user/login',
        '/api/fresns/user/delete',
        '/api/fresns/user/restore',
        '/api/fresns/user/reset',
        '/api/fresns/user/edit',
        '/api/fresns/member/auth',
        '/api/fresns/member/edit',
        '/api/fresns/editor/create',
        '/api/fresns/editor/publish',
        '/api/fresns/editor/submit',
    ];
}
