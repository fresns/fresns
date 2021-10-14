<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\Center\Common;

class ErrorCodeService
{
    const CODE_OK = 0;

    // Extensions
    const PLUGINS_CONFIG_ERROR = 30000;
    const PLUGINS_CLASS_ERROR = 30001;
    const PLUGINS_TIMEOUT_ERROR = 30002;
    const PLUGINS_IS_ENABLE_ERROR = 30003;
    const PLUGINS_PARAM_ERROR = 30004;
    const PLUGINS_HANDLE_ERROR = 30005;
    const CODE_PARAM_ERROR = 30006;
    const DATA_EXCEPTION_ERROR = 30007;
    const HELPER_EXCEPTION_ERROR = 30008;
    const VERIFY_CODE_CHECK_ERROR = 30009;
    const PRIVATE_MODE_ERROR = 30010;
    const CALLBACK_ERROR = 30011;
    const CALLBACK_UUID_ERROR = 30012;
    const CALLBACK_TIME_ERROR = 30013;
    const CALLBACK_STATUS_ERROR = 30014;

    // Header
    const HEADER_ERROR = 30100;
    const HEADER_SIGN_ERROR = 30101;
    const HEADER_SIGN_EXPIRED = 30102;
    const HEADER_INFO_ERROR = 30103;
    const HEADER_PLATFORM_ERROR = 30104;
    const HEADER_APP_ID_ERROR = 30105;
    const HEADER_KEY_ERROR = 30106;
    const UID_REQUIRED_ERROR = 30107;
    const MID_REQUIRED_ERROR = 30108;
    const TOKEN_REQUIRED_ERROR = 30109;
    const DEVICE_INFO_REQUIRED_ERROR = 30110;
    const USER_CHECK_ERROR = 30111;
    const MEMBER_CHECK_ERROR = 30112;
    const USER_TOKEN_ERROR = 30113;
    const MEMBER_TOKEN_ERROR = 30114;
    const TOKEN_IS_ENABLE_ERROR = 30115;
    const DEVICE_INFO_ERROR = 30116;

    // User
    const REGISTER_EMAIL_ERROR = 30200;
    const REGISTER_PHONE_ERROR = 30201;
    const REGISTER_USER_ERROR = 30202;
    const PASSWORD_LENGTH_ERROR = 30203;
    const PASSWORD_NUMBER_ERROR = 30204;
    const PASSWORD_LOWERCASE_ERROR = 30205;
    const PASSWORD_CAPITAL_ERROR = 30206;
    const PASSWORD_SYMBOL_ERROR = 30207;

    const EMAIL_ERROR = 30208;
    const EMAIL_REGEX_ERROR = 30209;
    const EMAIL_EXIST_ERROR = 30210;
    const EMAIL_BAND_ERROR = 30211;
    const PHONE_ERROR = 30212;
    const PHONE_REGEX_ERROR = 30213;
    const PHONE_EXIST_ERROR = 30214;
    const PHONE_BAND_ERROR = 30215;
    const COUNTRY_CODE_ERROR = 30216;
    const CODE_TEMPLATE_ERROR = 30217;
    const CONNECT_TOKEN_ERROR = 30218;

    const ACCOUNT_IS_EMPTY_ERROR = 30219;
    const ACCOUNT_CHECK_ERROR = 30220;
    const ACCOUNT_PASSWORD_INVALID = 30221;
    const ACCOUNT_ERROR = 30222;
    const ACCOUNT_COUNT_ERROR = 30223;

    const USER_ERROR = 30224;
    const USER_IS_ENABLE_ERROR = 30225;
    const USER_WALLETS_ERROR = 30226;
    const USER_BALANCE_ERROR = 30227;
    const BALANCE_CLOSING_BALANCE_ERROR = 30228;
    const TO_USER_WALLETS_ERROR = 30229;
    const TO_BALANCE_CLOSING_BALANCE_ERROR = 30230;

