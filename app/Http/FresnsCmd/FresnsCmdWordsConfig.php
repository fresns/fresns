<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsCmd;

use App\Http\Center\Base\BasePluginConfig;
use Illuminate\Validation\Rule;

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
    public const FRESNS_CMD_SEND_CODE = 'fresns_cmd_send_code';
    public const FRESNS_CMD_CHECKED_CODE = 'fresns_cmd_checked_code';
    public const FRESNS_CMD_SEND_EMAIL = 'fresns_cmd_send_email';
    public const FRESNS_CMD_SEND_SMS = 'fresns_cmd_send_sms';
    public const FRESNS_CMD_SEND_IOS = 'fresns_cmd_send_ios';
    public const FRESNS_CMD_SEND_ANDROID = 'fresns_cmd_send_android';
    public const FRESNS_CMD_SEND_WECHAT = 'fresns_cmd_send_wechat';

    // Command Word: User
    public const FRESNS_CMD_USER_REGISTER = 'fresns_cmd_user_register';
    public const FRESNS_CMD_USER_LOGIN = 'fresns_cmd_user_login';
    public const FRESNS_CMD_USER_DETAIL = 'fresns_cmd_user_detail';
    public const FRESNS_CMD_CREATE_SESSION_TOKEN = 'fresns_cmd_create_session_token';
    public const FRESNS_CMD_VERIFY_SESSION_TOKEN = 'fresns_cmd_verify_session_token';
    public const FRESNS_CMD_WALLET_INCREASE = 'fresns_cmd_wallet_increase';
    public const FRESNS_CMD_WALLET_DECREASE = 'fresns_cmd_wallet_decrease';

    // Command Word: File
    public const FRESNS_CMD_GET_UPLOAD_TOKEN = 'fresns_cmd_get_upload_token';
    public const FRESNS_CMD_UPLOAD_FILE = 'fresns_cmd_upload_file';
    public const FRESNS_CMD_ANTI_LINK_IMAGE = 'fresns_cmd_anti_link_image';
    public const FRESNS_CMD_ANTI_LINK_VIDEO = 'fresns_cmd_anti_link_video';
    public const FRESNS_CMD_ANTI_LINK_AUDIO = 'fresns_cmd_anti_link_audio';
    public const FRESNS_CMD_ANTI_LINK_DOC = 'fresns_cmd_anti_link_doc';
    public const FRESNS_CMD_PHYSICAL_DELETION_FILE = 'fresns_cmd_physical_deletion_file';
    public const FRESNS_CMD_PHYSICAL_DELETION_TEMP_FILE = 'fresns_cmd_physical_deletion_temp_file';

    // Command Word: Content
    public const FRESNS_CMD_DIRECT_RELEASE_CONTENT = 'fresns_cmd_direct_release_content';
    public const FRESNS_CMD_DELETE_CONTENT = 'fresns_cmd_delete_content'; //Logical Deletion

    // Command word callback mapping
    const FRESNS_CMD_HANDLE_MAP = [
        self::FRESNS_CMD_DEFAULT => 'defaultHandler',
        // Basis
        self::FRESNS_CMD_VERIFY_SIGN => 'verifySignHandler',
        self::FRESNS_CMD_UPLOAD_SESSION_LOG => 'uploadSessionLogHandler',
        self::FRESNS_CMD_SEND_CODE => 'sendCodeHandler',
        self::FRESNS_CMD_CHECKED_CODE => 'checkedCodeHandler',
        self::FRESNS_CMD_SEND_EMAIL => 'sendEmailHandler',
        self::FRESNS_CMD_SEND_SMS => 'sendSmsHandler',
        self::FRESNS_CMD_SEND_IOS => 'sendIosHandler',
        self::FRESNS_CMD_SEND_ANDROID => 'sendAndriodHandler',
        self::FRESNS_CMD_SEND_WECHAT => 'sendWeChatHandler',
        // User
        self::FRESNS_CMD_USER_REGISTER => 'userRegisterHandler',
        self::FRESNS_CMD_USER_LOGIN => 'userLoginHandler',
        self::FRESNS_CMD_USER_DETAIL => 'userDetailHandler',
        self::FRESNS_CMD_CREATE_SESSION_TOKEN => 'createSessionTokenHandler',
        self::FRESNS_CMD_VERIFY_SESSION_TOKEN => 'verifySessionTokenHandler',
        self::FRESNS_CMD_WALLET_INCREASE => 'walletIncreaseHandler',
        self::FRESNS_CMD_WALLET_DECREASE => 'walletDecreaseHandler',
        // File
        self::FRESNS_CMD_GET_UPLOAD_TOKEN => 'getUploadTokenHandler',
        self::FRESNS_CMD_UPLOAD_FILE => 'uploadFileHandler',
        self::FRESNS_CMD_ANTI_LINK_IMAGE => 'antiLinkImageHandler',
        self::FRESNS_CMD_ANTI_LINK_VIDEO => 'antiLinkVideoHandler',
        self::FRESNS_CMD_ANTI_LINK_AUDIO => 'antiLinkAudioHandler',
        self::FRESNS_CMD_ANTI_LINK_DOC => 'antiLinkDocHandler',
        self::FRESNS_CMD_PHYSICAL_DELETION_FILE => 'physicalDeletionFileHandler',
        self::FRESNS_CMD_PHYSICAL_DELETION_TEMP_FILE => 'physicalDeletionTempFileHandler',
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
            'versionInt' => 'required',
            'langTag' => 'required',
            'objectName' => 'required',
            'objectAction' => 'required',
            'objectResult' => 'required',
            'deviceInfo' => 'json',
            'moreJson' => 'json',
        ];

        return $rule;
    }

    // Send verification code
    public function sendCodeHandlerRule()
    {
        $request = request();
        $rule = [
            'type' => 'required|in:1,2',
            'templateId' => 'required',
            'account' => 'required',
            'langTag' => 'required',
        ];

        return $rule;
    }

    // Verify the verification code
    public function checkedCodeHandlerRule()
    {
        $request = request();
        $rule = [
            'type' => 'required|in:1,2',
            'verifyCode' => 'required',
            'account' => 'required',
        ];

        return $rule;
    }

    // Send email
    public function sendEmailHandlerRule()
    {
        $rule = [
            'email' => 'required',
            'title' => 'required',
            'content' => 'required',
        ];

        return $rule;
    }

    // Send sms
    public function sendSmsHandlerRule()
    {
        $rule = [
            'countryCode' => 'required',
            'phoneNumber' => 'required',
            'templateCode' => 'required',
            'templateParam' => 'json',
        ];

        return $rule;
    }

    // Send ios push
    public function sendIosHandlerRule()
    {
        $rule = [
            'mid' => 'required',
            'template' => 'required',
            'coverFileUrl' => 'required',
            'title' => 'required',
            'content' => 'required',
            'time' => 'required',
            'linkType' => 'required',
            'linkUrl' => 'required',
        ];

        return $rule;
    }

    // Send android push
    public function sendAndriodHandlerRule()
    {
        $rule = [
            'mid' => 'required',
            'template' => 'required',
            'coverFileUrl' => 'required',
            'title' => 'required',
            'content' => 'required',
            'time' => 'required',
            'linkType' => 'required',
            'linkUrl' => 'required',
        ];

        return $rule;
    }

    // Send wechat push
    public function sendWeChatHandlerRule()
    {
        $rule = [
            'mid' => 'required',
            'template' => 'required',
            'channel' => 'required|in:1,2',
            'coverFileUrl' => 'required',
            'title' => 'required',
            'content' => 'required',
            'time' => 'required',
            'linkType' => 'required',
            'linkUrl' => 'required',
        ];

        return $rule;
    }

    // User: Register
    public function userRegisterHandlerRule()
    {
        $rule = [
            'type' => 'required|in:1,2,3',
            'nickname' => 'required',
        ];

        return $rule;
    }

    // User: Login
    public function userLoginHandlerRule()
    {
        $rule = [
            'type' => 'required|in:1,2',
            'account' => 'required',
        ];

        return $rule;
    }

    // User: Detail
    public function userDetailHandlerRule()
    {
        $rule = [
            'uid' => 'required',
        ];

        return $rule;
    }

    // User: Creating Token
    public function createSessionTokenHandlerRule()
    {
        $rule = [
            'platform' => 'required',
            'uid' => 'required',
        ];

        return $rule;
    }

    // User: Verify Token
    public function verifySessionTokenHandlerRule()
    {
        $rule = [
            'platform' => 'required',
            'uid' => 'required',
            'token' => 'required',
        ];

        return $rule;
    }

    // User: Wallet (increase)
    public function walletIncreaseHandlerRule()
    {
        $rule = [
            'type' => 'required|in:1,2,3',
            'uid' => 'required',
            'amount' => 'required|numeric',
            'transactionAmount' => 'required|numeric',
            'systemFee' => 'required|numeric',
            'originName' => 'required',
        ];

        return $rule;
    }

    // User: Wallet (decrease)
    public function walletDecreaseHandlerRule()
    {
        $rule = [
            'type' => 'required|in:4,5,6',
            'uid' => 'required',
            'amount' => 'required|numeric',
            'transactionAmount' => 'required|numeric',
            'systemFee' => 'required|numeric',
            'originName' => 'required',
        ];

        return $rule;
    }

    // File: Get upload token
    public function getUploadTokenHandlerRule()
    {
        $rule = [
            'type' => 'required|in:1,2,3,4',
            'scene' => 'required|numeric',
        ];

        return $rule;
    }

    // File: Upload file
    public function uploadFileHandlerRule()
    {
        $rule = [
            'type' => 'required|in:1,2,3,4',
            'tableType' => 'required',
            'tableName' => 'required',
            'tableField' => 'required',
            'mode' => 'required|in:1,2',
        ];

        return $rule;
    }

    // File: anti hotlinking (image)
    public function antiLinkImageHandlerRule()
    {
        $rule = [
            'fid' => 'required',
        ];

        return $rule;
    }

    // File: anti hotlinking (video)
    public function antiLinkVideoHandlerRule()
    {
        $rule = [
            'fid' => 'required',
        ];

        return $rule;
    }

    // File: anti hotlinking (audio)
    public function antiLinkAudioHandlerRule()
    {
        $rule = [
            'fid' => 'required',
        ];

        return $rule;
    }

    // File: anti hotlinking (doc)
    public function antiLinkDocHandlerRule()
    {
        $rule = [
            'fid' => 'required',
        ];

        return $rule;
    }

    // File: Physical deletion file by fid
    public function physicalDeletionFileHandlerRule()
    {
        $rule = [
            'fid' => 'required',
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
