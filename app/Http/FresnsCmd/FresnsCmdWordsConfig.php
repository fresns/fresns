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
    public const PLG_CMD_DEFAULT = 'plg_cmd_default';

    // Command Word: Basis
    public const PLG_CMD_VERIFY_SIGN = 'plg_cmd_verify_sign';
    public const PLG_CMD_UPLOAD_SESSION_LOG = 'plg_cmd_upload_session_log';
    public const PLG_CMD_SEND_CODE = 'plg_cmd_send_code';
    public const PLG_CMD_CHECKED_CODE = 'plg_cmd_checked_code';
    public const PLG_CMD_SEND_EMAIL = 'plg_cmd_send_email';
    public const PLG_CMD_SEND_SMS = 'plg_cmd_send_sms';
    public const PLG_CMD_SEND_IOS = 'plg_cmd_send_ios';
    public const PLG_CMD_SEND_ANDROID = 'plg_cmd_send_android';
    public const PLG_CMD_SEND_WECHAT = 'plg_cmd_send_wechat';

    // Command Word: User
    public const PLG_CMD_USER_REGISTER = 'plg_cmd_user_register';
    public const PLG_CMD_USER_LOGIN = 'plg_cmd_user_login';
    public const PLG_CMD_USER_DETAIL = 'plg_cmd_user_detail';
    public const PLG_CMD_CREATE_SESSION_TOKEN = 'plg_cmd_create_session_token';
    public const PLG_CMD_VERIFY_SESSION_TOKEN = 'plg_cmd_verify_session_token';
    public const PLG_CMD_WALLET_INCREASE = 'plg_cmd_wallet_increase';
    public const PLG_CMD_WALLET_DECREASE = 'plg_cmd_wallet_decrease';

    // Command Word: File
    public const PLG_CMD_GET_UPLOAD_TOKEN = 'plg_cmd_get_upload_token';
    public const PLG_CMD_UPLOAD_FILE = 'plg_cmd_upload_file';
    public const PLG_CMD_ANTI_LINK_IMAGE = 'plg_cmd_anti_link_image';
    public const PLG_CMD_ANTI_LINK_VIDEO = 'plg_cmd_anti_link_video';
    public const PLG_CMD_ANTI_LINK_AUDIO = 'plg_cmd_anti_link_audio';
    public const PLG_CMD_ANTI_LINK_DOC = 'plg_cmd_anti_link_doc';
    public const PLG_CMD_PHYSICAL_DELETION_FILE = 'plg_cmd_physical_deletion_file';

    // Command Word: Content
    public const PLG_CMD_DIRECT_RELEASE_CONTENT = 'plg_cmd_direct_release_content';
    public const PLG_CMD_DELETE_CONTENT = 'plg_cmd_delete_content';

    // Command word callback mapping
    const PLG_CMD_HANDLE_MAP = [
        self::PLG_CMD_DEFAULT => 'defaultHandler',
        // Basis
        self::PLG_CMD_VERIFY_SIGN => 'verifySignHandler',
        self::PLG_CMD_UPLOAD_SESSION_LOG => 'uploadSessionLogHandler',
        self::PLG_CMD_SEND_CODE => 'sendCodeHandler',
        self::PLG_CMD_CHECKED_CODE => 'checkedCodeHandler',
        self::PLG_CMD_SEND_EMAIL => 'sendEmailHandler',
        self::PLG_CMD_SEND_SMS => 'sendSmsHandler',
        self::PLG_CMD_SEND_IOS => 'sendIosHandler',
        self::PLG_CMD_SEND_ANDROID => 'sendAndriodHandler',
        self::PLG_CMD_SEND_WECHAT => 'sendWeChatHandler',
        // User
        self::PLG_CMD_USER_REGISTER => 'userRegisterHandler',
        self::PLG_CMD_USER_LOGIN => 'userLoginHandler',
        self::PLG_CMD_USER_DETAIL => 'userDetailHandler',
        self::PLG_CMD_CREATE_SESSION_TOKEN => 'createSessionTokenHandler',
        self::PLG_CMD_VERIFY_SESSION_TOKEN => 'verifySessionTokenHandler',
        self::PLG_CMD_WALLET_INCREASE => 'walletIncreaseHandler',
        self::PLG_CMD_WALLET_DECREASE => 'walletDecreaseHandler',
        // File
        self::PLG_CMD_GET_UPLOAD_TOKEN => 'getUploadTokenHandler',
        self::PLG_CMD_UPLOAD_FILE => 'uploadFileHandler',
        self::PLG_CMD_ANTI_LINK_IMAGE => 'antiLinkImageHandler',
        self::PLG_CMD_ANTI_LINK_VIDEO => 'antiLinkVideoHandler',
        self::PLG_CMD_ANTI_LINK_AUDIO => 'antiLinkAudioHandler',
        self::PLG_CMD_ANTI_LINK_DOC => 'antiLinkDocHandler',
        self::PLG_CMD_PHYSICAL_DELETION_FILE => 'physicalDeletionFileHandler',
        // Content
        self::PLG_CMD_DIRECT_RELEASE_CONTENT => 'directReleaseContentHandler',
        self::PLG_CMD_DELETE_CONTENT => 'deleteContentHandler',
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
            'transactionFsount' => 'required|numeric',
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
            'transactionFsount' => 'required|numeric',
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
