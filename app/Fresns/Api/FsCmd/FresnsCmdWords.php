<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsCmd;

use App\Fresns\Api\Center\Base\BasePlugin;
use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\Center\Common\LogService;
use App\Fresns\Api\Center\Common\ValidateService;
use App\Fresns\Api\Center\Helper\CmdRpcHelper;
use App\Fresns\Api\Center\Helper\PluginHelper;
use App\Fresns\Api\Center\Scene\FileSceneConfig;
use App\Fresns\Api\Center\Scene\FileSceneService;
use App\Fresns\Api\FsDb\FresnsAccountConnects\FresnsAccountConnects;
use App\Fresns\Api\FsDb\FresnsAccountConnects\FresnsAccountConnectsConfig;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccounts;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccountsConfig;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccountsService;
use App\Fresns\Api\FsDb\FresnsAccountWalletLogs\FresnsAccountWalletLogs;
use App\Fresns\Api\FsDb\FresnsAccountWallets\FresnsAccountWallets;
use App\Fresns\Api\FsDb\FresnsCommentAppends\FresnsCommentAppendsConfig;
use App\Fresns\Api\FsDb\FresnsCommentLogs\FresnsCommentLogs;
use App\Fresns\Api\FsDb\FresnsCommentLogs\FresnsCommentLogsConfig;
use App\Fresns\Api\FsDb\FresnsComments\FresnsComments;
use App\Fresns\Api\FsDb\FresnsComments\FresnsCommentsConfig;
use App\Fresns\Api\FsDb\FresnsComments\FresnsCommentsService;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigs;
use App\Fresns\Api\FsDb\FresnsDomainLinks\FresnsDomainLinksConfig;
use App\Fresns\Api\FsDb\FresnsDomains\FresnsDomains;
use App\Fresns\Api\FsDb\FresnsExtendLinkeds\FresnsExtendLinkedsConfig;
use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtendsConfig;
use App\Fresns\Api\FsDb\FresnsFileAppends\FresnsFileAppends;
use App\Fresns\Api\FsDb\FresnsFileAppends\FresnsFileAppendsConfig;
use App\Fresns\Api\FsDb\FresnsFiles\FresnsFiles;
use App\Fresns\Api\FsDb\FresnsFiles\FresnsFilesConfig;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroups;
use App\Fresns\Api\FsDb\FresnsHashtagLinkeds\FresnsHashtagLinkedsConfig;
use App\Fresns\Api\FsDb\FresnsHashtags\FresnsHashtags;
use App\Fresns\Api\FsDb\FresnsLanguages\FresnsLanguagesConfig;
use App\Fresns\Api\FsDb\FresnsMentions\FresnsMentionsConfig;
use App\Fresns\Api\FsDb\FresnsPostAllows\FresnsPostAllowsConfig;
use App\Fresns\Api\FsDb\FresnsPostAppends\FresnsPostAppendsConfig;
use App\Fresns\Api\FsDb\FresnsPostLogs\FresnsPostLogs;
use App\Fresns\Api\FsDb\FresnsPostLogs\FresnsPostLogsConfig;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPosts;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPostsConfig;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPostsService;
use App\Fresns\Api\FsDb\FresnsSessionKeys\FresnsSessionKeys;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogs;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogsConfig;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogsService;
use App\Fresns\Api\FsDb\FresnsSessionTokens\FresnsSessionTokensConfig;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRoles;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use App\Fresns\Api\FsDb\FresnsUserStats\FresnsUserStats;
use App\Fresns\Api\FsDb\FresnsVerifyCodes\FresnsVerifyCodes;
use App\Fresns\Api\Helpers\ApiCommonHelper;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\SignHelper;
use App\Fresns\Api\Helpers\StrHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

class FresnsCmdWords extends BasePlugin
{
    // Constructors
    public function __construct()
    {
        $this->pluginConfig = new FresnsCmdWordsConfig();
        $this->pluginCmdHandlerMap = FresnsCmdWordsConfig::FRESNS_CMD_HANDLE_MAP;
    }

    // Get Status Code
    public function getCodeMap()
    {
        return FresnsCmdWordsConfig::CODE_MAP;
    }

