<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class SessionLog extends Model
{
    const TYPE_CUSTOMIZE = 1;
    const TYPE_PLUGIN = 2;
    const TYPE_LOGIN_PANEL = 3;
    const TYPE_ACCOUNT_REGISTER = 4;
    const TYPE_ACCOUNT_LOGIN = 5;
    const TYPE_ACCOUNT_EDIT_DATA = 6;
    const TYPE_ACCOUNT_EDIT_PASSWORD = 7;
    const TYPE_ACCOUNT_DELETE = 8;
    const TYPE_USER_ADD = 9;
    const TYPE_USER_LOGIN = 10;
    const TYPE_USER_EDIT_DATA = 11;
    const TYPE_USER_EDIT_PASSWORD = 12;
    const TYPE_USER_DELETE = 13;
    const TYPE_WALLET_INCREASE = 14;
    const TYPE_WALLET_DECREASE = 15;
    const TYPE_WALLET_EDIT_PASSWORD = 16;
    const TYPE_POST_CREATE_DRAFT = 17;
    const TYPE_POST_REVIEW = 18;
    const TYPE_POST_PUBLISH = 19;
    const TYPE_POST_DELETE = 20;
    const TYPE_POST_LOG_DELETE = 21;
    const TYPE_COMMENT_CREATE_DRAFT = 22;
    const TYPE_COMMENT_REVIEW = 23;
    const TYPE_COMMENT_PUBLISH = 24;
    const TYPE_COMMENT_DELETE = 25;
    const TYPE_COMMENT_LOG_DELETE = 26;
    const TYPE_MARK_LIKE = 27;
    const TYPE_MARK_DISLIKE = 28;
    const TYPE_MARK_FOLLOW = 29;
    const TYPE_MARK_BLOCK = 30;
    const TYPE_UPLOAD_FILE = 31;
    const TYPE_DIALOG_MESSAGE = 32;

    const STATE_UNKNOWN = 1;
    const STATE_SUCCESS = 2;
    const STATE_FAILURE = 3;

    protected $casts = [
        'device_info' => 'json',
    ];
}