    // Member
    const MEMBER_FAIL = 30300;
    const MEMBER_ERROR = 30301;
    const MEMBER_IS_ENABLE_ERROR = 30302;
    const MEMBER_PASSWORD_INVALID = 30303;
    const MEMBER_EXPIRED_ERROR = 30304;
    const MEMBER_NO_PERMISSION = 30305;
    const MEMBER_NAME_ERROR = 30306;
    const UPDATE_TIME_ERROR = 30307;
    const DISABLE_NAME_ERROR = 30308;

    // Member Mark
    const MARK_NOT_ENABLE = 30309;
    const MARK_FOLLOW_ERROR = 30310;
    const MARK_REPEAT_ERROR = 30311;

    // Member Role
    const ROLE_NO_CONFIG_ERROR = 30400;
    const ROLE_NO_PERMISSION = 30401;
    const ROLE_NO_PERMISSION_BROWSE = 30402;
    const ROLE_NO_PERMISSION_PUBLISH = 30403;
    const ROLE_PUBLISH_LIMIT = 30404;
    const ROLE_PUBLISH_EMAIL_VERIFY = 30405;
    const ROLE_PUBLISH_PHONE_VERIFY = 30406;
    const ROLE_PUBLISH_PROVE_VERIFY = 30407;
    const ROLE_NO_PERMISSION_UPLOAD_IMAGE = 30408;
    const ROLE_NO_PERMISSION_UPLOAD_VIDEO = 30409;
    const ROLE_NO_PERMISSION_UPLOAD_AUDIO = 30410;
    const ROLE_NO_PERMISSION_UPLOAD_DOC = 30411;
    const ROLE_UPLOAD_FILES_SIZE_ERROR = 30412;
    const ROLE_DIALOG_ERROR = 30413;
    const ROLE_DOWNLOAD_ERROR = 30414;

    // Dialog
    const DIALOG_ERROR = 30500;
    const DIALOG_MESSAGE_ERROR = 30501;
    const SEND_ME_ERROR = 30502;
    const FILE_OR_TEXT_ERROR = 30503;
    const DIALOG_LIMIT_2_ERROR = 30504;
    const DIALOG_LIMIT_3_ERROR = 30505;
    const DIALOG_WORD_ERROR = 30506;
    const DIALOG_OR_MESSAGE_ERROR = 30507;
    const DELETE_NOTIFY_ERROR = 30508;

    // Group Configs
    const GROUP_MARK_FOLLOW_ERROR = 30600;
    const GROUP_TYPE_ERROR = 30601;
    const GROUP_POST_ALLOW_ERROR = 30602;
    const GROUP_COMMENTS_ALLOW_ERROR = 30603;

    // Publish Configs
    const PUBLISH_EMAIL_VERIFY_ERROR = 30700;
    const PUBLISH_PHONE_VERIFY_ERROR = 30701;
    const PUBLISH_PROVE_VERIFY_ERROR = 30702;
    const PUBLISH_LIMIT_ERROR = 30703;
    const POSTS_EDIT_ERROR = 30704;
    const COMMENTS_EDIT_ERROR = 30705;
    const EDIT_STICKY_ERROR = 30706;
    const EDIT_TIME_ERROR = 30707;
    const EDIT_ESSENCE_ERROR = 30708;
    const UPLOAD_FILES_SUFFIX_ERROR = 30709;
    const POST_BROWSE_ERROR = 30710;

    // Main Content
    const GROUP_EXIST_ERROR = 30800;
    const HASHTAG_EXIST_ERROR = 30801;
    const POST_EXIST_ERROR = 30802;
    const COMMENT_EXIST_ERROR = 30803;
    const POST_LOG_EXIST_ERROR = 30804;
    const COMMENT_LOG_EXIST_ERROR = 30805;
    const POST_APPEND_ERROR = 30806;
    const COMMENT_APPEND_ERROR = 30807;
    const FILE_EXIST_ERROR = 30808;
    const EXTEND_EXIST_ERROR = 30809;
    const DELETE_CONTENT_ERROR = 30810;
    const DELETE_POST_ERROR = 30811;
    const DELETE_COMMENT_ERROR = 30812;
    const DELETE_FILE_ERROR = 30813;
    const DELETE_EXTEND_ERROR = 30814;

