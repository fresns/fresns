<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class SessionLog extends Model
{
    const TYPE_UNKNOWN = 1;
    const TYPE_PLUGIN = 2;
    const TYPE_LOGIN_PANEL = 3;
    const TYPE_ACCOUNT_REGISTER = 4;
    const TYPE_ACCOUNT_LOGIN = 5;
    const TYPE_ACCOUNT_UPDATE_DATA = 6;
    const TYPE_ACCOUNT_UPDATE_PASSWORD = 7;
    const TYPE_ACCOUNT_DELETE = 8;
    const TYPE_USER_ADD = 9;
    const TYPE_USER_LOGIN = 10;
    const TYPE_USER_UPDATE_PROFILE = 11;
    const TYPE_USER_UPDATE_SETTING = 12;
    const TYPE_USER_UPDATE_PIN = 13;
    const TYPE_USER_DELETE = 14;
    const TYPE_WALLET_INCREASE = 15;
    const TYPE_WALLET_DECREASE = 16;
    const TYPE_WALLET_UPDATE_PASSWORD = 17;
    const TYPE_POST_CREATE_DRAFT = 18;
    const TYPE_POST_REVIEW = 19;
    const TYPE_POST_PUBLISH = 20;
    const TYPE_POST_DELETE = 21;
    const TYPE_POST_LOG_DELETE = 22;
    const TYPE_COMMENT_CREATE_DRAFT = 23;
    const TYPE_COMMENT_REVIEW = 24;
    const TYPE_COMMENT_PUBLISH = 25;
    const TYPE_COMMENT_DELETE = 26;
    const TYPE_COMMENT_LOG_DELETE = 27;
    const TYPE_MARK_LIKE = 28;
    const TYPE_MARK_DISLIKE = 29;
    const TYPE_MARK_FOLLOW = 30;
    const TYPE_MARK_BLOCK = 31;
    const TYPE_UPLOAD_FILE = 32;
    const TYPE_CONVERSATION_MESSAGE = 33;

    const STATE_UNKNOWN = 1;
    const STATE_SUCCESS = 2;
    const STATE_FAILURE = 3;

    protected $casts = [
        'device_info' => 'json',
        'more_info' => 'json',
    ];
}