    // Send Verification Code
    protected function sendCodeHandler($input)
    {
        // Send
        $type = $input['type'];
        if ($type == 1) {
            $pluginUniKey = ApiConfigHelper::getConfigByItemKey('send_email_service');
        } else {
            $pluginUniKey = ApiConfigHelper::getConfigByItemKey('send_sms_service');
        }
        if (empty($pluginUniKey)) {
            return $this->pluginError(ErrorCodeService::PLUGINS_CONFIG_ERROR);
        }
        // Start Handle
        $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
        if (empty($pluginClass)) {
            LogService::error('Plugin class not found');

            return $this->pluginError(ErrorCodeService::PLUGINS_CLASS_ERROR);
        }
        LogService::info('Start Handle: ', $input);
        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_SEND_CODE;
        // Preparation parameters
        $account = $input['account'];
        $templateId = $input['templateId'];
        $langTag = $input['langTag'];
        $countryCode = $input['countryCode'];
        // Email
        if ($type == 1) {
            $input = [
                'type'   => $type,
                'account' => $account,
                'templateId' => $templateId,
                'langTag' => $langTag,
            ];
        // SMS
        } else {
            $input = [
                'type'   => $type,
                'account' => $account,
                'templateId' => $templateId,
                'countryCode' => $countryCode,
                'langTag' => $langTag,
            ];
        }
        $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);

        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            return $this->pluginError($resp['code']);
        }

        LogService::info('Handle Done: ', $input);

        return $this->pluginSuccess($resp['output']);
    }

    // Verify the verification code
    public function checkCodeHandler($input)
    {
        $type = $input['type'];
        $account = $input['account'];
        $verifyCode = $input['verifyCode'];
        $countryCode = $input['countryCode'];
        // type: 1.email / 2.sms
        if ($type == 1) {
            $where = [
                'type' => $type,
                'account' => $account,
                'code' => $verifyCode,
                'is_enable' => 1,
            ];
        } else {
            $where = [
                'type' => $type,
                'account' => $countryCode.$account,
                'code' => $verifyCode,
                'is_enable' => 1,
            ];
        }
        // Is the verification code valid
        $verifyInfo = FresnsVerifyCodes::where($where)->where('expired_at', '>', date('Y-m-d H:i:s'))->first();
        if ($verifyInfo) {
            FresnsVerifyCodes::where('id', $verifyInfo['id'])->update(['is_enable' => 0]);

            return $this->pluginSuccess();
        } else {
            return $this->pluginError(ErrorCodeService::VERIFY_CODE_CHECK_ERROR);
        }
    }

    // Submit content into the main form (post and comment)
    public function directReleaseContentHandler($input)
    {
        $type = $input['type'];
        $logId = $input['logId'];
        $sessionLogsId = $input['sessionLogsId'];
        $commentCid = $input['commentCid'] ?? 0;
        $FresnsPostsService = new FresnsPostsService();
        $fresnsCommentService = new FresnsCommentsService();
        switch ($type) {
            case 1:
                $result = $FresnsPostsService->releaseByDraft($logId, $sessionLogsId);
                // $postId = FresnsPostLogs::find($logId);
                // $cmd = FresnsSubPluginConfig::FRESNS_CMD_SUB_ACTIVE_COMMAND_WORD;
                // $input = [
                //     'tableName' => 'posts',
                //     'insertId' => $postId['post_id'],
                //     'commandWord' => 'fresns_cmd_direct_release_content',
                // ];
                // $resp = CmdRpcHelper::call(FresnsSubPlugin::class, $cmd, $input);
                break;
            case 2:
                $result = $fresnsCommentService->releaseByDraft($logId, $commentCid, $sessionLogsId);
                // $commentInfo = FresnsCommentLogs::find($logId);
                // $cmd = FresnsSubPluginConfig::FRESNS_CMD_SUB_ACTIVE_COMMAND_WORD;
                // $input = [
                //     'tableName' => 'comments',
                //     'insertId' => $commentInfo['comment_id'],
                //     'commandWord' => 'fresns_cmd_direct_release_content',
                // ];
                // $resp = CmdRpcHelper::call(FresnsSubPlugin::class, $cmd, $input);
                break;
        }

        return $this->pluginSuccess();
    }

    // Send email
    public function sendEmailHandler($input)
    {
        $email = $input['email'];
        $title = $input['title'];
        $content = $input['content'];
        $pluginUniKey = ApiConfigHelper::getConfigByItemKey('send_email_service');
        if (empty($pluginUniKey)) {
            LogService::error('No outgoing service provider configured');

            return $this->pluginError(ErrorCodeService::PLUGINS_CONFIG_ERROR);
        }
        // Command
        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_SEND_EMAIL;
        $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
        if (empty($pluginClass)) {
            LogService::error('Plugin class not found');

            return $this->pluginError(ErrorCodeService::PLUGINS_CLASS_ERROR);
        }
        $input = [
            'email' => $email,
            'title' => $title,
            'content' => $content,
        ];
        $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            return $this->pluginError($resp['code']);
        }

        return $this->pluginSuccess($resp);
    }

    // Send sms
    public function sendSmsHandler($input)
    {
        $countryCode = $input['countryCode'];
        $phoneNumber = $input['phoneNumber'];
        $signName = $input['signName'];
        $templateCode = $input['templateCode'];
        $templateParam = $input['templateParam'];
        $pluginUniKey = ApiConfigHelper::getConfigByItemKey('send_sms_service');
        if (empty($pluginUniKey)) {
            LogService::error('No outgoing service provider configured');

            return $this->pluginError(ErrorCodeService::PLUGINS_CONFIG_ERROR);
        }
        // Command
        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_SEND_SMS;
        $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
        if (empty($pluginClass)) {
            LogService::error('Plugin class not found');

            return $this->pluginError(ErrorCodeService::PLUGINS_CLASS_ERROR);
        }
        $input = [
            'countryCode' => $countryCode,
            'phoneNumber' => $phoneNumber,
            'signName' => $signName,
            'templateCode' => $templateCode,
            'templateParam' => $templateParam,
        ];
        $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            return $this->pluginError($resp['code']);
        }

        return $this->pluginSuccess($resp);
    }

    // Send wechat push
    public function sendWeChatHandler($input)
    {
        $uid = $input['uid'];
        $template = $input['template'];
        $channel = $input['channel'];
        $coverFileUrl = $input['coverFileUrl'];
        $title = $input['title'];
        $content = $input['content'];
        $time = $input['time'];
        $linkType = $input['linkType'];
        $linkUrl = $input['linkUrl'];
        $pluginUniKey = ApiConfigHelper::getConfigByItemKey('send_wechat_service');
        if (empty($pluginUniKey)) {
            LogService::error('No outgoing service provider configured');

            return $this->pluginError(ErrorCodeService::PLUGINS_CONFIG_ERROR);
        }
        // Command
        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_SEND_WECHAT;
        $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
        if (empty($pluginClass)) {
            LogService::error('Plugin class not found');

            return $this->pluginError(ErrorCodeService::PLUGINS_CLASS_ERROR);
        }
        $input = [
            'uid' => $uid,
            'template' => $template,
            'channel' => $channel,
            'coverFileUrl' => $coverFileUrl,
            'title' => $title,
            'content' => $content,
            'time' => $time,
            'linkType' => $linkType,
            'linkUrl' => $linkUrl,
        ];
        $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            return $this->pluginError($resp['code']);
        }

        return $this->pluginSuccess($resp);
    }

    // Send ios push
    public function sendIosHandler($input)
    {
        $uid = $input['uid'];
        $template = $input['template'];
        $coverFileUrl = $input['coverFileUrl'];
        $title = $input['title'];
        $content = $input['content'];
        $time = $input['time'];
        $linkType = $input['linkType'];
        $linkUrl = $input['linkUrl'];
        $pluginUniKey = ApiConfigHelper::getConfigByItemKey('send_ios_service');
        if (empty($pluginUniKey)) {
            LogService::error('No outgoing service provider configured');

            return $this->pluginError(ErrorCodeService::PLUGINS_CONFIG_ERROR);
        }
        // Command
        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_SEND_IOS;
        $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
        if (empty($pluginClass)) {
            LogService::error('Plugin class not found');

            return $this->pluginError(ErrorCodeService::PLUGINS_CLASS_ERROR);
        }
        $input = [
            'uid' => $uid,
            'template' => $template,
            'cover_file_url' => $coverFileUrl,
            'title' => $title,
            'content' => $content,
            'time' => $time,
            'link_type' => $linkType,
            'linkUrl' => $linkUrl,
        ];
        $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            return $this->pluginError($resp['code']);
        }

        return $this->pluginSuccess($resp);
    }

    // Send android push
    public function sendAndriodHandler($input)
    {
        $phone = $input['uid'];
        $template = $input['template'];
        $coverFileUrl = $input['coverFileUrl'];
        $title = $input['title'];
        $content = $input['content'];
        $time = $input['time'];
        $linkType = $input['linkType'];
        $linkUrl = $input['linkUrl'];
        $pluginUniKey = ApiConfigHelper::getConfigByItemKey('send_android_service');
        if (empty($pluginUniKey)) {
            LogService::error('No outgoing service provider configured');

            return $this->pluginError(ErrorCodeService::PLUGINS_CONFIG_ERROR);
        }
        // Command
        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_SEND_ANDROID;
        $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
        if (empty($pluginClass)) {
            LogService::error('Plugin class not found');

            return $this->pluginError(ErrorCodeService::PLUGINS_CLASS_ERROR);
        }
        $input = [
            'phone' => $phone,
            'template' => $template,
            'coverFileUrl' => $coverFileUrl,
            'title' => $title,
            'content' => $content,
            'time' => $time,
            'linkType' => $linkType,
            'linkUrl' => $linkUrl,
        ];
        $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            return $this->pluginError($resp['code']);
        }

        return $this->pluginSuccess($resp);
    }

    // Creating Token
    public function createSessionTokenHandler($input)
    {
        $uri = Request::getRequestUri();

        $accountId = $input['aid'];
        $userId = $input['uid'] ?? null;
        $platform = $input['platform'];

        $expiredTime = $input['expiredTime'] ?? null;
        if ($accountId) {
            $accountId = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('aid', $accountId)->value('id');
        }
        if ($userId) {
            $userId = DB::table(FresnsUsersConfig::CFG_TABLE)->where('uid', $userId)->value('id');
        }
        if (empty($userId)) {
            $tokenCount = DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('account_id', $accountId)->where('user_id', null)->where('platform_id', $platform)->count();
            $token = StrHelper::createToken();

            if ($tokenCount > 0) {
                DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('account_id', $accountId)->where('user_id', null)->where('platform_id', $platform)->delete();
            }
            $input = [];
            $input['platform_id'] = $platform;
            $input['account_id'] = $accountId;
            $input['token'] = $token;
            if ($expiredTime) {
                $input['expired_at'] = $expiredTime ?? null;
            }
            DB::table(FresnsSessionTokensConfig::CFG_TABLE)->insert($input);
        } else {
            $sessionToken = DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('account_id', $accountId)->where('user_id', $userId)->where('platform_id', $platform)->first();
            $token = StrHelper::createToken();
            if ($sessionToken) {
                DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('account_id', $accountId)->where('user_id', $userId)->where('platform_id', $platform)->delete();
            }
            $input = [];
            $input['token'] = $token;
            $input['platform_id'] = $platform;
            $input['account_id'] = $accountId;
            $input['user_id'] = $userId;
            if ($expiredTime) {
                $input['expired_at'] = $expiredTime ?? null;
            }

            DB::table(FresnsSessionTokensConfig::CFG_TABLE)->insert($input);
        }

        $data = [];
        $data['token'] = $token;
        $data['tokenExpiredTime'] = $expiredTime;

        return $this->pluginSuccess($data);
    }

    // Verify Token
    public function verifySessionTokenHandler($input)
    {
        $accountId = $input['aid'];
        $userId = $input['uid'] ?? null;
        $platform = $input['platform'];
        $token = $input['token'];
        $time = date('Y-m-d H:i:s', time());

        if ($accountId) {
            $accountId = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('aid', $accountId)->value('id');
        }
        if ($userId) {
            $userId = DB::table(FresnsUsersConfig::CFG_TABLE)->where('uid', $userId)->value('id');
        }

        if (empty($userId)) {
            // Verify Token
            $aidToken = DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('platform_id', $platform)->where('account_id', $accountId)->where('user_id', null)->first();

            if (empty($aidToken)) {
                return $this->pluginError(ErrorCodeService::ACCOUNT_TOKEN_ERROR);
            }

            if (! empty($aidToken->expired_at)) {
                if ($aidToken->expired_at < $time) {
                    return $this->pluginError(ErrorCodeService::ACCOUNT_TOKEN_ERROR);
                }
            }

            if ($aidToken->token != $token) {
                return $this->pluginError(ErrorCodeService::ACCOUNT_TOKEN_ERROR);
            }
        } else {
            // Verify Token
            $uidToken = DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('platform_id', $platform)->where('account_id', $accountId)->where('user_id', $userId)->first();
            if (empty($uidToken)) {
                return $this->pluginError(ErrorCodeService::USER_TOKEN_ERROR);
            }

            if (! empty($uidToken->expired_at)) {
                if ($uidToken->expired_at < $time) {
                    return $this->pluginError(ErrorCodeService::USER_TOKEN_ERROR);
                }
            }

            if ($uidToken->token != $token) {
                return $this->pluginError(ErrorCodeService::USER_TOKEN_ERROR);
            }
        }

        return $this->pluginSuccess();
    }

    // Upload log
    public function uploadSessionLogHandler($input)
    {
        $platform = $input['platform'];
        $version = $input['version'];
        $objectName = $input['objectName'];
        $objectAction = $input['objectAction'];
        $objectResult = $input['objectResult'];
        $objectType = $input['objectType'] ?? 1;
        $langTag = $input['langTag'] ?? null;
        $objectOrderId = $input['objectOrderId'] ?? null;
        $deviceInfo = $input['deviceInfo'] ?? null;
        $accountId = $input['aid'] ?? null;
        $userId = $input['uid'] ?? null;
        $moreJson = $input['moreJson'] ?? null;

        if ($accountId) {
            $accountId = FresnsAccounts::where('aid', $accountId)->value('id');
        }
        if ($userId) {
            $userId = FresnsUsers::where('uid', $userId)->value('id');
        }
        $input = [
            'platform_id' => $platform,
            'version' => $version,
            'lang_tag' => $langTag,
            'object_name' => $objectName,
            'object_action' => $objectAction,
            'object_result' => $objectResult,
            'object_order_id' => $objectOrderId,
            'device_info' => $deviceInfo,
            'account_id' => $accountId,
            'user_id' => $userId,
            'more_json' => $moreJson,
            'object_type' => $objectType,
        ];

        FresnsSessionLogs::insert($input);

        return $this->pluginSuccess();
    }

    // Get upload token
    public function getUploadTokenHandler($input)
    {
        $type = $input['type'];
        $scene = $input['scene'];
        switch ($type) {
            case 1:
                $unikey = ApiConfigHelper::getConfigByItemKey('image_service');
                break;
            case 2:
                $unikey = ApiConfigHelper::getConfigByItemKey('video_service');
                break;
            case 3:
                $unikey = ApiConfigHelper::getConfigByItemKey('audio_service');
                break;
            default:
                $unikey = ApiConfigHelper::getConfigByItemKey('document_service');
                break;
        }
        $pluginUniKey = $unikey;

        $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
        if (empty($pluginClass)) {
            LogService::error('Plugin Class Not Found');

            return $this->pluginError(ErrorCodeService::PLUGINS_CONFIG_ERROR);
        }

        $isPlugin = PluginHelper::pluginCanUse($pluginUniKey);
        if ($isPlugin == false) {
            LogService::error('Plugin Class Not Found');

            return $this->pluginError(ErrorCodeService::PLUGINS_IS_ENABLE_ERROR);
        }

        $file['file_type'] = $type;
        $paramsExist = false;
        if ($file['file_type'] == FileSceneConfig::FILE_TYPE_1) {
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['image_secret_id', 'image_secret_key', 'image_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['image_secret_id', 'image_secret_key', 'image_bucket_domain']);
        }
        if ($file['file_type'] == FileSceneConfig::FILE_TYPE_2) {
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['video_secret_id', 'video_secret_key', 'video_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['video_secret_id', 'video_secret_key', 'video_bucket_domain']);
        }
        if ($file['file_type'] == FileSceneConfig::FILE_TYPE_3) {
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['audio_secret_id', 'audio_secret_key', 'audio_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['audio_secret_id', 'audio_secret_key', 'audio_bucket_domain']);
        }
        if ($file['file_type'] == FileSceneConfig::FILE_TYPE_4) {
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['document_secret_id', 'document_secret_key', 'document_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['document_secret_id', 'document_secret_key', 'document_bucket_domain']);
        }
        if ($paramsExist == false) {
            LogService::error('Unconfigured Plugin');

            return $this->pluginError(ErrorCodeService::PLUGINS_PARAM_ERROR);
        }

        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_GET_UPLOAD_TOKEN;
        $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            return $this->pluginError($resp['code']);
        }
        $output = $resp['output'];

        $data['storageId'] = $output['storageId'] ?? 1;
        $data['token'] = $output['token'] ?? null;
        $data['expireTime'] = $output['expireTime'] ?? null;

        return $this->pluginSuccess($data);
    }

    // Upload file
    public function uploadFileHandler($input)
    {
        $t1 = time();
        $type = $input['type'];
        $tableType = $input['tableType'];
        $tableName = $input['tableName'];
        $tableColumn = $input['tableColumn'];
        $tableId = $input['tableId'];
        $tableKey = $input['tableKey'];
        $mode = $input['mode'];
        $uploadFile = $input['file'];
        $fileInfo = $input['fileInfo'] ?? null;
        $platformId = $input['platform'];
        $accountId = $input['aid'] ?? null;
        $userId = $input['uid'] ?? null;

        if ($tableId) {
            if (Schema::hasColumn($tableName, 'uuid')) {
                $tableId = DB::table($tableName)->where('uuid', $tableId)->value('id');
            }
        }
        if ($accountId) {
            $accountId = FresnsAccounts::where('aid', $accountId)->value('id');
        }
        if ($userId) {
            $userId = FresnsUsers::where('uid', $userId)->value('id');
        }

        if ($mode == 2) {
            if (empty($tableId) && empty($tableKey)) {
                $input = [
                    'Parameter error: ' => 'fill in at least one of tableId or tableKey',
                ];

                return $this->pluginError(ErrorCodeService::CODE_PARAM_ERROR);
            }
        }

        $data = [];

        switch ($type) {
            case 1:
                $unikey = ApiConfigHelper::getConfigByItemKey('image_service');
                break;
            case 2:
                $unikey = ApiConfigHelper::getConfigByItemKey('video_service');
                break;
            case 3:
                $unikey = ApiConfigHelper::getConfigByItemKey('audio_service');
                break;
            default:
                $unikey = ApiConfigHelper::getConfigByItemKey('document_service');
                break;
        }
        $pluginUniKey = $unikey;

        $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
        if (empty($pluginClass)) {
            LogService::error('Plugin Class Not Found');

            return $this->pluginError(ErrorCodeService::PLUGINS_CONFIG_ERROR);
        }

        $isPlugin = PluginHelper::pluginCanUse($pluginUniKey);
        if ($isPlugin == false) {
            LogService::error('Plugin Class Not Found');

            return $this->pluginError(ErrorCodeService::PLUGINS_IS_ENABLE_ERROR);
        }

        $file['file_type'] = $type;
        $paramsExist = false;
        if ($file['file_type'] == FileSceneConfig::FILE_TYPE_1) {
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['image_secret_id', 'image_secret_key', 'image_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['image_secret_id', 'image_secret_key', 'image_bucket_domain']);
        }
        if ($file['file_type'] == FileSceneConfig::FILE_TYPE_2) {
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['video_secret_id', 'video_secret_key', 'video_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['video_secret_id', 'video_secret_key', 'video_bucket_domain']);
        }

        if ($file['file_type'] == FileSceneConfig::FILE_TYPE_3) {
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['audio_secret_id', 'audio_secret_key', 'audio_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['audio_secret_id', 'audio_secret_key', 'audio_bucket_domain']);
        }
        if ($file['file_type'] == FileSceneConfig::FILE_TYPE_4) {
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['document_secret_id', 'document_secret_key', 'document_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['document_secret_id', 'document_secret_key', 'document_bucket_domain']);
        }
        if ($paramsExist == false) {
            LogService::error('Unconfigured Plugin');

            return $this->pluginError(ErrorCodeService::PLUGINS_PARAM_ERROR);
        }

        if ($mode == 1) {
            // Confirm Directory
            $options['file_type'] = $type;
            $options['table_type'] = $tableType;
            $storePath = FileSceneService::getEditorPath($options);

            if (! $storePath) {
                return $this->pluginError(ErrorCodeService::USER_FAIL);
            }

            // Get an instance of UploadFile
            if (empty($uploadFile)) {
                return $this->pluginError(ErrorCodeService::FILE_EXIST_ERROR);
            }
            // Storage
            $path = $uploadFile->store($storePath);
            $basePath = base_path();
            $basePath = $basePath.'/storage/app/';
            $newPath = $storePath.'/'.StrHelper::createToken(8).'.'.$uploadFile->getClientOriginalExtension();
            copy($basePath.$path, $basePath.$newPath);
            unlink($basePath.$path);
            $file['file_name'] = $uploadFile->getClientOriginalName();
            $file['file_extension'] = $uploadFile->getClientOriginalExtension();
            $file['file_path'] = str_replace('public', '', $newPath);
            $file['rank_num'] = 9;
            $file['table_type'] = $tableType;
            $file['table_name'] = $tableName;
            $file['table_column'] = $tableColumn;
            $file['table_id'] = $tableId ?? null;
            $file['table_key'] = $tableKey ?? null;

            LogService::info('File Storage Local Success', $file);
            $t2 = time();

            $file['fid'] = StrHelper::createFsid();
            // Insert
            $retId = FresnsFiles::insertGetId($file);
            // FresnsSubPluginService::addSubTablePluginItem(FresnsFilesConfig::CFG_TABLE, $retId);

            $file['real_path'] = $newPath;
            $input = [
                'file_id' => $retId,
                'file_mime' => $uploadFile->getMimeType(),
                'file_size' => $uploadFile->getSize(),
                'platform_id' => $platformId,
                'transcoding_state' => 1,
                'account_id' => $accountId,
                'user_id' => $userId,
                'image_is_long' => 0,
            ];
            if ($type == 1) {
                $imageSize = getimagesize($uploadFile);
                $input['image_width'] = $imageSize[0] ?? null;
                $input['image_height'] = $imageSize[1] ?? null;
                if (! empty($input['image_width']) >= 700) {
                    if ($input['image_height'] >= $input['image_width'] * 3) {
                        $input['image_is_long'] = 1;
                    }
                }
            }
            $file['file_size'] = $input['file_size'];
            FresnsFileAppends::insert($input);

            LogService::info('Upload local time', ($t2 - $t1));

            $fidArr = [$file['fid']];
            $fileIdArr = [$retId];
        } else {
            $fileInfoArr = json_decode($fileInfo, true);
            $fileIdArr = [];
            $fidArr = [];
            if ($fileInfoArr) {
                foreach ($fileInfoArr as $fileInfo) {
                    $item = [];
                    $item['file_type'] = $type;
                    $item['file_name'] = $fileInfo['name'];
                    $item['file_extension'] = $fileInfo['extension'];
                    $item['file_path'] = $fileInfo['path'];
                    $item['rank_num'] = $fileInfo['rankNum'];
                    $fid = $fileInfo['fid'] ?? StrHelper::createFsid();
                    $item['fid'] = $fid;
                    $item['table_type'] = $tableType;
                    $item['table_name'] = $tableName;
                    $item['table_column'] = $tableColumn;
                    $item['table_id'] = $tableId ?? null;
                    $item['table_key'] = $tableKey ?? null;
                    $fieldId = FresnsFiles::insertGetId($item);
                    // FresnsSubPluginService::addSubTablePluginItem(FresnsFilesConfig::CFG_TABLE, $fieldId);
                    $fileIdArr[] = $fieldId;
                    $fidArr[] = $item['fid'];
                    $append = [];
                    $append['file_id'] = $fieldId;
                    $append['account_id'] = $accountId;
                    $append['user_id'] = $userId;
                    $append['file_original_path'] = $fileInfo['originalPath'] == '' ? null : $fileInfo['originalPath'];
                    $append['file_mime'] = $fileInfo['mime'] == '' ? null : $fileInfo['mime'];
                    $append['file_size'] = $fileInfo['size'] == '' ? null : $fileInfo['size'];
                    $append['file_md5'] = $fileInfo['md5'] == '' ? null : $fileInfo['md5'];
                    $append['file_sha1'] = $fileInfo['sha1'] == '' ? null : $fileInfo['sha1'];
                    $append['image_width'] = $fileInfo['imageWidth'] == '' ? null : $fileInfo['imageWidth'];
                    $append['image_height'] = $fileInfo['imageHeight'] == '' ? null : $fileInfo['imageHeight'];
                    $imageLong = 0;
                    if (! empty($fileInfo['image_width'])) {
                        if ($fileInfo['image_width'] >= 700) {
                            if ($fileInfo['image_height'] >= $fileInfo['image_width'] * 3) {
                                $imageLong = 1;
                            } else {
                                $imageLong = 0;
                            }
                        }
                    }
                    $append['image_is_long'] = $imageLong;
                    $append['video_time'] = $fileInfo['videoTime'] == '' ? null : $fileInfo['videoTime'];
                    $append['video_cover'] = $fileInfo['videoCover'] == '' ? null : $fileInfo['videoCover'];
                    $append['video_gif'] = $fileInfo['videoGif'] == '' ? null : $fileInfo['videoGif'];
                    $append['audio_time'] = $fileInfo['audioTime'] == '' ? null : $fileInfo['audioTime'];
                    $append['platform_id'] = $platformId;
                    $append['transcoding_state'] = $fileInfo['transcodingState'] ?? 2;
                    $append['more_json'] = json_encode($fileInfo['moreJson']);

                    FresnsFileAppends::insert($append);
                }
            }
        }

        if ($pluginClass) {
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_UPLOAD_FILE;
            $input = [];
            $input['fid'] = json_encode($fidArr);
            $input['mode'] = $mode;
            $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                return $this->pluginError($resp['code']);
            }
        }

        $data['files'] = [];

        if ($fileIdArr) {
            $filesArr = FresnsFiles::whereIn('id', $fileIdArr)->get()->toArray();
            foreach ($filesArr as $file) {
                $item = [];
                $fid = $file['fid'];
                $append = FresnsFileAppends::where('file_id', $file['id'])->first();
                $item['fid'] = $file['fid'];
                $item['type'] = $file['file_type'];
                $item['name'] = $file['file_name'];
                $item['extension'] = $file['file_extension'];
                $item['mime'] = $append['file_mime'];
                $item['size'] = $append['file_size'];
                $item['rankNum'] = $file['rank_num'];
                if ($type == 1) {
                    $item['imageWidth'] = $append['image_width'] ?? null;
                    $item['imageHeight'] = $append['image_height'] ?? null;
                    $item['imageLong'] = $append['image_is_long'] ?? 0;
                    $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_IMAGE;
                    $input['fid'] = $fid;
                    $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                    if (CmdRpcHelper::isErrorCmdResp($resp)) {
                        return $this->pluginError($resp['code']);
                    }
                    $output = $resp['output'];
                    $item['imageDefaultUrl'] = $output['imageDefaultUrl'];
                    $item['imageConfigUrl'] = $output['imageConfigUrl'];
                    $item['imageAvatarUrl'] = $output['imageAvatarUrl'];
                    $item['imageRatioUrl'] = $output['imageRatioUrl'];
                    $item['imageSquareUrl'] = $output['imageSquareUrl'];
                    $item['imageBigUrl'] = $output['imageBigUrl'];
                }
                if ($type == 2) {
                    $item['videoTime'] = $append['video_time'] ?? null;
                    $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_VIDEO;
                    $input['fid'] = $fid;
                    $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                    if (CmdRpcHelper::isErrorCmdResp($resp)) {
                        return $this->pluginError($resp['code']);
                    }
                    $output = $resp['output'];
                    $item['videoCover'] = $output['videoCover'];
                    $item['videoGif'] = $output['videoGif'];
                    $item['videoUrl'] = $output['videoUrl'];
                    $item['transcodingState'] = $append['transcoding_state'];
                }
                if ($type == 3) {
                    $item['audioTime'] = $append['audio_time'] ?? null;
                    $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_AUDIO;
                    $input['fid'] = $fid;
                    $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                    if (CmdRpcHelper::isErrorCmdResp($resp)) {
                        return $this->pluginError($resp['code']);
                    }
                    $output = $resp['output'];
                    $item['audioUrl'] = $output['audioUrl'];
                    $item['transcodingState'] = $append['transcoding_state'];
                }
                if ($type == 4) {
                    $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_DOC;
                    $input['fid'] = $fid;
                    $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                    if (CmdRpcHelper::isErrorCmdResp($resp)) {
                        return $this->pluginError($resp['code']);
                    }
                    $output = $resp['output'];
                    $item['docUrl'] = $output['docUrl'];
                }
                $item['moreJson'] = json_decode($append['more_json'], true);
                $data['files'][] = $item;
            }
        }

        return $this->pluginSuccess($data);
    }

    // anti hotlinking (image)
    public function antiLinkImageHandler($input)
    {
        $fid = $input['fid'];
        $files = FresnsFiles::where('fid', $fid)->first();
        if (empty($files)) {
            return $this->pluginError(ErrorCodeService::FILE_EXIST_ERROR);
        }
        $append = FresnsFileAppends::where('file_id', $files['id'])->first();

        $imagesStatus = ApiConfigHelper::getConfigByItemKey('image_url_status');
        $imagesBucketDomain = ApiConfigHelper::getConfigByItemKey('image_bucket_domain');
        $imagesThumbConfig = ApiConfigHelper::getConfigByItemKey('image_thumb_config');
        $imagesThumbAvatar = ApiConfigHelper::getConfigByItemKey('image_thumb_avatar');
        $imagesThumbRatio = ApiConfigHelper::getConfigByItemKey('image_thumb_ratio');
        $imagesThumbSquare = ApiConfigHelper::getConfigByItemKey('image_thumb_square');
        $imagesThumbBig = ApiConfigHelper::getConfigByItemKey('image_thumb_big');

        $imageDefaultUrl = $imagesBucketDomain.$files['file_path'];
        $imageConfigUrl = $imagesBucketDomain.$files['file_path'].$imagesThumbConfig;
        $imageAvatarUrl = $imagesBucketDomain.$files['file_path'].$imagesThumbAvatar;
        $imageRatioUrl = $imagesBucketDomain.$files['file_path'].$imagesThumbRatio;
        $imageSquareUrl = $imagesBucketDomain.$files['file_path'].$imagesThumbSquare;
        $imageBigUrl = $imagesBucketDomain.$files['file_path'].$imagesThumbBig;
        $originalUrl = $imagesBucketDomain.($append['file_original_path'] ?? '');
        if ($imagesStatus == true) {
            $unikey = ApiConfigHelper::getConfigByItemKey('image_service');
            $pluginUniKey = $unikey;

            $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
            if (empty($pluginClass)) {
                LogService::error('Plugin Class Not Found');

                return $this->pluginError(ErrorCodeService::PLUGINS_CONFIG_ERROR);
            }

            $isPlugin = PluginHelper::pluginCanUse($pluginUniKey);
            if ($isPlugin == false) {
                LogService::error('Plugin Class Not Found');

                return $this->pluginError(ErrorCodeService::PLUGINS_IS_ENABLE_ERROR);
            }

            $configMapInDB = FresnsConfigs::whereIn('item_key', ['image_secret_id', 'image_secret_key', 'image_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['image_secret_id', 'image_secret_key', 'image_bucket_domain']);
            if ($paramsExist == false) {
                LogService::error('Unconfigured Plugin');

                return $this->pluginError(ErrorCodeService::PLUGINS_PARAM_ERROR);
            }

            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_IMAGE;
            $input = [];
            $input['fid'] = $fid;
            $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                return $this->pluginError($resp['code']);
            }
            $output = $resp['output'];

            $imageDefaultUrl = $output['imageDefaultUrl'] ?? $imageDefaultUrl;
            $imageConfigUrl = $output['imageConfigUrl'] ?? null;
            $imageAvatarUrl = $output['imageAvatarUrl'] ?? null;
            $imageRatioUrl = $output['imageRatioUrl'] ?? null;
            $imageSquareUrl = $output['imageSquareUrl'] ?? null;
            $imageBigUrl = $output['imageBigUrl'] ?? null;
            $originalUrl = $output['originalUrl'] ?? null;
        } else {
            $imageDefaultUrl = $imageDefaultUrl;
            $imageConfigUrl = $imageConfigUrl;
            $imageAvatarUrl = $imageAvatarUrl;
            $imageRatioUrl = $imageRatioUrl;
            $imageSquareUrl = $imageSquareUrl;
            $imageBigUrl = $imageBigUrl;
            $originalUrl = $originalUrl;
        }

        $item['imageDefaultUrl'] = $imageDefaultUrl;
        $item['imageConfigUrl'] = $imageConfigUrl;
        $item['imageAvatarUrl'] = $imageAvatarUrl;
        $item['imageRatioUrl'] = $imageRatioUrl;
        $item['imageSquareUrl'] = $imageSquareUrl;
        $item['imageBigUrl'] = $imageBigUrl;
        $item['originalUrl'] = $originalUrl;

        return $this->pluginSuccess($item);
    }

    // anti hotlinking (video)
    public function antiLinkVideoHandler($input)
    {
        $fid = $input['fid'];
        $files = FresnsFiles::where('fid', $fid)->first();
        if (empty($files)) {
            return $this->pluginError(ErrorCodeService::FILE_EXIST_ERROR);
        }
        $append = FresnsFileAppends::where('file_id', $files['id'])->first();

        $videosStatus = ApiConfigHelper::getConfigByItemKey('video_url_status');
        $videosBucketDomain = ApiConfigHelper::getConfigByItemKey('video_bucket_domain');

        $videoCover = $videosBucketDomain.$append['video_cover'];
        $videoGif = $videosBucketDomain.$append['video_gif'];
        $videoUrl = $videosBucketDomain.$files['file_path'];
        $originalUrl = $videosBucketDomain.$append['file_original_path'];

        if ($videosStatus == true) {
            $unikey = ApiConfigHelper::getConfigByItemKey('video_service');
            $pluginUniKey = $unikey;

            $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
            if (empty($pluginClass)) {
                LogService::error('Plugin Class Not Found');

                return $this->pluginError(ErrorCodeService::PLUGINS_CONFIG_ERROR);
            }

            $isPlugin = PluginHelper::pluginCanUse($pluginUniKey);
            if ($isPlugin == false) {
                LogService::error('Plugin Class Not Found');

                return $this->pluginError(ErrorCodeService::PLUGINS_IS_ENABLE_ERROR);
            }

            $configMapInDB = FresnsConfigs::whereIn('item_key', ['video_secret_id', 'video_secret_key', 'video_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['video_secret_id', 'video_secret_key', 'video_bucket_domain']);
            if ($paramsExist == false) {
                LogService::error('Unconfigured Plugin');

                return $this->pluginError(ErrorCodeService::PLUGINS_PARAM_ERROR);
            }

            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_VIDEO;
            $input = [];
            $input['fid'] = $fid;
            $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                return $this->pluginError($resp['code']);
            }
            $output = $resp['output'];

            $videoCover = $output['videoCover'] ?? null;
            $videoGif = $output['videoGif'] ?? null;
            $videoUrl = $output['videoUrl'] ?? null;
            $originalUrl = $output['originalUrl'] ?? null;
        } else {
            $videoCover = $videoCover;
            $videoGif = $videoGif;
            $videoUrl = $videoUrl;
            $originalUrl = $originalUrl;
        }

        $item['videoCover'] = $videoCover;
        $item['videoGif'] = $videoGif;
        $item['videoUrl'] = $videoUrl;
        $item['originalUrl'] = $originalUrl;

        return $this->pluginSuccess($item);
    }

    // anti hotlinking (audio)
    public function antiLinkAudioHandler($input)
    {
        $fid = $input['fid'];
        $files = FresnsFiles::where('fid', $fid)->first();
        if (empty($files)) {
            return $this->pluginError(ErrorCodeService::FILE_EXIST_ERROR);
        }
        $append = FresnsFileAppends::where('file_id', $files['id'])->first();

        $audiosStatus = ApiConfigHelper::getConfigByItemKey('audio_url_status');
        $audiosBucketDomain = ApiConfigHelper::getConfigByItemKey('audio_bucket_domain');

        $audioUrl = $audiosBucketDomain.$files['file_path'];
        $originalUrl = $audiosBucketDomain.$append['file_original_path'];

        if ($audiosStatus == true) {
            $unikey = ApiConfigHelper::getConfigByItemKey('audio_service');
            $pluginUniKey = $unikey;

            $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
            if (empty($pluginClass)) {
                LogService::error('Plugin Class Not Found');

                return $this->pluginError(ErrorCodeService::PLUGINS_CONFIG_ERROR);
            }

            $isPlugin = PluginHelper::pluginCanUse($pluginUniKey);
            if ($isPlugin == false) {
                LogService::error('Plugin Class Not Found');

                return $this->pluginError(ErrorCodeService::PLUGINS_IS_ENABLE_ERROR);
            }

            $configMapInDB = FresnsConfigs::whereIn('item_key', ['audio_secret_id', 'audio_secret_key', 'audio_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['audio_secret_id', 'audio_secret_key', 'audio_bucket_domain']);
            if ($paramsExist == false) {
                LogService::error('Unconfigured Plugin');

                return $this->pluginError(ErrorCodeService::PLUGINS_PARAM_ERROR);
            }

            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_AUDIO;
            $input = [];
            $input['fid'] = $fid;
            $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                return $this->pluginError($resp['code']);
            }
            $output = $resp['output'];

            $audioUrl = $output['audioUrl'] ?? null;
            $originalUrl = $output['originalUrl'] ?? null;
        } else {
            $audioUrl = $audioUrl;
            $originalUrl = $originalUrl;
        }

        $item['audioUrl'] = $audioUrl;
        $item['originalUrl'] = $originalUrl;

        return $this->pluginSuccess($item);
    }

    // anti hotlinking (doc)
    public function antiLinkDocHandler($input)
    {
        $fid = $input['fid'];
        $files = FresnsFiles::where('fid', $fid)->first();
        if (empty($files)) {
            return $this->pluginError(ErrorCodeService::FILE_EXIST_ERROR);
        }
        $append = FresnsFileAppends::where('file_id', $files['id'])->first();

        $docsStatus = ApiConfigHelper::getConfigByItemKey('document_url_status');
        $docsBucketDomain = ApiConfigHelper::getConfigByItemKey('document_bucket_domain');

        $docUrl = $docsBucketDomain.$files['file_path'];
        $originalUrl = $docsBucketDomain.$append['file_original_path'];

        if ($docsStatus == true) {
            $unikey = ApiConfigHelper::getConfigByItemKey('document_service');
            $pluginUniKey = $unikey;

            $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
            if (empty($pluginClass)) {
                LogService::error('Plugin Class Not Found');

                return $this->pluginError(ErrorCodeService::PLUGINS_CONFIG_ERROR);
            }

            $isPlugin = PluginHelper::pluginCanUse($pluginUniKey);
            if ($isPlugin == false) {
                LogService::error('Plugin Class Not Found');

                return $this->pluginError(ErrorCodeService::PLUGINS_IS_ENABLE_ERROR);
            }

            $configMapInDB = FresnsConfigs::whereIn('item_key', ['document_secret_id', 'document_secret_key', 'document_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['document_secret_id', 'document_secret_key', 'document_bucket_domain']);
            if ($paramsExist == false) {
                LogService::error('Unconfigured Plugin');

                return $this->pluginError(ErrorCodeService::PLUGINS_PARAM_ERROR);
            }

            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_DOC;
            $input = [];
            $input['fid'] = $fid;
            $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                return $this->pluginError($resp['code']);
            }
            $output = $resp['output'];

            $docUrl = $output['docUrl'] ?? null;
            $originalUrl = $output['originalUrl'] ?? null;
        } else {
            $docUrl = $docUrl;
            $originalUrl = $originalUrl;
        }

        $item['docUrl'] = $docUrl;
        $item['originalUrl'] = $originalUrl;

        return $this->pluginSuccess($item);
    }

    // Physical deletion temp file by fid
    public function physicalDeletionTempFileHandler($input)
    {
        $fid = $input['fid'];
        $files = FresnsFiles::where('fid', $fid)->first();
        if (empty($files)) {
            return $this->pluginError(ErrorCodeService::FILE_EXIST_ERROR);
        }

        $basePath = base_path().'/storage/app/public'.$files['file_path'];

        if (file_exists($basePath)) {
            unlink($basePath);
        }

        return $this->pluginSuccess();
    }

    // Physical deletion file by fid
    public function physicalDeletionFileHandler($input)
    {
        $fid = $input['fid'];
        $files = FresnsFiles::where('fid', $fid)->first();
        if (empty($files)) {
            return $this->pluginError(ErrorCodeService::FILE_EXIST_ERROR);
        }
        $type = $files['file_type'];
        switch ($type) {
            case 1:
                $unikey = ApiConfigHelper::getConfigByItemKey('image_service');
                break;
            case 2:
                $unikey = ApiConfigHelper::getConfigByItemKey('video_service');
                break;
            case 3:
                $unikey = ApiConfigHelper::getConfigByItemKey('audio_service');
                break;
            default:
                $unikey = ApiConfigHelper::getConfigByItemKey('document_service');
                break;
        }
        $pluginUniKey = $unikey;

        $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
        if (empty($pluginClass)) {
            LogService::error('Plugin Class Not Found');

            return $this->pluginError(ErrorCodeService::PLUGINS_CONFIG_ERROR);
        }

        $isPlugin = PluginHelper::pluginCanUse($pluginUniKey);
        if ($isPlugin == false) {
            LogService::error('Plugin Class Not Found');

            return $this->pluginError(ErrorCodeService::PLUGINS_IS_ENABLE_ERROR);
        }

        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_PHYSICAL_DELETION_FILE;
        $input = [];
        $input['fid'] = $fid;
        $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            return $this->pluginError($resp['code']);
        }

        return $this->pluginSuccess();
    }

    /**
     * Delete official content (Logical Deletion)
     * type: 1.post / 2.comment
     * contentId: primary key ID
     * https://fresns.org/extensions/delete.html.
     */
    public function deleteContentHandler($input)
    {
        $type = $input['type'];
        $contentId = $input['content'];
        switch ($type) {
            case 1:
                /*
                 * post
                 * Step 1
                 * delete extend
                 */
                $input = ['linked_type' => 1, 'linked_id' => $contentId];
                $extendsLinksArr = DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where($input)->pluck('extend_id')->toArray();
                // Determine if an extend exists
                if (! empty($extendsLinksArr)) {
                    foreach ($extendsLinksArr as $e) {
                        $extendsLinksInfo = DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('extend_id',
                            $e)->where('linked_type', 1)->where('linked_id', '!=', $contentId)->first();
                        // extend_linkeds: Whether the association is unique.
                        if (empty($extendsLinksInfo)) {
                            // Query whether the extension has attached files
                            $input = [
                                'table_type' => 10,
                                'table_name' => FresnsExtendsConfig::CFG_TABLE,
                                'table_column' => 'id',
                                'table_id' => $e,
                            ];
                            $extendFiles = FresnsFiles::where($input)->get(['id', 'fid', 'file_type'])->toArray();
                            // The queried file ID will be forwarded to the associated plugin with the file type, and the plugin will delete the physical files of the storage service provider.
                            if (! empty($extendFiles)) {
                                foreach ($extendFiles as $file) {
                                    $extendsFileId = $file['fid'];
                                    $extendsFileType = $file['file_type'];
                                    // Plugin handle logic.
                                    $cmd = FresnsCmdWordsConfig::FRESNS_CMD_PHYSICAL_DELETION_FILE;
                                    $input['fid'] = $extendsFileId;
                                    $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                                    // Delete file data records from both "files" + "file_appends" tables.
                                    DB::table(FresnsFileAppendsConfig::CFG_TABLE)->where('file_id', $file['id'])->delete();
                                }
                            }

                            // Delete the language table contents of the extend content
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'title')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'desc_primary')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'desc_secondary')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'btn_name')->where('table_id', $e)->delete();
                            // Delete the associated records in the "extend_linkeds" table
                            DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('extend_id', $e)->where('linked_type', 1)->where('linked_id', '=', $contentId)->delete();
                            // Delete extends extended content records.
                            DB::table(FresnsExtendsConfig::CFG_TABLE)->where('id', $e)->delete();
                        } else {
                            DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('linked_type', 1)->where('linked_id', $contentId)->where('extend_id', $e)->delete();
                        }
                    }
                }

                /*
                 * post
                 * Step 2
                 * Delete attached files
                 * Read the main table "posts > more_json > files" file list, plus all logs of the post "post_logs > files_json" file list, and perform bulk delete.
                 */
                $post = DB::table(FresnsPostsConfig::CFG_TABLE)->where('id', $contentId)->first();
                $postAppend = DB::table(FresnsPostAppendsConfig::CFG_TABLE)->where('post_id', $contentId)->first();
                // Get the post master form file
                $filesFidArr = [];
                if (! empty($post->more_json)) {
                    $postMoreJsonArr = json_decode($post->more_json, true);
                    if (! empty($postMoreJsonArr['files'])) {
                        foreach ($postMoreJsonArr['files'] as $v) {
                            $filesFidArr[] = $v['fid'];
                        }
                    }
                }
                // Get "post_logs" table file information
                $postLogsFiles = DB::table(FresnsPostLogsConfig::CFG_TABLE)->where('post_id',
                    $post->id)->pluck('files_json')->toArray();
                if (! empty($postLogsFiles)) {
                    foreach ($postLogsFiles as $v) {
                        $filesArr = json_decode($v, true);
                        if (! empty($filesArr)) {
                            foreach ($filesArr as $files) {
                                $filesFidArr[] = $files['fid'];
                            }
                        }
                    }
                }
                if ($filesFidArr) {
                    $filesFidArr = array_unique($filesFidArr);
                    $filesIdArr = DB::table(FresnsFilesConfig::CFG_TABLE)->whereIn('fid', $filesFidArr)->pluck('id')->toArray();
                    if ($filesIdArr) {
                        // Delete physical files
                        foreach ($filesIdArr as $v) {
                            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_PHYSICAL_DELETION_FILE;
                            $input['fid'] = $v;
                            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                        }
                        // Delete file data
                        DB::table(FresnsFilesConfig::CFG_TABLE)->whereIn('fid', $filesIdArr)->delete();
                        DB::table(FresnsFileAppendsConfig::CFG_TABLE)->whereIn('file_id', $filesIdArr)->delete();
                    }
                }

                /*
                 * post
                 * Step 3
                 * Remove parsing association
                 * Delete the mentions record.
                 */
                DB::table(FresnsMentionsConfig::CFG_TABLE)->where('linked_type', 1)->where('linked_id', $contentId)->delete();
                // Delete hashtag-related "hashtag_linkeds" records
                // Corresponding hashtag "hashtags > comment_count" field value -1
                $linkedArr = DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 1)->pluck('hashtag_id')->toArray();
                FresnsHashtags::whereIn('id', $linkedArr)->decrement('post_count');
                DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 1)->delete();
                // Delete hyperlinks "domain_links"
                // Corresponding domain "domains > post_count" field value -1
                $domainArr = DB::table(FresnsDomainLinksConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 1)->pluck('domain_id')->toArray();
                FresnsDomains::whereIn('id', $domainArr)->decrement('post_count');
                DB::table(FresnsDomainLinksConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 1)->delete();

                /*
                 * post
                 * Step 4
                 * Delete post affiliation form (language)
                 * Delete the fields "allow_btn_name", "comment_btn_name", and "user_list_name" from the posts table in the languages table.
                 */
                if ($postAppend) {
                    DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsPostsConfig::CFG_TABLE)->where('table_column', 'allow_btn_name')->where('table_id', $postAppend->id)->delete();
                    DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsPostsConfig::CFG_TABLE)->where('table_column', 'comment_btn_name')->where('table_id', $postAppend->id)->delete();
                    DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsPostsConfig::CFG_TABLE)->where('table_column', 'user_list_name')->where('table_id', $postAppend->id)->delete();
                }

                /*
                 * post
                 * Step 5
                 * Delete statistical values
                 */
                $groupPostCount = FresnsGroups::where('id', $post->group_id)->value('post_count');
                if ($groupPostCount > 0) {
                    FresnsGroups::where('id', $post->group_id)->decrement('post_count');
                }
                FresnsConfigs::where('item_key', 'posts_count')->decrement('item_value');

                /*
                 * post
                 * Step 6
                 * Delete all records of the "post_id" in the associated table "post_appends" + "post_allows" + "post_logs".
                 */
                DB::table(FresnsPostAppendsConfig::CFG_TABLE)->where('post_id', $contentId)->delete();
                DB::table(FresnsPostAllowsConfig::CFG_TABLE)->where('post_id', $contentId)->delete();
                DB::table(FresnsPostLogsConfig::CFG_TABLE)->where('post_id', $contentId)->delete();

                /*
                 * post
                 * Step 7
                 * Deletes the row from the "posts" table.
                 */
                DB::table(FresnsPostsConfig::CFG_TABLE)->where('id', $contentId)->delete();

                break;

            default:
                /*
                 * comment
                 * Step 1
                 * delete extend
                 */
                $input = ['linked_type' => 2, 'linked_id' => $contentId];
                $extendsLinksArr = DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where($input)->pluck('extend_id')->toArray();
                // Determine if an extend exists
                if (! empty($extendsLinksArr)) {
                    foreach ($extendsLinksArr as $e) {
                        $extendsLinksInfo = DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('extend_id', $e)->where('linked_type', 2)->where('linked_id', '!=', $contentId)->first();
                        // extend_linkeds: Whether the association is unique.
                        if (empty($extendsLinksInfo)) {
                            // Query whether the extension has attached files
                            $input = [
                                'table_type' => 10,
                                'table_name' => FresnsExtendsConfig::CFG_TABLE,
                                'table_column' => 'id',
                                'table_id' => $e,
                            ];
                            $extendFiles = FresnsFiles::where($input)->get(['id', 'fid', 'file_type'])->toArray();
                            // The queried file ID will be forwarded to the associated plugin with the file type, and the plugin will delete the physical files of the storage service provider.
                            if (! empty($extendFiles)) {
                                foreach ($extendFiles as $file) {
                                    $extendsFileId = $file['fid'];
                                    $extendsFileType = $file['file_type'];
                                    // Plugin handle logic.
                                    $cmd = FresnsCmdWordsConfig::FRESNS_CMD_PHYSICAL_DELETION_FILE;
                                    $input['fid'] = $extendsFileId;
                                    $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);

                                    // Delete file data records from both "files" + "file_appends" tables.
                                    DB::table(FresnsFileAppendsConfig::CFG_TABLE)->where('file_id', $file['id'])->delete();
                                }
                            }

                            // Delete the language table contents of the extend content
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'title')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'desc_primary')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'desc_secondary')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'btn_name')->where('table_id', $e)->delete();
                            // Delete the associated records in the "extend_linkeds" table
                            DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('extend_id', $e)->where('linked_type', 2)->where('linked_id', '=', $contentId)->delete();
                            // Delete extends extended content records.
                            DB::table(FresnsExtendsConfig::CFG_TABLE)->where('id', $e)->delete();
                        } else {
                            DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('linked_type', 2)->where('linked_id', $contentId)->where('extend_id', $e)->delete();
                        }
                    }
                }

                /*
                 * comment
                 * Step 2
                 * Delete attached files
                 */
                $comment = DB::table(FresnsCommentsConfig::CFG_TABLE)->where('id', $contentId)->first();
                $commentAppend = DB::table(FresnsCommentAppendsConfig::CFG_TABLE)->where('comment_id', $contentId)->first();
                // Get the comment master form file
                $filesFidArr = [];
                if (! empty($comment->more_json)) {
                    $commentMoreJsonArr = json_decode($comment->more_json, true);
                    if (! empty($commentMoreJsonArr['files'])) {
                        foreach ($commentMoreJsonArr['files'] as $v) {
                            $filesFidArr[] = $v['fid'];
                        }
                    }
                }
                // Get "comment_logs" table file information
                $commentLogsFiles = DB::table(FresnsCommentLogsConfig::CFG_TABLE)->where('comment_id',
                    $comment->id)->pluck('files_json')->toArray();
                if (! empty($commentLogsFiles)) {
                    foreach ($commentLogsFiles as $v) {
                        $filesArr = json_decode($v, true);
                        if (! empty($filesArr)) {
                            foreach ($filesArr as $files) {
                                $filesFidArr[] = $files['fid'];
                            }
                        }
                    }
                }
                if ($filesFidArr) {
                    $filesFidArr = array_unique($filesFidArr);
                    $filesIdArr = DB::table(FresnsFilesConfig::CFG_TABLE)->whereIn('fid', $filesFidArr)->pluck('id')->toArray();
                    if ($filesFidArr) {
                        // Delete physical files
                        foreach ($filesFidArr as $v) {
                            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_PHYSICAL_DELETION_FILE;
                            $input['fid'] = $v;
                            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                        }
                        // Delete files
                        DB::table(FresnsFilesConfig::CFG_TABLE)->whereIn('id', $filesIdArr)->delete();
                        DB::table(FresnsFileAppendsConfig::CFG_TABLE)->whereIn('file_id', $filesIdArr)->delete();
                    }
                }

                /*
                 * comment
                 * Step 3
                 * Remove parsing association
                 * Delete the mentions record.
                 */
                DB::table(FresnsMentionsConfig::CFG_TABLE)->where('linked_type', 2)->where('linked_id', $contentId)->delete();
                // Delete hashtag-related "hashtag_linkeds" records
                // Corresponding hashtag "hashtags > comment_count" field value -1
                $linkedArr = DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 2)->pluck('hashtag_id')->toArray();
                FresnsHashtags::whereIn('id', $linkedArr)->decrement('comment_count');
                DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 2)->delete();
                // Delete hyperlinks "domain_links"
                // Corresponding domain "domains > post_count" field value -1
                $domainArr = DB::table(FresnsDomainLinksConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 2)->pluck('domain_id')->toArray();
                FresnsDomains::whereIn('id', $domainArr)->decrement('comment_count');
                DB::table(FresnsDomainLinksConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 2)->delete();

                /*
                 * comment
                 * Step 4
                 * Delete post affiliation form (language)
                 */
                FresnsComments::where('id', $comment->parent_id)->decrement('comment_count');
                FresnsComments::where('id', $comment->parent_id)->decrement('comment_like_count', $comment->like_count);
                FresnsPosts::where('id', $comment->post_id)->decrement('comment_count');
                FresnsPosts::where('id', $comment->post_id)->decrement('comment_like_count', $comment->like_count);
                FresnsConfigs::where('item_key', 'comments_count')->decrement('item_value');

                /*
                 * comment
                 * Step 5
                 * Delete all records of the "comment_id" in the "comment_appends" + "comment_logs" table
                 */
                DB::table(FresnsCommentAppendsConfig::CFG_TABLE)->where('comment_id', $contentId)->delete();
                DB::table(FresnsCommentLogsConfig::CFG_TABLE)->where('comment_id', $contentId)->delete();

                /*
                 * comment
                 * Step 6
                 * Deletes the row from the "comments" table.
                 */
                DB::table(FresnsCommentsConfig::CFG_TABLE)->where('id', $contentId)->delete();

                break;
        }

        return $this->pluginSuccess();
    }

    // Verify Sign
    public function verifySignHandler($input)
    {
        $platform = $input['platform'];
        $version = $input['version'] ?? null;
        $appId = $input['appId'];
        $timestamp = $input['timestamp'];
        $sign = $input['sign'];
        $aid = $input['aid'] ?? null;
        $uid = $input['uid'] ?? null;
        $token = $input['token'] ?? null;

        $dataMap['platform'] = $platform;
        if ($version) {
            $dataMap['version'] = $version;
        }
        $dataMap['appId'] = $appId;
        $dataMap['timestamp'] = $timestamp;
        if ($aid) {
            $dataMap['aid'] = $aid;
        }
        if ($uid) {
            $dataMap['uid'] = $uid;
        }
        if ($token) {
            $dataMap['token'] = $token;
        }
        $dataMap['sign'] = $sign;

        // Header Signature Expiration Date
        $min = 5; //Expiration time limit (unit: minutes)
        //Determine the timestamp type
        $timestampNum = strlen($timestamp);
        if ($timestampNum == 10) {
            $now = time();
            $expiredMin = $min * 60;
        } else {
            $now = intval(microtime(true) * 1000);
            $expiredMin = $min * 60 * 1000;
        }

        if ($now - $timestamp > $expiredMin) {
            return $this->pluginError(ErrorCodeService::HEADER_SIGN_EXPIRED);
        }
        LogService::info('Tips: ', $dataMap);
        $signKey = FresnsSessionKeys::where('app_id', $appId)->value('app_secret');

        $checkSignRes = SignHelper::checkSign($dataMap, $signKey);
        if ($checkSignRes !== true) {
            $info = [
                'sign' => $checkSignRes,
            ];

            return $this->pluginError(ErrorCodeService::HEADER_SIGN_ERROR, $info);
        }

        return $this->pluginSuccess();
    }

    // Wallet Trading (increase)
    // Note: When querying the last transaction record with is_enable=1, the default ending balance is 0 if no transaction record is queried.
    public function walletIncreaseHandler($input)
    {
        $type = $input['type'];
        $aid = $input['aid'];
        $uid = $input['uid'] ?? null;
        $amount = $input['amount'];
        $transactionAmount = $input['transactionAmount'];
        $systemFee = $input['systemFee'];
        $originAid = $input['originAid'] ?? null;
        $originUid = $input['originUid'] ?? null;
        $originName = $input['originName'];
        $originId = $input['originId'] ?? null;

        $accountId = FresnsAccounts::where('aid', $aid)->value('id');
        if (empty($accountId)) {
            return $this->pluginError(ErrorCodeService::ACCOUNT_CHECK_ERROR);
        }
        $userId = null;
        if (! empty($uid)) {
            // If there is a pass uid then check if it belongs to aid
            $user = FresnsUsers::where('uid', $uid)->first();
            if (empty($user)) {
                return $this->pluginError(ErrorCodeService::USER_CHECK_ERROR);
            }
            if ($user['account_id'] !== $accountId) {
                return $this->pluginError(ErrorCodeService::USER_FAIL);
            }
            $userId = $user['id'];
        }

        // Need to query the ending balance value of the account's last transaction record before the transaction (is_enable=1)
        // Compare the current account's wallet balance, and return a status code if it does not match.
        $accountWallets = FresnsAccountWallets::where('account_id', $accountId)->where('is_enable', 1)->first();
        if (empty($accountWallets)) {
            return $this->pluginError(ErrorCodeService::ACCOUNT_WALLETS_ERROR);
        }

        $balance = $accountWallets['balance'] ?? 0;
        $closingBalance = FresnsAccountWalletLogs::where('account_id', $accountId)->where('is_enable', 1)->orderByDesc('id')->value('closing_balance');
        $closingBalance = $closingBalance ?? 0;

        if ($balance !== $closingBalance) {
            return $this->pluginError(ErrorCodeService::BALANCE_CLOSING_BALANCE_ERROR);
        }

        $originAccountId = null;
        if ($originAid) {
            $originAccountId = FresnsAccounts::where('aid', $originAid)->value('id');
            if (empty($originAccountId)) {
                return $this->pluginError(ErrorCodeService::ACCOUNT_CHECK_ERROR);
            }
        }

        $originUserId = null;
        if ($originUid) {
            $originUser = FresnsUsers::where('uid', $originUid)->first();
            if (empty($originUser)) {
                return $this->pluginError(ErrorCodeService::USER_CHECK_ERROR);
            }
            if ($originUser['account_id'] !== $accountId) {
                return $this->pluginError(ErrorCodeService::USER_FAIL);
            }
            $originUserId = $originUser['id'];
        }

        // If there is a related party, generate a transaction record for the other party "account_wallet_logs" table, and subtract the balance of the other party with the amount parameter "account_wallets > balance"
        if ($originAccountId) {
            $originAccountWallets = FresnsAccountWallets::where('account_id', $originAccountId)->where('is_enable', 1)->first();
            if (empty($originAccountWallets)) {
                return $this->pluginError(ErrorCodeService::TO_ACCOUNT_WALLETS_ERROR);
            }

            $originAccountBalance = $originAccountWallets['balance'] ?? 0;
            $originAccountClosingBalance = FresnsAccountWalletLogs::where('account_id', $originAccountId)->where('is_enable', 1)->orderByDesc('id')->value('closing_balance');
            $originAccountClosingBalance = $originAccountClosingBalance ?? 0;

            if ($originAccountBalance < $amount) {
                return $this->pluginError(ErrorCodeService::ACCOUNT_BALANCE_ERROR);
            }

            if ($originAccountBalance !== $originAccountClosingBalance) {
                return $this->pluginError(ErrorCodeService::TO_BALANCE_CLOSING_BALANCE_ERROR);
            }

            if ($originAccountBalance - $amount < 0) {
                return $this->pluginError(ErrorCodeService::ACCOUNT_BALANCE_ERROR);
            }

            switch ($type) {
                case 1:
                    $decreaseType = 4;
                    break;
                case 2:
                    $decreaseType = 5;
                    break;
                default:
                    $decreaseType = 6;
                    break;
            }
            // Add a counterpart expense wallet log
            $input = [
                'account_id' => $originAccountId,
                'user_id' => $originUserId,
                'object_type' => $decreaseType,
                'amount' => $amount,
                'transaction_amount' => $transactionAmount,
                'system_fee' => $systemFee,
                'object_account_id' => $accountId,
                'object_user_id' => $userId,
                'object_name' => $originName,
                'object_id' => $originId,
                'opening_balance' => $originAccountBalance,
                'closing_balance' => $originAccountBalance - $amount,
            ];

            FresnsAccountWalletLogs::insert($input);
            // Update Account Wallet
            $originWalletsInput = [
                'balance' => $originAccountBalance - $amount,
            ];
            FresnsAccountWallets::where('account_id', $originAccountId)->update($originWalletsInput);
        }

        // Add to wallet log
        $input = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'object_type' => $type,
            'amount' => $amount,
            'transaction_amount' => $transactionAmount,
            'system_fee' => $systemFee,
            'object_account_id' => $originAccountId,
            'object_user_id' => $originUserId,
            'object_name' => $originName,
            'object_id' => $originId,
            'opening_balance' => $balance,
            'closing_balance' => $balance + $transactionAmount,
        ];

        FresnsAccountWalletLogs::insert($input);
        // Update Account Wallet
        $accountWalletsInput = [
            'balance' => $balance + $transactionAmount,
        ];
        FresnsAccountWallets::where('account_id', $accountId)->update($accountWalletsInput);

        return $this->pluginSuccess();
    }

    // Wallet Trading (decrease)
    // Note: When querying the last transaction record with is_enable=1, the default ending balance is 0 if no transaction record is queried.
    public function walletDecreaseHandler($input)
    {
        $type = $input['type'];
        $aid = $input['aid'];
        $uid = $input['uid'] ?? null;
        $amount = $input['amount'];
        $transactionAmount = $input['transactionAmount'];
        $systemFee = $input['systemFee'];
        $originAid = $input['originAid'] ?? null;
        $originUid = $input['originUid'] ?? null;
        $originName = $input['originName'];
        $originId = $input['originId'] ?? null;

        $accountId = FresnsAccounts::where('aid', $aid)->value('id');
        if (empty($accountId)) {
            return $this->pluginError(ErrorCodeService::ACCOUNT_CHECK_ERROR);
        }
        $userId = null;
        if (! empty($uid)) {
            // If there is a pass uid then check if it belongs to aid
            $user = FresnsUsers::where('uid', $uid)->first();
            if (empty($user)) {
                return $this->pluginError(ErrorCodeService::USER_CHECK_ERROR);
            }
            if ($user['account_id'] !== $accountId) {
                return $this->pluginError(ErrorCodeService::USER_FAIL);
            }
            $userId = $user['id'];
        }

        $originAccountId = null;
        if ($originAid) {
            $originAccountId = FresnsAccounts::where('aid', $originAid)->value('id');
            if (empty($originAccountId)) {
                return $this->pluginError(ErrorCodeService::ACCOUNT_CHECK_ERROR);
            }
        }

        $originUserId = null;
        if ($originUid) {
            $originUser = FresnsUsers::where('uid', $originUid)->first();
            if (empty($originUser)) {
                return $this->pluginError(ErrorCodeService::USER_CHECK_ERROR);
            }
            if ($originUser['account_id'] !== $accountId) {
                return $this->pluginError(ErrorCodeService::USER_FAIL);
            }
            $originUserId = $originUser['id'];
        }

        $accountWallets = FresnsAccountWallets::where('account_id', $accountId)->where('is_enable', 1)->first();
        if (empty($accountWallets)) {
            return $this->pluginError(ErrorCodeService::ACCOUNT_WALLETS_ERROR);
        }

        $balance = $accountWallets['balance'] ?? 0;
        $accountClosingBalance = FresnsAccountWalletLogs::where('account_id', $accountId)->where('is_enable', 1)->orderByDesc('id')->value('closing_balance');
        $accountClosingBalance = $accountClosingBalance ?? 0;

        if ($balance !== $accountClosingBalance) {
            return $this->pluginError(ErrorCodeService::BALANCE_CLOSING_BALANCE_ERROR);
        }

        if ($originAccountId) {
            $originAccountWallets = FresnsAccountWallets::where('account_id', $originAccountId)->where('is_enable', 1)->first();
            if (empty($originAccountWallets)) {
                return $this->pluginError(ErrorCodeService::TO_ACCOUNT_WALLETS_ERROR);
            }

            if ($balance < $amount) {
                return $this->pluginError(ErrorCodeService::ACCOUNT_BALANCE_ERROR);
            }

            $originBalance = $originAccountWallets['balance'] ?? 0;
            $originClosingBalance = FresnsAccountWalletLogs::where('account_id', $originAccountId)->where('is_enable', 1)->orderByDesc('id')->value('closing_balance');
            $originClosingBalance = $originClosingBalance ?? 0;

            if ($originBalance !== $originClosingBalance) {
                return $this->pluginError(ErrorCodeService::TO_BALANCE_CLOSING_BALANCE_ERROR);
            }

            if ($balance - $amount < 0) {
                return $this->pluginError(ErrorCodeService::ACCOUNT_BALANCE_ERROR);
            }

            switch ($type) {
                case 4:
                    $decreaseType = 1;
                    break;
                case 5:
                    $decreaseType = 2;
                    break;
                default:
                    $decreaseType = 3;
                    break;
            }
            // Add a counterpart income wallet log
            $input = [
                'account_id' => $originAccountId,
                'user_id' => $originUserId,
                'object_type' => $decreaseType,
                'amount' => $amount,
                'transaction_amount' => $transactionAmount,
                'system_fee' => $systemFee,
                'object_account_id' => $accountId,
                'object_user_id' => $userId,
                'object_name' => $originName,
                'object_id' => $originId,
                'opening_balance' => $originBalance,
                'closing_balance' => $originBalance + $transactionAmount,
            ];

            FresnsAccountWalletLogs::insert($input);
            // Update Account Wallet
            $originWalletsInput = [
                'balance' => $originBalance + $transactionAmount,
            ];
            FresnsAccountWallets::where('account_id', $originAccountId)->update($originWalletsInput);
        }

        if ($balance - $amount < 0) {
            return $this->pluginError(ErrorCodeService::ACCOUNT_BALANCE_ERROR);
        }

        // Add to wallet log
        $input = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'object_type' => $type,
            'amount' => $amount,
            'transaction_amount' => $transactionAmount,
            'system_fee' => $systemFee,
            'object_account_id' => $originAccountId,
            'object_user_id' => $originUserId,
            'object_name' => $originName,
            'object_id' => $originId,
            'opening_balance' => $balance,
            'closing_balance' => $balance - $amount,
        ];

        FresnsAccountWalletLogs::insert($input);
        // Update Account Wallet
        $accountWalletsInput = [
            'balance' => $balance - $amount,
        ];
        FresnsAccountWallets::where('account_id', $accountId)->update($accountWalletsInput);

        return $this->pluginSuccess();
    }

    public function accountRegisterHandler($inputData)
    {
        $type = $inputData['type'];
        $account = $inputData['account'];
        $countryCode = $inputData['countryCode'] ?? null;
        $connectInfo = $inputData['connectInfo'] ?? null;
        $password = $inputData['password'] ?? null;
        $nickname = $inputData['nickname'];
        $avatarFid = $inputData['avatarFid'] ?? null;
        $avatarUrl = $inputData['avatarUrl'] ?? null;
        $gender = $inputData['gender'] ?? 0;
        $birthday = $inputData['birthday'] ?? null;
        $timezone = $inputData['timezone'] ?? null;
        $language = $inputData['language'] ?? null;

        // If the connectInfo parameter is passed, check if the connectToken exists
        $connectInfoArr = [];
        if ($connectInfo) {
            $connectInfoArr = json_decode($connectInfo, true);
            $connectTokenArr = [];
            foreach ($connectInfoArr as $v) {
                $connectTokenArr[] = $v['connectToken'];
            }

            $count = DB::table(FresnsAccountConnectsConfig::CFG_TABLE)->whereIn('connect_token', $connectTokenArr)->count();
            if ($count > 0) {
                return $this->pluginError(ErrorCodeService::CONNECT_TOKEN_ERROR);
            }
        }

        $input = [];
        // Verify successful account creation
        switch ($type) {
            case 1:
                $input = [
                    'email' => $account,
                ];
                break;
            case 2:
                $input = [
                    'country_code' => $countryCode,
                    'pure_phone' => $account,
                    'phone' => $countryCode.$account,
                ];
                break;
            default:
                // code...
                break;
        }
        $accountAid = StrHelper::createFsid();
        $input['aid'] = $accountAid;
        $input['last_login_at'] = date('Y-m-d H:i:s');
        if ($password) {
            $input['password'] = StrHelper::createPassword($password);
        }

        $aid = FresnsAccounts::insertGetId($input);
        // FresnsSubPluginService::addSubTablePluginItem(FresnsAccountsConfig::CFG_TABLE, $aid);

        $fileId = null;
        if ($avatarFid) {
            $fileId = FresnsFiles::where('fid', $avatarFid)->value('id');
        }

        $userInput = [
            'account_id' => $aid,
            'username' => StrHelper::createToken(rand(6, 8)),
            'nickname' => $nickname,
            'uid' => ApiCommonHelper::createUserUid(),
            'avatar_file_id' => $fileId,
            'avatar_file_url' => $avatarUrl,
            'gender' => $gender,
            'birthday' => $birthday,
            'timezone' => $timezone,
            'language' => $language,
        ];

        $uid = FresnsUsers::insertGetId($userInput);
        // FresnsSubPluginService::addSubTablePluginItem(FresnsUsersConfig::CFG_TABLE, $uid);

        $langTag = request()->header('langTag');

        if ($type == 1) {
            // Add Counts
            $accountCounts = ApiConfigHelper::getConfigByItemKey('accounts_count');
            if ($accountCounts === null) {
                $input = [
                    'item_key' => 'accounts_count',
                    'item_value' => 1,
                    'item_tag' => 'stats',
                    'item_type' => 'number',
                ];
                FresnsConfigs::insert($input);
            } else {
                FresnsConfigs::where('item_key', 'accounts_count')->update(['item_value' => $accountCounts + 1]);
            }
            $userCounts = ApiConfigHelper::getConfigByItemKey('users_count');
            if ($userCounts === null) {
                $input = [
                    'item_key' => 'users_count',
                    'item_value' => 1,
                    'item_tag' => 'stats',
                    'item_type' => 'number',
                ];
                FresnsConfigs::insert($input);
            } else {
                FresnsConfigs::where('item_key', 'users_count')->update(['item_value' => $userCounts + 1]);
            }
        }

        // Register successfully to add records to the table
        $userStatsInput = [
            'user_id' => $uid,
        ];
        FresnsUserStats::insert($userStatsInput);
        $accountWalletsInput = [
            'account_id' => $aid,
            'balance' => 0,
        ];
        FresnsAccountWallets::insert($accountWalletsInput);
        $defaultRoleId = ApiConfigHelper::getConfigByItemKey('default_role');
        $userRolesInput = [
            'user_id' => $uid,
            'role_id' => $defaultRoleId,
            'type' => 2,
        ];
        FresnsUserRoles::insert($userRolesInput);

        // If the connectInfo parameter is passed, add it to the account_connects table
        if ($connectInfoArr) {
            $itemArr = [];
            foreach ($connectInfoArr as $info) {
                $item = [];
                $item['account_id'] = $aid;
                $item['connect_id'] = $info['connectId'];
                $item['connect_token'] = $info['connectToken'];
                $item['connect_name'] = $info['connectName'];
                $item['connect_nickname'] = $info['connectNickname'];
                $item['connect_avatar'] = $info['connectAvatar'];
                $item['plugin_unikey'] = 'fresns_cmd_account_register';
                $itemArr[] = $item;
            }

            FresnsAccountConnects::insert($itemArr);
        }

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $aid, $uid, $aid);
        }

        $service = new FresnsAccountsService();
        $data = $service->getAccountDetail($aid, $langTag, $uid);

        return $this->pluginSuccess($data);
    }

    public function accountLoginHandler($input)
    {
        $type = $input['type'];
        $account = $input['account'];
        $countryCode = $input['countryCode'];
        $verifyCode = $input['verifyCode'];
        $passwordBase64 = $input['password'];

        if ($passwordBase64) {
            $password = base64_decode($passwordBase64, true);
            if ($password == false) {
                $password = $passwordBase64;
            }
        } else {
            $password = null;
        }

        switch ($type) {
            case 1:
                $account = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('email', $account)->first();
                break;
            case 2:
                $account = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('phone', $countryCode.$account)->first();
                break;
            default:
                // code...
                break;
        }

        $sessionLogId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionLogId) {
            $sessionInput = [
                'object_order_id' => $account->id,
                'account_id' => $account->id,
            ];
            FresnsSessionLogs::where('id', $sessionLogId)->update($sessionInput);
        }

        // Check the account of login password errors in the last 1 hour for the account to whom the email or cell phone number belongs.
        // If it reaches 5 times, the login will be restricted.
        // session_logs > object_type=3
        $startTime = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $sessionCount = FresnsSessionLogs::where('created_at', '>=', $startTime)
        ->where('account_id', $account->id)
        ->where('object_result', FresnsSessionLogsConfig::OBJECT_RESULT_ERROR)
        ->where('object_type', FresnsSessionLogsConfig::OBJECT_TYPE_ACCOUNT_LOGIN)
        ->count();

        if ($sessionCount >= 5) {
            return $this->pluginError(ErrorCodeService::ACCOUNT_COUNT_ERROR);
        }
        // One of the password or verification code is required
        if (empty($password) && empty($verifyCode)) {
            return $this->pluginError(ErrorCodeService::ACCOUNT_VERIFY_ERROR);
        }

        $time = date('Y-m-d H:i:s', time());
        if ($type != 3) {
            if ($verifyCode) {
                switch ($type) {
                    case 1:
                        $codeArr = FresnsVerifyCodes::where('type', $type)->where('account',
                            $account)->where('expired_at', '>', $time)->pluck('code')->toArray();
                        break;
                    case 2:
                        $codeArr = FresnsVerifyCodes::where('type', $type)->where('account',
                            $countryCode.$account)->where('expired_at', '>', $time)->pluck('code')->toArray();
                        break;
                    default:
                        // code...
                        break;
                }

                if (! in_array($verifyCode, $codeArr)) {
                    return $this->pluginError(ErrorCodeService::VERIFY_CODE_CHECK_ERROR);
                }
            }

            if ($password) {
                if (! Hash::check($password, $account->password)) {
                    return $this->pluginError(ErrorCodeService::ACCOUNT_PASSWORD_INVALID);
                }
            }
        }

        if ($account->is_enable == 0) {
            return $this->pluginError(ErrorCodeService::ACCOUNT_IS_ENABLE_ERROR);
        }
        $langTag = request()->header('langTag');
        $service = new FresnsAccountsService();
        $data = $service->getAccountDetail($account->id, $langTag);
        // Update the last_login_at field in the accounts table
        FresnsAccounts::where('id', $account->id)->update(['last_login_at' => date('Y-m-d H:i:s', time())]);

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $account->id, null, $account->id);
        }

        return $this->pluginSuccess($data);
    }

    public function accountDetailHandler($input)
    {
        $aid = $input['aid'];
        $aid = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('aid', $aid)->value('id');

        $langTag = request()->header('langTag');
        $service = new FresnsAccountsService();
        $data = $service->getAccountDetail($aid, $langTag);

        return $this->pluginSuccess($data);
    }
}