    // Editor
    const POST_STATE_2_ERROR = 30815;
    const POST_STATE_3_ERROR = 30816;
    const COMMENT_STATE_2_ERROR = 30817;
    const COMMENT_STATE_3_ERROR = 30818;
    const POST_SUBMIT_STATE_2_ERROR = 30819;
    const POST_SUBMIT_STATE_3_ERROR = 30820;
    const COMMENT_SUBMIT_STATE_2_ERROR = 30821;
    const COMMENT_SUBMIT_STATE_3_ERROR = 30822;
    const POST_REMOKE_ERROR = 30823;
    const COMMENT_REMOKE_ERROR = 30824;
    const CONTENT_AUTHOR_ERROR = 30825;
    const COMMENT_CREATE_ERROR = 30826;

    // Editor Check Parameters
    const MEMBER_LIST_JSON_ERROR = 30900;
    const COMMENT_SET_JSON_ERROR = 30901;
    const ALLOW_JSON_ERROR = 30902;
    const LOCATION_JSON_ERROR = 30903;
    const FILES_JSON_ERROR = 30904;
    const EXTENDS_JSON_ERROR = 30905;
    const EXTENDS_JSON_EID_ERROR = 30906;
    const FILE_INFO_JSON_ERROR = 30907;
    const COMMENT_PID_ERROR = 30908;
    const COMMENT_PID_EXIST_ERROR = 30909;
    const TITLE_ERROR = 30910;
    const CONTENT_STOP_WORDS_ERROR = 30911;
    const CONTENT_CHECK_PARAMS_ERROR = 30912;
    const CONTENT_TYPES_ERROR = 30913;
    const CONTENT_COUNT_ERROR = 30914;

    // Console Error Message
    const SETTING_ERROR = 40000;
    const SAVE_ERROR = 40001;
    const DELETE_ERROR = 40002;
    const LANGUAGE_SETTING_ERROR = 40003;
    const BACKEND_PATH_ERROR = 40004;
    const DELETE_ADMIN_ERROR = 40005;
    const KEY_NAME_ERROR = 40006;
    const KEY_PLATFORM_ERROR = 40007;
    const KEY_PLUGIN_ERROR = 40008;

    // Console Manage Extensions
    const UNINSTALL_EXTENSION_ERROR = 40100;
    const PLUGIN_UNIKEY_ERROR = 40101;
    const FOLDER_NAME_EMPTY_ERROR = 40102;
    const EXTENSION_DOWMLOAD_ERROR = 40103;

