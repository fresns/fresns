<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsCmd;

use App\Fresns\Api\Center\Base\BasePluginConfig;

class FresnsCmdWordsConfig extends BasePluginConfig
{
    /**
     * System Command Word
     * https://fresns.org/extensions/command.html.
     */

    // unikey
    public $uniKey = 'fresns';

    // Command Word: Default
    public const FRESNS_CMD_DEFAULT = 'fresns_cmd_default';

    // Command Word: Basis
    public const FRESNS_CMD_VERIFY_SIGN = 'fresns_cmd_verify_sign';
    public const FRESNS_CMD_UPLOAD_SESSION_LOG = 'fresns_cmd_upload_session_log';
    public const FRESNS_CMD_CHECK_CODE = 'fresns_cmd_check_code';

    // Command Word: Account
    public const FRESNS_CMD_ACCOUNT_REGISTER = 'fresns_cmd_account_register';
    public const FRESNS_CMD_ACCOUNT_LOGIN = 'fresns_cmd_account_login';
    public const FRESNS_CMD_ACCOUNT_DETAIL = 'fresns_cmd_account_detail';
    public const FRESNS_CMD_CREATE_SESSION_TOKEN = 'fresns_cmd_create_session_token';
    public const FRESNS_CMD_VERIFY_SESSION_TOKEN = 'fresns_cmd_verify_session_token';

    // Command Word: Content
    public const FRESNS_CMD_DIRECT_RELEASE_CONTENT = 'fresns_cmd_direct_release_content';
    public const FRESNS_CMD_DELETE_CONTENT = 'fresns_cmd_delete_content'; //Logical Deletion

    // Command word callback mapping
    const FRESNS_CMD_HANDLE_MAP = [
        self::FRESNS_CMD_DEFAULT => 'defaultHandler',
        // Basis
        self::FRESNS_CMD_VERIFY_SIGN => 'verifySignHandler',
        self::FRESNS_CMD_UPLOAD_SESSION_LOG => 'uploadSessionLogHandler',
        self::FRESNS_CMD_CHECK_CODE => 'checkCodeHandler',
        // Account
        self::FRESNS_CMD_ACCOUNT_REGISTER => 'accountRegisterHandler',
        self::FRESNS_CMD_ACCOUNT_LOGIN => 'accountLoginHandler',
        self::FRESNS_CMD_ACCOUNT_DETAIL => 'accountDetailHandler',
        self::FRESNS_CMD_CREATE_SESSION_TOKEN => 'createSessionTokenHandler',
        self::FRESNS_CMD_VERIFY_SESSION_TOKEN => 'verifySessionTokenHandler',
        // Content
        self::FRESNS_CMD_DIRECT_RELEASE_CONTENT => 'directReleaseContentHandler',
        self::FRESNS_CMD_DELETE_CONTENT => 'deleteContentHandler',
    ];

    // Verify Sign
    public function verifySignHandlerRule()
    {
        $rule = [
            'platform' => 'required',
            'appId' => 'required',
            'timestamp' => 'required',
            'sign' => 'required',
        ];

        return $rule;
    }

    // Upload log
    public function uploadSessionLogHandlerRule()
    {
        $rule = [
            'platform' => 'required',
            'version' => 'required',
            'langTag' => 'required',
            'objectName' => 'required',
            'objectAction' => 'required',
            'objectResult' => 'required',
            'deviceInfo' => 'json',
            'moreJson' => 'json',
        ];

        return $rule;
    }

    // Verify the verification code
    public function checkCodeHandlerRule()
    {
        $request = request();
        $rule = [
            'type' => 'required|in:1,2',
            'verifyCode' => 'required',
            'account' => 'required',
        ];

        return $rule;
    }

    // Account: Register
    public function accountRegisterHandlerRule()
    {
        $rule = [
            'type' => 'required|in:1,2,3',
            'nickname' => 'required',
        ];

        return $rule;
    }

    // Account: Login
    public function accountLoginHandlerRule()
    {
        $rule = [
            'type' => 'required|in:1,2',
            'account' => 'required',
        ];

        return $rule;
    }

    // Account: Detail
    public function accountDetailHandlerRule()
    {
        $rule = [
            'aid' => 'required',
        ];

        return $rule;
    }

    // Account: Creating Token
    public function createSessionTokenHandlerRule()
    {
        $rule = [
            'platform' => 'required',
            'aid' => 'required',
        ];

        return $rule;
    }

    // Account: Verify Token
    public function verifySessionTokenHandlerRule()
    {
        $rule = [
            'platform' => 'required',
            'aid' => 'required',
            'token' => 'required',
        ];

        return $rule;
    }

    // Content: Publish
    public function directReleaseContentHandlerRule()
    {
        $rule = [
            'type' => 'required|in:1,2',
            'logId' => 'required',
        ];

        return $rule;
    }

    // Content: Delete
    public function deleteContentHandlerRule()
    {
        $rule = [
            'type' => 'required | in:1,2',
            'content' => 'required',
        ];

        return $rule;
    }
}