    private static $CODE_MSG_MAP = [
        self::CODE_OK                           => 'ok',

        // Extensions
        self::PLUGINS_CONFIG_ERROR              => 'No service provider configured',
        self::PLUGINS_CLASS_ERROR               => 'The service provider not exist',
        self::PLUGINS_TIMEOUT_ERROR             => 'No response from the service provider',
        self::PLUGINS_IS_ENABLE_ERROR           => 'The service provider not enabled',
        self::PLUGINS_PARAM_ERROR               => 'Service provider config parameter is empty',
        self::PLUGINS_HANDLE_ERROR              => 'Service provider processing failed',
        self::CODE_PARAM_ERROR                  => 'Parameter error',
        self::DATA_EXCEPTION_ERROR              => 'Abnormal data: failed to be queried or data duplicated.',
        self::HELPER_EXCEPTION_ERROR            => 'Abnormal execution: file lost or wrong record',
        self::VERIFY_CODE_CHECK_ERROR           => 'Verification code incorrect or expired',
        self::PRIVATE_MODE_ERROR                => 'Request for the interface is forbidden under private mode',
        self::CALLBACK_ERROR                    => 'Callback error',
        self::CALLBACK_UUID_ERROR               => 'Wrong UUID or record not exist',
        self::CALLBACK_TIME_ERROR               => 'Record expired and invalid',
        self::CALLBACK_STATUS_ERROR             => 'Record used. Please try again.',

        // Header
        self::HEADER_ERROR                      => 'Header error',
        self::HEADER_SIGN_ERROR                 => 'Signature error',
        self::HEADER_SIGN_EXPIRED               => 'Signature expired',
        self::HEADER_INFO_ERROR                 => 'The information input is wrong',
        self::HEADER_PLATFORM_ERROR             => 'Platform ID not exist',
        self::HEADER_APP_ID_ERROR               => 'App ID not exist',
        self::HEADER_KEY_ERROR                  => 'The key does not have the right to request for the interface',
        self::UID_REQUIRED_ERROR                => 'UID Required',
        self::MID_REQUIRED_ERROR                => 'MID Required',
        self::TOKEN_REQUIRED_ERROR              => 'Token Required',
        self::DEVICE_INFO_REQUIRED_ERROR        => 'Device Info Required',
        self::USER_CHECK_ERROR                  => 'Wrong user or record not exist',
        self::MEMBER_CHECK_ERROR                => 'Wrong member or record not exist',
        self::USER_TOKEN_ERROR                  => 'User token error',
        self::MEMBER_TOKEN_ERROR                => 'Member token error',
        self::TOKEN_IS_ENABLE_ERROR             => 'The token not enabled',
        self::DEVICE_INFO_ERROR                 => 'Wrong format of device information',

        // User
        self::REGISTER_EMAIL_ERROR              => 'Registration with E-mail not supported',
        self::REGISTER_PHONE_ERROR              => 'Registration with mobile phone number not supported',
        self::REGISTER_USER_ERROR               => 'The user has registered',
        self::PASSWORD_LENGTH_ERROR             => 'Password length incorrect',
        self::PASSWORD_NUMBER_ERROR             => 'Password should contain numbers',
        self::PASSWORD_LOWERCASE_ERROR          => 'Password should contain lowercase letters',
        self::PASSWORD_CAPITAL_ERROR            => 'Password should contain uppercase numbers',
        self::PASSWORD_SYMBOL_ERROR             => 'Password should contain symbols',

        self::EMAIL_ERROR                       => 'E-mail registered',
        self::EMAIL_REGEX_ERROR                 => 'E-mail format incorrect',
        self::EMAIL_EXIST_ERROR                 => 'E-mail not exist',
        self::EMAIL_BAND_ERROR                  => 'E-mail bound',
        self::PHONE_ERROR                       => 'Phone number registered',
        self::PHONE_REGEX_ERROR                 => 'Phone number format incorrect',
        self::PHONE_EXIST_ERROR                 => 'Phone number not exist',
        self::PHONE_BAND_ERROR                  => 'Phone bound',
        self::COUNTRY_CODE_ERROR                => 'International area code error',
        self::CODE_TEMPLATE_ERROR               => 'Verification code template unavailable or not exist',
        self::CONNECT_TOKEN_ERROR               => 'Connect token error',

        self::ACCOUNT_IS_EMPTY_ERROR            => 'Account cannot be empty',
        self::ACCOUNT_CHECK_ERROR               => 'Wrong account or record not exist',
        self::ACCOUNT_PASSWORD_INVALID          => 'Incorrect account password',
        self::ACCOUNT_ERROR                     => 'Incorrect account or wrong password',
        self::ACCOUNT_COUNT_ERROR               => 'The error has exceeded the system limit. Please log in again 1 hour later',

        self::USER_ERROR                        => 'The user has been logged out',
        self::USER_IS_ENABLE_ERROR              => 'Current user has been banned',
        self::USER_WALLETS_ERROR                => 'User wallet not exist',
        self::USER_BALANCE_ERROR                => 'Wallet balance is not allowed to make payment',
        self::BALANCE_CLOSING_BALANCE_ERROR     => 'The closing balance not match with the wallet limit',
        self::TO_USER_WALLETS_ERROR             => 'The counterparty\'s wallet not exist',
        self::TO_BALANCE_CLOSING_BALANCE_ERROR  => 'The closing balance of the counterparty does not match with the wallet limit',

        // Member
        self::MEMBER_FAIL                       => 'Current member not exist or not belong to the current user',
        self::MEMBER_ERROR                      => 'The member has been logged out',
        self::MEMBER_IS_ENABLE_ERROR            => 'Current member has been banned',
        self::MEMBER_PASSWORD_INVALID           => 'Incorrect password',
        self::MEMBER_EXPIRED_ERROR              => 'The member has expired and has no right to use the function',
        self::MEMBER_NO_PERMISSION              => 'Current member has no right to request',
        self::MEMBER_NAME_ERROR                 => 'Member names should all be different',
        self::UPDATE_TIME_ERROR                 => 'Could only be modified once within the specified number of days',
        self::DISABLE_NAME_ERROR                => 'The name contains stop words',

        // Member Mark
        self::MARK_NOT_ENABLE                   => 'The operating function not enabled',
        self::MARK_FOLLOW_ERROR                 => 'Operation against oneself not allowed',
        self::MARK_REPEAT_ERROR                 => 'Repeated operation not allowed',

        // Member Role
        self::ROLE_NO_CONFIG_ERROR              => 'Current role not configured with permissions. Please contact the administrator to confirm.',
        self::ROLE_NO_PERMISSION                => 'Current role has no right to make request',
        self::ROLE_NO_PERMISSION_BROWSE         => 'Current role has no right to browse',
        self::ROLE_NO_PERMISSION_PUBLISH        => 'Current role has no right to publish content',
        self::ROLE_PUBLISH_LIMIT                => 'There is a time limit for the current role to publish content. Please try again within specific time',
        self::ROLE_PUBLISH_EMAIL_VERIFY         => 'Current role has to have an e-mail bound before publishing content',
        self::ROLE_PUBLISH_PHONE_VERIFY         => 'Current role has to have a mobile phone number bound before publishing content',
        self::ROLE_PUBLISH_PROVE_VERIFY         => 'Real-name verification is required for current role to publish content',
        self::ROLE_NO_PERMISSION_UPLOAD_IMAGE   => 'Current role has no right to upload images',
        self::ROLE_NO_PERMISSION_UPLOAD_VIDEO   => 'Current role has no right to upload videos',
        self::ROLE_NO_PERMISSION_UPLOAD_AUDIO   => 'Current role has no right to upload audios',
        self::ROLE_NO_PERMISSION_UPLOAD_DOC     => 'Current role has no right to upload files',
        self::ROLE_UPLOAD_FILES_SIZE_ERROR      => 'File size exceeded the limit for current role',
        self::ROLE_DIALOG_ERROR                 => 'Current role has no private message permission',
        self::ROLE_DOWNLOAD_ERROR               => 'The current role has reached the upper limit of today download, please download again tomorrow.',

        // Dialog
        self::DIALOG_ERROR                      => 'Abnormal session or the session does not belong to current member',
        self::DIALOG_MESSAGE_ERROR              => 'Message deleted',
        self::SEND_ME_ERROR                     => 'You can not send messages to yourself',
        self::FILE_OR_TEXT_ERROR                => 'Each message should be eighter [file] or [text]',
        self::DIALOG_LIMIT_2_ERROR              => 'The counterparty only allow members it follows to send message to it',
        self::DIALOG_LIMIT_3_ERROR              => 'The counterparty only allow members it follows and verified members to send message to it',
        self::DIALOG_WORD_ERROR                 => 'The message could not be sent for the stop words it contains',
        self::DIALOG_OR_MESSAGE_ERROR           => 'Either session or message could be sent. These two types of message could not be deleted simultaneously',
        self::DELETE_NOTIFY_ERROR               => 'Only your own messages could be deleted.',

        // Group Configs
        self::GROUP_MARK_FOLLOW_ERROR           => 'Only specified operation mode is supported. Operation against this interface is forbidden',
        self::GROUP_TYPE_ERROR                  => 'Publication of content not allowed under the group classification',
        self::GROUP_POST_ALLOW_ERROR            => 'Current member does not have the post permission of the group',
        self::GROUP_COMMENTS_ALLOW_ERROR        => 'Current member does not have the comment permission of the group.',

        // Publish Configs
        self::PUBLISH_EMAIL_VERIFY_ERROR        => 'Please have your e-mail bound before publishing content',
        self::PUBLISH_PHONE_VERIFY_ERROR        => 'Please have your mobile phone number bound before publishing content',
        self::PUBLISH_PROVE_VERIFY_ERROR        => 'Please go through the real-name verification process before publishing content',
        self::PUBLISH_LIMIT_ERROR               => 'The system has time limit for content publishing. Please try again within specified time',
        self::POSTS_EDIT_ERROR                  => 'Post editing not allowed',
        self::COMMENTS_EDIT_ERROR               => 'Comment editing not allowed',
        self::EDIT_STICKY_ERROR                 => 'Editing not allowed for top posts',
        self::EDIT_TIME_ERROR                   => 'Editable time expired',
        self::EDIT_ESSENCE_ERROR                => 'Editing not allowed for highlighted posts',
        self::UPLOAD_FILES_SUFFIX_ERROR         => 'This type of file can not be uploaded',
        self::POST_BROWSE_ERROR                 => 'The content could not be accessed without authorization',

        // Main Content
        self::GROUP_EXIST_ERROR                 => 'Wrong group or record not exist',
        self::HASHTAG_EXIST_ERROR               => 'Wrong hashtag or record not exist',
        self::POST_EXIST_ERROR                  => 'Wrong post or record not exist',
        self::COMMENT_EXIST_ERROR               => 'Wrong comment or record not exist',
        self::POST_LOG_EXIST_ERROR              => 'Wrong post draft or record not exist',
        self::COMMENT_LOG_EXIST_ERROR           => 'Wrong comment draft or record not exist',
        self::POST_APPEND_ERROR                 => 'Abnormal post. Sub-table record of the post not found',
        self::COMMENT_APPEND_ERROR              => 'Abnormal comment. Sub-table record of the comment not found',
        self::FILE_EXIST_ERROR                  => 'Wrong file or record not exist',
        self::EXTEND_EXIST_ERROR                => 'Wrong extended content or record not exist',
        self::DELETE_CONTENT_ERROR              => 'The content can not be deleted',
        self::DELETE_POST_ERROR                 => 'Failed to delete. Post error or not exist',
        self::DELETE_COMMENT_ERROR              => 'Failed to delete. Comment error or not exist',
        self::DELETE_FILE_ERROR                 => 'The file is being used and can not be deleted',
        self::DELETE_EXTEND_ERROR               => 'The extended content is being used by others and can not be deleted',

        // Editor
        self::POST_STATE_2_ERROR                => 'The post is being reviewed and can not be edited',
        self::POST_STATE_3_ERROR                => 'The post has been published and can not be edited',
        self::COMMENT_STATE_2_ERROR             => 'The comment is being reviewed and can not be edited',
        self::COMMENT_STATE_3_ERROR             => 'The comment has been published and can not be edited',
        self::POST_SUBMIT_STATE_2_ERROR         => 'Posts being reviewed can not be submitted again',
        self::POST_SUBMIT_STATE_3_ERROR         => 'Posts being published can not be submitted again',
        self::COMMENT_SUBMIT_STATE_2_ERROR      => 'Comments being reviewed can not be submitted again',
        self::COMMENT_SUBMIT_STATE_3_ERROR      => 'Comments being published can not be submitted again',
        self::POST_REMOKE_ERROR                 => 'There is no need to withdraw the post, for it is not being reviewed',
        self::COMMENT_REMOKE_ERROR              => 'There is no need to withdraw the comment, for it is not being reviewed',
        self::CONTENT_AUTHOR_ERROR              => 'Operation failed. Please confirm that you are the author',
        self::COMMENT_CREATE_ERROR              => 'Failed to create draft comment. Only first-level comment can create draft',

        // Editor Check Parameters
        self::MEMBER_LIST_JSON_ERROR            => 'memberListJson format error or abnormal data',
        self::COMMENT_SET_JSON_ERROR            => 'commentSetJson format error or abnormal data',
        self::ALLOW_JSON_ERROR                  => 'allowJson format error or abnormal data',
        self::LOCATION_JSON_ERROR               => 'locationJson format error or abnormal data',
        self::FILES_JSON_ERROR                  => 'filesJson format error or abnormal data',
        self::EXTENDS_JSON_ERROR                => 'extendsJson format error or abnormal data',
        self::EXTENDS_JSON_EID_ERROR            => 'eid parameter in extendsJson must be filled in',
        self::FILE_INFO_JSON_ERROR              => 'fileInfo format error or abnormal data',
        self::COMMENT_PID_ERROR                 => 'PID parameter is required for comment posting ',
        self::COMMENT_PID_EXIST_ERROR           => 'Comment failed. Post not found',
        self::TITLE_ERROR                       => 'The title is too long. The upper limit is 255 characters',
        self::CONTENT_STOP_WORDS_ERROR          => 'Stop words contained. Please modify the content and then try again',
        self::CONTENT_CHECK_PARAMS_ERROR        => 'Content, file and extended content could not be empty simultaneously. At least one of the three should have value.',
        self::CONTENT_TYPES_ERROR               => 'Content type parameter is wrong or the number of characters has reached the upper limit',
        self::CONTENT_COUNT_ERROR               => 'Number of words exceeded the limit',

        // Console Error Message
        self::SETTING_ERROR                     => 'Setting error',
        self::SAVE_ERROR                        => 'Save error',
        self::DELETE_ERROR                      => 'Delete error',
        self::LANGUAGE_SETTING_ERROR            => 'Language setting error',
        self::BACKEND_PATH_ERROR                => 'Entrance name occupied',
        self::DELETE_ADMIN_ERROR                => 'Deleting oneself is not allowed',
        self::KEY_NAME_ERROR                    => 'Key name is required',
        self::KEY_PLATFORM_ERROR                => 'Please select key application platforms',
        self::KEY_PLUGIN_ERROR                  => 'Please select associated plugins',

        // Console Manage Extensions
        self::PLUGIN_UNIKEY_ERROR               => 'UniKey error',
        self::UNINSTALL_EXTENSION_ERROR         => 'Uninstall only after being disabled',
        self::FOLDER_NAME_EMPTY_ERROR           => 'Folder name can not be empty',
        self::EXTENSION_DOWMLOAD_ERROR          => 'Failed to download the extension installation package',
    ];

    // Get Message
    public static function getMsg($code, $data = [])
    {
        if (! isset(self::$CODE_MSG_MAP[$code])) {
            return 'Plugin Check Exception';
        }

        // Specifying information about parameter errors
        try {
            if ($code == self::CODE_PARAM_ERROR) {
                $data = (array) $data;
                foreach ($data as $key => $messageBag) {
                    foreach ($messageBag as $k => $infoArr) {
                        if (count($infoArr) > 0) {
                            return $infoArr[0];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            LogService::warning('get error msg missing ', $data);
        }

        return self::$CODE_MSG_MAP[$code];
    }
}
