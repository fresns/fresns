<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsCmd;

use App\Helpers\SignHelper;
use App\Helpers\StrHelper;
use App\Http\Center\Base\BasePlugin;
use App\Http\Center\Common\ErrorCodeService;
use App\Http\Center\Common\GlobalService;
use App\Http\Center\Common\LogService;
use App\Http\Center\Common\ValidateService;
use App\Http\Center\Helper\CmdRpcHelper;
use App\Http\Center\Helper\PluginHelper;
use App\Http\Center\Scene\FileSceneConfig;
use App\Http\Center\Scene\FileSceneService;
use App\Http\FresnsApi\Helpers\ApiCommonHelper;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsDb\FresnsCommentAppends\FresnsCommentAppendsConfig;
use App\Http\FresnsDb\FresnsCommentLogs\FresnsCommentLogs;
use App\Http\FresnsDb\FresnsCommentLogs\FresnsCommentLogsConfig;
use App\Http\FresnsDb\FresnsComments\FresnsComments;
use App\Http\FresnsDb\FresnsComments\FresnsCommentsConfig;
use App\Http\FresnsDb\FresnsComments\FresnsCommentsService;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigs;
use App\Http\FresnsDb\FresnsDomainLinks\FresnsDomainLinksConfig;
use App\Http\FresnsDb\FresnsDomains\FresnsDomains;
use App\Http\FresnsDb\FresnsExtendLinkeds\FresnsExtendLinkedsConfig;
use App\Http\FresnsDb\FresnsExtends\FresnsExtendsConfig;
use App\Http\FresnsDb\FresnsFileAppends\FresnsFileAppends;
use App\Http\FresnsDb\FresnsFileAppends\FresnsFileAppendsConfig;
use App\Http\FresnsDb\FresnsFiles\FresnsFiles;
use App\Http\FresnsDb\FresnsFiles\FresnsFilesConfig;
use App\Http\FresnsDb\FresnsGroups\FresnsGroups;
use App\Http\FresnsDb\FresnsHashtagLinkeds\FresnsHashtagLinkedsConfig;
use App\Http\FresnsDb\FresnsHashtags\FresnsHashtags;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguagesConfig;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsMembers\FresnsMembersConfig;
use App\Http\FresnsDb\FresnsMemberStats\FresnsMemberStats;
use App\Http\FresnsDb\FresnsMentions\FresnsMentionsConfig;
use App\Http\FresnsDb\FresnsPostAllows\FresnsPostAllowsConfig;
use App\Http\FresnsDb\FresnsPostAppends\FresnsPostAppendsConfig;
use App\Http\FresnsDb\FresnsPostLogs\FresnsPostLogs;
use App\Http\FresnsDb\FresnsPostLogs\FresnsPostLogsConfig;
use App\Http\FresnsDb\FresnsPosts\FresnsPosts;
use App\Http\FresnsDb\FresnsPosts\FresnsPostsConfig;
use App\Http\FresnsDb\FresnsPosts\FresnsPostsService;
use App\Http\FresnsDb\FresnsSessionKeys\FresnsSessionKeys;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogs;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogsConfig;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogsService;
use App\Http\FresnsDb\FresnsSessionTokens\FresnsSessionTokensConfig;
use App\Http\FresnsDb\FresnsUserConnects\FresnsUserConnects;
use App\Http\FresnsDb\FresnsUserConnects\FresnsUserConnectsConfig;
use App\Http\FresnsDb\FresnsUsers\FresnsUsers;
use App\Http\FresnsDb\FresnsUsers\FresnsUsersConfig;
use App\Http\FresnsDb\FresnsUsers\FresnsUsersService;
use App\Http\FresnsDb\FresnsUserWalletLogs\FresnsUserWalletLogs;
use App\Http\FresnsDb\FresnsUserWallets\FresnsUserWallets;
use App\Http\FresnsDb\FresnsVerifyCodes\FresnsVerifyCodes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

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
                $postId = FresnsPostLogs::find($logId);
                $cmd = FresnsSubPluginConfig::FRESNS_CMD_SUB_ACTIVE_COMMAND_WORD;
                $input = [
                    'tableName' => 'posts',
                    'insertId' => $postId['post_id'],
                    'commandWord' => 'fresns_cmd_direct_release_content',
                ];
                $resp = CmdRpcHelper::call(FresnsSubPlugin::class, $cmd, $input);
                break;
            case 2:
                $result = $fresnsCommentService->releaseByDraft($logId, $commentCid, $sessionLogsId);
                $commentInfo = FresnsCommentLogs::find($logId);
                $cmd = FresnsSubPluginConfig::FRESNS_CMD_SUB_ACTIVE_COMMAND_WORD;
                $input = [
                    'tableName' => 'comments',
                    'insertId' => $commentInfo['comment_id'],
                    'commandWord' => 'fresns_cmd_direct_release_content',
                ];
                $resp = CmdRpcHelper::call(FresnsSubPlugin::class, $cmd, $input);
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
        $mid = $input['mid'];
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
            'mid' => $mid,
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
        $mid = $input['mid'];
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
            'mid' => $mid,
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
        $phone = $input['mid'];
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

        $userId = $input['uid'];
        $memberId = $input['mid'] ?? null;
        $platform = $input['platform'];

        $expiredTime = $input['expiredTime'] ?? null;
        if ($userId) {
            $userId = DB::table(FresnsUsersConfig::CFG_TABLE)->where('uuid', $userId)->value('id');
        }
        if ($memberId) {
            $memberId = DB::table(FresnsMembersConfig::CFG_TABLE)->where('uuid', $memberId)->value('id');
        }
        if (empty($memberId)) {
            $tokenCount = DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('user_id', $userId)->where('member_id', null)->where('platform_id', $platform)->count();
            $token = StrHelper::createToken();

            if ($tokenCount > 0) {
                DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('user_id', $userId)->where('member_id', null)->where('platform_id', $platform)->delete();
            }
            $input = [];
            $input['platform_id'] = $platform;
            $input['user_id'] = $userId;
            $input['token'] = $token;
            if ($expiredTime) {
                $input['expired_at'] = $expiredTime ?? null;
            }
            DB::table(FresnsSessionTokensConfig::CFG_TABLE)->insert($input);
        } else {
            $sessionToken = DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('user_id', $userId)->where('member_id', $memberId)->where('platform_id', $platform)->first();
            $token = StrHelper::createToken();
            if ($sessionToken) {
                DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('user_id', $userId)->where('member_id', $memberId)->where('platform_id', $platform)->delete();
            }
            $input = [];
            $input['token'] = $token;
            $input['platform_id'] = $platform;
            $input['user_id'] = $userId;
            $input['member_id'] = $memberId;
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
        $userId = $input['uid'];
        $memberId = $input['mid'] ?? null;
        $platform = $input['platform'];
        $token = $input['token'];
        $time = date('Y-m-d H:i:s', time());

        if ($userId) {
            $userId = DB::table(FresnsUsersConfig::CFG_TABLE)->where('uuid', $userId)->value('id');
        }
        if ($memberId) {
            $memberId = DB::table(FresnsMembersConfig::CFG_TABLE)->where('uuid', $memberId)->value('id');
        }

        if (empty($memberId)) {
            // Verify Token
            $uidToken = DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('platform_id', $platform)->where('user_id', $userId)->where('member_id', null)->first();

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
        } else {
            // Verify Token
            $midToken = DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('platform_id', $platform)->where('user_id', $userId)->where('member_id', $memberId)->first();
            if (empty($midToken)) {
                return $this->pluginError(ErrorCodeService::MEMBER_TOKEN_ERROR);
            }

            if (! empty($midToken->expired_at)) {
                if ($midToken->expired_at < $time) {
                    return $this->pluginError(ErrorCodeService::MEMBER_TOKEN_ERROR);
                }
            }

            if ($midToken->token != $token) {
                return $this->pluginError(ErrorCodeService::MEMBER_TOKEN_ERROR);
            }
        }

        return $this->pluginSuccess();
    }

    // Upload log
    public function uploadSessionLogHandler($input)
    {
        $platform = $input['platform'];
        $version = $input['version'];
        $versionInt = $input['versionInt'];
        $objectName = $input['objectName'];
        $objectAction = $input['objectAction'];
        $objectResult = $input['objectResult'];
        $objectType = $input['objectType'] ?? 1;
        $langTag = $input['langTag'] ?? null;
        $objectOrderId = $input['objectOrderId'] ?? null;
        $deviceInfo = $input['deviceInfo'] ?? null;
        $userId = $input['uid'] ?? null;
        $memberId = $input['uid'] ?? null;
        $moreJson = $input['moreJson'] ?? null;

        if ($userId) {
            $userId = FresnsUsers::where('uuid', $userId)->value('id');
        }
        if ($memberId) {
            $memberId = FresnsMembers::where('uuid', $memberId)->value('id');
        }
        $input = [
            'platform_id' => $platform,
            'version' => $version,
            'version_int' => $versionInt,
            'lang_tag' => $langTag,
            'object_name' => $objectName,
            'object_action' => $objectAction,
            'object_result' => $objectResult,
            'object_order_id' => $objectOrderId,
            'device_info' => $deviceInfo,
            'user_id' => $userId,
            'member_id' => $memberId,
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
                $unikey = ApiConfigHelper::getConfigByItemKey('images_service');
                break;
            case 2:
                $unikey = ApiConfigHelper::getConfigByItemKey('videos_service');
                break;
            case 3:
                $unikey = ApiConfigHelper::getConfigByItemKey('audios_service');
                break;
            default:
                $unikey = ApiConfigHelper::getConfigByItemKey('docs_service');
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
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['images_secret_id', 'images_secret_key', 'images_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['images_secret_id', 'images_secret_key', 'images_bucket_domain']);
        }
        if ($file['file_type'] == FileSceneConfig::FILE_TYPE_2) {
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['videos_secret_id', 'videos_secret_key', 'videos_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['videos_secret_id', 'videos_secret_key', 'videos_bucket_domain']);
        }
        if ($file['file_type'] == FileSceneConfig::FILE_TYPE_3) {
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['audios_secret_id', 'audios_secret_key', 'audios_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['audios_secret_id', 'audios_secret_key', 'audios_bucket_domain']);
        }
        if ($file['file_type'] == FileSceneConfig::FILE_TYPE_4) {
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['docs_secret_id', 'docs_secret_key', 'docs_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['docs_secret_id', 'docs_secret_key', 'docs_bucket_domain']);
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
        $tableField = $input['tableField'];
        $tableId = $input['tableId'];
        $tableKey = $input['tableKey'];
        $mode = $input['mode'];
        $uploadFile = $input['file'];
        $fileInfo = $input['fileInfo'] ?? null;
        $platformId = $input['platform'];
        $userId = $input['uid'] ?? null;
        $memberId = $input['mid'] ?? null;

        if($tableId){
            $tableId = DB::table($tableName)->where('uuid',$tableId)->value('id');
        }
        if ($userId) {
            $userId = FresnsUsers::where('uuid', $userId)->value('id');
        }
        if ($memberId) {
            $memberId = FresnsMembers::where('uuid', $memberId)->value('id');
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
                $unikey = ApiConfigHelper::getConfigByItemKey('images_service');
                break;
            case 2:
                $unikey = ApiConfigHelper::getConfigByItemKey('videos_service');
                break;
            case 3:
                $unikey = ApiConfigHelper::getConfigByItemKey('audios_service');
                break;
            default:
                $unikey = ApiConfigHelper::getConfigByItemKey('docs_service');
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
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['images_secret_id', 'images_secret_key', 'images_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['images_secret_id', 'images_secret_key', 'images_bucket_domain']);
        }
        if ($file['file_type'] == FileSceneConfig::FILE_TYPE_2) {
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['videos_secret_id', 'videos_secret_key', 'videos_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['videos_secret_id', 'videos_secret_key', 'videos_bucket_domain']);
        }

        if ($file['file_type'] == FileSceneConfig::FILE_TYPE_3) {
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['audios_secret_id', 'audios_secret_key', 'audios_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['audios_secret_id', 'audios_secret_key', 'audios_bucket_domain']);
        }
        if ($file['file_type'] == FileSceneConfig::FILE_TYPE_4) {
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['docs_secret_id', 'docs_secret_key', 'docs_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['docs_secret_id', 'docs_secret_key', 'docs_bucket_domain']);
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
                return $this->pluginError(ErrorCodeService::MEMBER_FAIL);
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
            $file['table_field'] = $tableField;
            $file['table_id'] = $tableId ?? null;
            $file['table_key'] = $tableKey ?? null;

            LogService::info('File Storage Local Success', $file);
            $t2 = time();

            $file['uuid'] = StrHelper::createUuid();
            // Insert
            $retId = FresnsFiles::insertGetId($file);
            FresnsSubPluginService::addSubTablePluginItem(FresnsFilesConfig::CFG_TABLE, $retId);

            $file['real_path'] = $newPath;
            $input = [
                'file_id' => $retId,
                'file_mime' => $uploadFile->getMimeType(),
                'file_size' => $uploadFile->getSize(),
                'platform_id' => $platformId,
                'transcoding_state' => 1,
                'user_id' => $userId,
                'member_id' => $memberId,
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

            $fidArr = [$file['uuid']];
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
                    $uuid = $fileInfo['fid'] ?? StrHelper::createUuid();
                    $item['uuid'] = $uuid;
                    $item['table_type'] = $tableType;
                    $item['table_name'] = $tableName;
                    $item['table_field'] = $tableField;
                    $item['table_id'] = $tableId ?? null;
                    $item['table_key'] = $tableKey ?? null;
                    $fieldId = FresnsFiles::insertGetId($item);
                    FresnsSubPluginService::addSubTablePluginItem(FresnsFilesConfig::CFG_TABLE, $fieldId);
                    $fileIdArr[] = $fieldId;
                    $fidArr[] = $item['uuid'];
                    $append = [];
                    $append['file_id'] = $fieldId;
                    $append['user_id'] = $userId;
                    $append['member_id'] = $memberId;
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
                $uuid = $file['uuid'];
                $append = FresnsFileAppends::where('file_id', $file['id'])->first();
                $item['fid'] = $file['uuid'];
                $item['type'] = $file['file_type'];
                $item['name'] = $file['file_name'];
                $item['extension'] = $file['file_extension'];
                $item['mime'] = $append['file_mime'];
                $item['size'] = $append['file_size'];
                $item['rankNum'] = $file['rank_num'];
                if ($type == 1) {
                    $item['imageWidth'] = $append['image_width'] ?? '';
                    $item['imageHeight'] = $append['image_height'] ?? '';
                    $item['imageLong'] = $append['image_is_long'] ?? 0;
                    $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_IMAGE;
                    $input['fid'] = $uuid;
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
                    $item['videoTime'] = $append['video_time'] ?? '';
                    $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_VIDEO;
                    $input['fid'] = $uuid;
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
                    $item['audioTime'] = $append['audio_time'] ?? '';
                    $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_AUDIO;
                    $input['fid'] = $uuid;
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
                    $input['fid'] = $uuid;
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
        $files = FresnsFiles::where('uuid', $fid)->first();
        if (empty($files)) {
            return $this->pluginError(ErrorCodeService::FILE_EXIST_ERROR);
        }
        $append = FresnsFileAppends::where('file_id', $files['id'])->first();

        $imagesStatus = ApiConfigHelper::getConfigByItemKey('images_url_status');
        $imagesBucketDomain = ApiConfigHelper::getConfigByItemKey('images_bucket_domain');
        $imagesThumbConfig = ApiConfigHelper::getConfigByItemKey('images_thumb_config');
        $imagesThumbAvatar = ApiConfigHelper::getConfigByItemKey('images_thumb_avatar');
        $imagesThumbRatio = ApiConfigHelper::getConfigByItemKey('images_thumb_ratio');
        $imagesThumbSquare = ApiConfigHelper::getConfigByItemKey('images_thumb_square');
        $imagesThumbBig = ApiConfigHelper::getConfigByItemKey('images_thumb_big');

        $imageDefaultUrl = $imagesBucketDomain.$files['file_path'];
        $imageConfigUrl = $imagesBucketDomain.$files['file_path'].$imagesThumbConfig;
        $imageAvatarUrl = $imagesBucketDomain.$files['file_path'].$imagesThumbAvatar;
        $imageRatioUrl = $imagesBucketDomain.$files['file_path'].$imagesThumbRatio;
        $imageSquareUrl = $imagesBucketDomain.$files['file_path'].$imagesThumbSquare;
        $imageBigUrl = $imagesBucketDomain.$files['file_path'].$imagesThumbBig;
        $originalUrl = $imagesBucketDomain.($append['file_original_path'] ?? '');
        if ($imagesStatus == true) {
            $unikey = ApiConfigHelper::getConfigByItemKey('images_service');
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

            $configMapInDB = FresnsConfigs::whereIn('item_key', ['images_secret_id', 'images_secret_key', 'images_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['images_secret_id', 'images_secret_key', 'images_bucket_domain']);
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
            $imageConfigUrl = $output['imageConfigUrl'] ?? '';
            $imageAvatarUrl = $output['imageAvatarUrl'] ?? '';
            $imageRatioUrl = $output['imageRatioUrl'] ?? '';
            $imageSquareUrl = $output['imageSquareUrl'] ?? '';
            $imageBigUrl = $output['imageBigUrl'] ?? '';
            $originalUrl = $output['originalUrl'] ?? '';
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
        $files = FresnsFiles::where('uuid', $fid)->first();
        if (empty($files)) {
            return $this->pluginError(ErrorCodeService::FILE_EXIST_ERROR);
        }
        $append = FresnsFileAppends::where('file_id', $files['id'])->first();

        $videosStatus = ApiConfigHelper::getConfigByItemKey('videos_url_status');
        $videosBucketDomain = ApiConfigHelper::getConfigByItemKey('videos_bucket_domain');

        $videoCover = $videosBucketDomain.$append['video_cover'];
        $videoGif = $videosBucketDomain.$append['video_gif'];
        $videoUrl = $videosBucketDomain.$files['file_path'];
        $originalUrl = $videosBucketDomain.$append['file_original_path'];

        if ($videosStatus == true) {
            $unikey = ApiConfigHelper::getConfigByItemKey('videos_service');
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

            $configMapInDB = FresnsConfigs::whereIn('item_key', ['videos_secret_id', 'videos_secret_key', 'videos_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['videos_secret_id', 'videos_secret_key', 'videos_bucket_domain']);
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

            $videoCover = $output['videoCover'] ?? '';
            $videoGif = $output['videoGif'] ?? '';
            $videoUrl = $output['videoUrl'] ?? '';
            $originalUrl = $output['originalUrl'] ?? '';
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
        $files = FresnsFiles::where('uuid', $fid)->first();
        if (empty($files)) {
            return $this->pluginError(ErrorCodeService::FILE_EXIST_ERROR);
        }
        $append = FresnsFileAppends::where('file_id', $files['id'])->first();

        $audiosStatus = ApiConfigHelper::getConfigByItemKey('audios_url_status');
        $audiosBucketDomain = ApiConfigHelper::getConfigByItemKey('audios_bucket_domain');

        $audioUrl = $audiosBucketDomain.$files['file_path'];
        $originalUrl = $audiosBucketDomain.$append['file_original_path'];

        if ($audiosStatus == true) {
            $unikey = ApiConfigHelper::getConfigByItemKey('audios_service');
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

            $configMapInDB = FresnsConfigs::whereIn('item_key', ['audios_secret_id', 'audios_secret_key', 'audios_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['audios_secret_id', 'audios_secret_key', 'audios_bucket_domain']);
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

            $audioUrl = $output['audioUrl'] ?? '';
            $originalUrl = $output['originalUrl'] ?? '';
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
        $files = FresnsFiles::where('uuid', $fid)->first();
        if (empty($files)) {
            return $this->pluginError(ErrorCodeService::FILE_EXIST_ERROR);
        }
        $append = FresnsFileAppends::where('file_id', $files['id'])->first();

        $docsStatus = ApiConfigHelper::getConfigByItemKey('docs_url_status');
        $docsBucketDomain = ApiConfigHelper::getConfigByItemKey('docs_bucket_domain');

        $docUrl = $docsBucketDomain.$files['file_path'];
        $originalUrl = $docsBucketDomain.$append['file_original_path'];

        if ($docsStatus == true) {
            $unikey = ApiConfigHelper::getConfigByItemKey('docs_service');
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

            $configMapInDB = FresnsConfigs::whereIn('item_key', ['docs_secret_id', 'docs_secret_key', 'docs_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['docs_secret_id', 'docs_secret_key', 'docs_bucket_domain']);
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

            $docUrl = $output['docUrl'] ?? '';
            $originalUrl = $output['originalUrl'] ?? '';
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
        $files = FresnsFiles::where('uuid', $fid)->first();
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
        $files = FresnsFiles::where('uuid', $fid)->first();
        if (empty($files)) {
            return $this->pluginError(ErrorCodeService::FILE_EXIST_ERROR);
        }
        $type = $files['file_type'];
        switch ($type) {
            case 1:
                $unikey = ApiConfigHelper::getConfigByItemKey('images_service');
                break;
            case 2:
                $unikey = ApiConfigHelper::getConfigByItemKey('videos_service');
                break;
            case 3:
                $unikey = ApiConfigHelper::getConfigByItemKey('audios_service');
                break;
            default:
                $unikey = ApiConfigHelper::getConfigByItemKey('docs_service');
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
                                'table_field' => 'id',
                                'table_id' => $e,
                            ];
                            $extendFiles = FresnsFiles::where($input)->get(['id', 'uuid', 'file_type'])->toArray();
                            // The queried file ID will be forwarded to the associated plugin with the file type, and the plugin will delete the physical files of the storage service provider.
                            if (! empty($extendFiles)) {
                                foreach ($extendFiles as $file) {
                                    $extendsFileId = $file['uuid'];
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
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_field', 'title')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_field', 'desc_primary')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_field', 'desc_secondary')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_field', 'btn_name')->where('table_id', $e)->delete();
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
                $filesUuidArr = [];
                if (! empty($post->more_json)) {
                    $postMoreJsonArr = json_decode($post->more_json, true);
                    if (! empty($postMoreJsonArr['files'])) {
                        foreach ($postMoreJsonArr['files'] as $v) {
                            $filesUuidArr[] = $v['fid'];
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
                                $filesUuidArr[] = $files['fid'];
                            }
                        }
                    }
                }
                if ($filesUuidArr) {
                    $filesUuidArr = array_unique($filesUuidArr);
                    $filesIdArr = DB::table(FresnsFilesConfig::CFG_TABLE)->whereIn('uuid',
                        $filesUuidArr)->pluck('id')->toArray();
                    if ($filesIdArr) {
                        // Delete physical files
                        foreach ($filesIdArr as $v) {
                            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_PHYSICAL_DELETION_FILE;
                            $input['fid'] = $v;
                            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                        }
                        // Delete file data
                        DB::table(FresnsFilesConfig::CFG_TABLE)->whereIn('uuid', $filesIdArr)->delete();
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
                 * Delete the fields "allow_btn_name", "comment_btn_name", and "member_list_name" from the posts table in the languages table.
                 */
                if ($postAppend) {
                    DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsPostsConfig::CFG_TABLE)->where('table_field', 'allow_btn_name')->where('table_id', $postAppend->id)->delete();
                    DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsPostsConfig::CFG_TABLE)->where('table_field', 'comment_btn_name')->where('table_id', $postAppend->id)->delete();
                    DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsPostsConfig::CFG_TABLE)->where('table_field', 'member_list_name')->where('table_id', $postAppend->id)->delete();
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
                FresnsConfigs::where('item_key', 'post_counts')->decrement('item_value');

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
                                'table_field' => 'id',
                                'table_id' => $e,
                            ];
                            $extendFiles = FresnsFiles::where($input)->get(['id', 'uuid', 'file_type'])->toArray();
                            // The queried file ID will be forwarded to the associated plugin with the file type, and the plugin will delete the physical files of the storage service provider.
                            if (! empty($extendFiles)) {
                                foreach ($extendFiles as $file) {
                                    $extendsFileId = $file['uuid'];
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
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_field', 'title')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_field', 'desc_primary')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_field', 'desc_secondary')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_field', 'btn_name')->where('table_id', $e)->delete();
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
                $filesUuidArr = [];
                if (! empty($comment->more_json)) {
                    $commentMoreJsonArr = json_decode($comment->more_json, true);
                    if (! empty($commentMoreJsonArr['files'])) {
                        foreach ($commentMoreJsonArr['files'] as $v) {
                            $filesUuidArr[] = $v['fid'];
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
                                $filesUuidArr[] = $files['fid'];
                            }
                        }
                    }
                }
                if ($filesUuidArr) {
                    $filesUuidArr = array_unique($filesUuidArr);
                    $filesIdArr = DB::table(FresnsFilesConfig::CFG_TABLE)->whereIn('uuid',
                        $filesUuidArr)->pluck('id')->toArray();
                    if ($filesUuidArr) {
                        // Delete physical files
                        foreach ($filesUuidArr as $v) {
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
                FresnsConfigs::where('item_key', 'comment_counts')->decrement('item_value');

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
        $versionInt = $input['versionInt'] ?? null;
        $appId = $input['appId'];
        $timestamp = $input['timestamp'];
        $sign = $input['sign'];
        $uid = $input['uid'] ?? null;
        $mid = $input['mid'] ?? null;
        $token = $input['token'] ?? null;

        $dataMap['platform'] = $platform;
        if ($version) {
            $dataMap['version'] = $version;
        }
        if ($versionInt) {
            $dataMap['versionInt'] = $versionInt;
        }
        $dataMap['appId'] = $appId;
        $dataMap['timestamp'] = $timestamp;
        if ($uid) {
            $dataMap['uid'] = $uid;
        }
        if ($mid) {
            $dataMap['mid'] = $mid;
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
        $uid = $input['uid'];
        $mid = $input['mid'] ?? null;
        $amount = $input['amount'];
        $transactionAmount = $input['transactionAmount'];
        $systemFee = $input['systemFee'];
        $originUid = $input['originUid'] ?? null;
        $originMid = $input['originMid'] ?? null;
        $originName = $input['originName'];
        $originId = $input['originId'] ?? null;

        $userId = FresnsUsers::where('uuid', $uid)->value('id');
        if (empty($userId)) {
            return $this->pluginError(ErrorCodeService::USER_CHECK_ERROR);
        }
        $memberId = null;
        if (! empty($mid)) {
            // If there is a pass mid then check if it belongs to uid
            $member = FresnsMembers::where('uuid', $mid)->first();
            if (empty($member)) {
                return $this->pluginError(ErrorCodeService::MEMBER_CHECK_ERROR);
            }
            if ($member['user_id'] !== $userId) {
                return $this->pluginError(ErrorCodeService::MEMBER_FAIL);
            }
            $memberId = $member['id'];
        }

        // Need to query the ending balance value of the user's last transaction record before the transaction (is_enable=1)
        // Compare the current user's wallet balance, and return a status code if it does not match.
        $userWallets = FresnsUserWallets::where('user_id', $userId)->where('is_enable', 1)->first();
        if (empty($userWallets)) {
            return $this->pluginError(ErrorCodeService::USER_WALLETS_ERROR);
        }

        $balance = $userWallets['balance'] ?? 0;
        $closingBalance = FresnsUserWalletLogs::where('user_id', $userId)->where('is_enable', 1)->orderByDesc('id')->value('closing_balance');
        $closingBalance = $closingBalance ?? 0;

        if ($balance !== $closingBalance) {
            return $this->pluginError(ErrorCodeService::BALANCE_CLOSING_BALANCE_ERROR);
        }

        $originUserId = null;
        if ($originUid) {
            $originUserId = FresnsUsers::where('uuid', $originUid)->value('id');
            if (empty($originUserId)) {
                return $this->pluginError(ErrorCodeService::USER_CHECK_ERROR);
            }
        }

        $originMemberId = null;
        if ($originMid) {
            $originMember = FresnsMembers::where('uuid', $originMid)->first();
            if (empty($originMember)) {
                return $this->pluginError(ErrorCodeService::MEMBER_CHECK_ERROR);
            }
            if ($originMember['user_id'] !== $userId) {
                return $this->pluginError(ErrorCodeService::MEMBER_FAIL);
            }
            $originMemberId = $originMember['id'];
        }

        // If there is a related party, generate a transaction record for the other party "user_wallet_logs" table, and subtract the balance of the other party with the amount parameter "user_wallets > balance"
        if ($originUserId) {
            $originUserWallets = FresnsUserWallets::where('user_id', $originUserId)->where('is_enable', 1)->first();
            if (empty($originUserWallets)) {
                return $this->pluginError(ErrorCodeService::TO_USER_WALLETS_ERROR);
            }

            $originUserBalance = $originUserWallets['balance'] ?? 0;
            $originUserClosingBalance = FresnsUserWalletLogs::where('user_id', $originUserId)->where('is_enable', 1)->orderByDesc('id')->value('closing_balance');
            $originUserClosingBalance = $originUserClosingBalance ?? 0;

            if ($originUserBalance < $amount) {
                return $this->pluginError(ErrorCodeService::USER_BALANCE_ERROR);
            }

            if ($originUserBalance !== $originUserClosingBalance) {
                return $this->pluginError(ErrorCodeService::TO_BALANCE_CLOSING_BALANCE_ERROR);
            }

            if ($originUserBalance - $amount < 0) {
                return $this->pluginError(ErrorCodeService::USER_BALANCE_ERROR);
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
                'user_id' => $originUserId,
                'member_id' => $originMemberId,
                'object_type' => $decreaseType,
                'amount' => $amount,
                'transaction_amount' => $transactionAmount,
                'system_fee' => $systemFee,
                'object_user_id' => $userId,
                'object_member_id' => $memberId,
                'object_name' => $originName,
                'object_id' => $originId,
                'opening_balance' => $originUserBalance,
                'closing_balance' => $originUserBalance - $amount,
            ];

            FresnsUserWalletLogs::insert($input);
            // Update User Wallet
            $originWalletsInput = [
                'balance' => $originUserBalance - $amount,
            ];
            FresnsUserWallets::where('user_id', $originUserId)->update($originWalletsInput);
        }

        // Add to wallet log
        $input = [
            'user_id' => $userId,
            'member_id' => $memberId,
            'object_type' => $type,
            'amount' => $amount,
            'transaction_amount' => $transactionAmount,
            'system_fee' => $systemFee,
            'object_user_id' => $originUserId,
            'object_member_id' => $originMemberId,
            'object_name' => $originName,
            'object_id' => $originId,
            'opening_balance' => $balance,
            'closing_balance' => $balance + $transactionAmount,
        ];

        FresnsUserWalletLogs::insert($input);
        // Update User Wallet
        $userWalletsInput = [
            'balance' => $balance + $transactionAmount,
        ];
        FresnsUserWallets::where('user_id', $userId)->update($userWalletsInput);

        return $this->pluginSuccess();
    }

    // Wallet Trading (decrease)
    // Note: When querying the last transaction record with is_enable=1, the default ending balance is 0 if no transaction record is queried.
    public function walletDecreaseHandler($input)
    {
        $type = $input['type'];
        $uid = $input['uid'];
        $mid = $input['mid'] ?? null;
        $amount = $input['amount'];
        $transactionAmount = $input['transactionAmount'];
        $systemFee = $input['systemFee'];
        $originUid = $input['originUid'] ?? null;
        $originMid = $input['originMid'] ?? null;
        $originName = $input['originName'];
        $originId = $input['originId'] ?? null;

        $userId = FresnsUsers::where('uuid', $uid)->value('id');
        if (empty($userId)) {
            return $this->pluginError(ErrorCodeService::USER_CHECK_ERROR);
        }
        $memberId = null;
        if (! empty($mid)) {
            // If there is a pass mid then check if it belongs to uid
            $member = FresnsMembers::where('uuid', $mid)->first();
            if (empty($member)) {
                return $this->pluginError(ErrorCodeService::MEMBER_CHECK_ERROR);
            }
            if ($member['user_id'] !== $userId) {
                return $this->pluginError(ErrorCodeService::MEMBER_FAIL);
            }
            $memberId = $member['id'];
        }

        $originUserId = null;
        if ($originUid) {
            $originUserId = FresnsUsers::where('uuid', $originUid)->value('id');
            if (empty($originUserId)) {
                return $this->pluginError(ErrorCodeService::USER_CHECK_ERROR);
            }
        }

        $originMemberId = null;
        if ($originMid) {
            $originMember = FresnsMembers::where('uuid', $originMid)->first();
            if (empty($originMember)) {
                return $this->pluginError(ErrorCodeService::MEMBER_CHECK_ERROR);
            }
            if ($originMember['user_id'] !== $userId) {
                return $this->pluginError(ErrorCodeService::MEMBER_FAIL);
            }
            $originMemberId = $originMember['id'];
        }

        $userWallets = FresnsUserWallets::where('user_id', $userId)->where('is_enable', 1)->first();
        if (empty($userWallets)) {
            return $this->pluginError(ErrorCodeService::USER_WALLETS_ERROR);
        }

        $balance = $userWallets['balance'] ?? 0;
        $userClosingBalance = FresnsUserWalletLogs::where('user_id', $userId)->where('is_enable', 1)->orderByDesc('id')->value('closing_balance');
        $userClosingBalance = $userClosingBalance ?? 0;

        if ($balance !== $userClosingBalance) {
            return $this->pluginError(ErrorCodeService::BALANCE_CLOSING_BALANCE_ERROR);
        }

        if ($originUserId) {
            $originUserWallets = FresnsUserWallets::where('user_id', $originUserId)->where('is_enable', 1)->first();
            if (empty($originUserWallets)) {
                return $this->pluginError(ErrorCodeService::TO_USER_WALLETS_ERROR);
            }

            if ($balance < $amount) {
                return $this->pluginError(ErrorCodeService::USER_BALANCE_ERROR);
            }

            $originBalance = $originUserWallets['balance'] ?? 0;
            $originClosingBalance = FresnsUserWalletLogs::where('user_id', $originUserId)->where('is_enable', 1)->orderByDesc('id')->value('closing_balance');
            $originClosingBalance = $originClosingBalance ?? 0;

            if ($originBalance !== $originClosingBalance) {
                return $this->pluginError(ErrorCodeService::TO_BALANCE_CLOSING_BALANCE_ERROR);
            }

            if ($balance - $amount < 0) {
                return $this->pluginError(ErrorCodeService::USER_BALANCE_ERROR);
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
                'user_id' => $originUserId,
                'member_id' => $originMemberId,
                'object_type' => $decreaseType,
                'amount' => $amount,
                'transaction_amount' => $transactionAmount,
                'system_fee' => $systemFee,
                'object_user_id' => $userId,
                'object_member_id' => $memberId,
                'object_name' => $originName,
                'object_id' => $originId,
                'opening_balance' => $originBalance,
                'closing_balance' => $originBalance + $transactionAmount,
            ];

            FresnsUserWalletLogs::insert($input);
            // Update User Wallet
            $originWalletsInput = [
                'balance' => $originBalance + $transactionAmount,
            ];
            FresnsUserWallets::where('user_id', $originUserId)->update($originWalletsInput);
        }

        if ($balance - $amount < 0) {
            return $this->pluginError(ErrorCodeService::USER_BALANCE_ERROR);
        }

        // Add to wallet log
        $input = [
            'user_id' => $userId,
            'member_id' => $memberId,
            'object_type' => $type,
            'amount' => $amount,
            'transaction_amount' => $transactionAmount,
            'system_fee' => $systemFee,
            'object_user_id' => $originUserId,
            'object_member_id' => $originMemberId,
            'object_name' => $originName,
            'object_id' => $originId,
            'opening_balance' => $balance,
            'closing_balance' => $balance - $amount,
        ];

        FresnsUserWalletLogs::insert($input);
        // Update User Wallet
        $userWalletsInput = [
            'balance' => $balance - $amount,
        ];
        FresnsUserWallets::where('user_id', $userId)->update($userWalletsInput);

        return $this->pluginSuccess();
    }

    public function userRegisterHandler($inputData)
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

            $count = DB::table(FresnsUserConnectsConfig::CFG_TABLE)->whereIn('connect_token', $connectTokenArr)->count();
            if ($count > 0) {
                return $this->pluginError(ErrorCodeService::CONNECT_TOKEN_ERROR);
            }
        }

        $input = [];
        // Verify successful user creation
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
        $userUuid = StrHelper::createUuid();
        $input['api_token'] = StrHelper::createToken();
        $input['uuid'] = $userUuid;
        $input['last_login_at'] = date('Y-m-d H:i:s');
        if ($password) {
            $input['password'] = StrHelper::createPassword($password);
        }

        $uid = FresnsUsers::insertGetId($input);
        FresnsSubPluginService::addSubTablePluginItem(FresnsUsersConfig::CFG_TABLE, $uid);

        $fileId = null;
        if ($avatarFid) {
            $fileId = FresnsFiles::where('uuid', $avatarFid)->value('id');
        }

        $memberInput = [
            'user_id' => $uid,
            'name' => StrHelper::createToken(rand(6, 8)),
            'nickname' => $nickname,
            'uuid' => ApiCommonHelper::createMemberUuid(),
            'avatar_file_id' => $fileId,
            'avatar_file_url' => $avatarUrl,
            'gender' => $gender,
            'birthday' => $birthday,
            'timezone' => $timezone,
            'language' => $language,
        ];

        $mid = FresnsMembers::insertGetId($memberInput);
        FresnsSubPluginService::addSubTablePluginItem(FresnsMembersConfig::CFG_TABLE, $mid);

        $langTag = request()->header('langTag');

        if ($type == 1) {
            // Add Counts
            $userCounts = ApiConfigHelper::getConfigByItemKey('user_counts');
            if ($userCounts === null) {
                $input = [
                    'item_key' => 'user_counts',
                    'item_value' => 1,
                    'item_tag' => 'stats',
                    'item_type' => 'number',
                ];
                FresnsConfigs::insert($input);
            } else {
                FresnsConfigs::where('item_key', 'user_counts')->update(['item_value' => $userCounts + 1]);
            }
            $memberCounts = ApiConfigHelper::getConfigByItemKey('member_counts');
            if ($memberCounts === null) {
                $input = [
                    'item_key' => 'member_counts',
                    'item_value' => 1,
                    'item_tag' => 'stats',
                    'item_type' => 'number',
                ];
                FresnsConfigs::insert($input);
            } else {
                FresnsConfigs::where('item_key', 'member_counts')->update(['item_value' => $memberCounts + 1]);
            }
        }

        // Register successfully to add records to the table
        $memberStatsInput = [
            'member_id' => $mid,
        ];
        FresnsMemberStats::insert($memberStatsInput);
        $userWalletsInput = [
            'user_id' => $uid,
            'balance' => 0,
        ];
        FresnsUserWallets::insert($userWalletsInput);
        $defaultRoleId = ApiConfigHelper::getConfigByItemKey('default_role');
        $memberRoleRelsInput = [
            'member_id' => $mid,
            'role_id' => $defaultRoleId,
            'type' => 2,
        ];
        FresnsMemberRoleRels::insert($memberRoleRelsInput);

        // If the connectInfo parameter is passed, add it to the user_connects table
        if ($connectInfoArr) {
            $itemArr = [];
            foreach ($connectInfoArr as $info) {
                $item = [];
                $item['user_id'] = $uid;
                $item['connect_id'] = $info['connectId'];
                $item['connect_token'] = $info['connectToken'];
                $item['connect_name'] = $info['connectName'];
                $item['connect_nickname'] = $info['connectNickname'];
                $item['connect_avatar'] = $info['connectAvatar'];
                $item['plugin_unikey'] = 'fresns_cmd_user_register';
                $itemArr[] = $item;
            }

            FresnsUserConnects::insert($itemArr);
        }

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $uid, $mid, $uid);
        }

        $service = new FresnsUsersService();
        $data = $service->getUserDetail($uid, $langTag, $mid);

        return $this->pluginSuccess($data);
    }

    public function userLoginHandler($input)
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
                $user = DB::table(FresnsUsersConfig::CFG_TABLE)->where('email', $account)->first();
                break;
            case 2:
                $user = DB::table(FresnsUsersConfig::CFG_TABLE)->where('phone', $countryCode.$account)->first();
                break;
            default:
                // code...
                break;
        }

        $sessionLogId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionLogId) {
            $sessionInput = [
                'object_order_id' => $user->id,
                'user_id' => $user->id,
            ];
            FresnsSessionLogs::where('id', $sessionLogId)->update($sessionInput);
        }

        // Check the user of login password errors in the last 1 hour for the user to whom the email or cell phone number belongs.
        // If it reaches 5 times, the login will be restricted.
        // session_logs > object_type=3
        $startTime = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $sessionCount = FresnsSessionLogs::where('created_at', '>=', $startTime)
        ->where('user_id', $user->id)
        ->where('object_result', FresnsSessionLogsConfig::OBJECT_RESULT_ERROR)
        ->where('object_type', FresnsSessionLogsConfig::OBJECT_TYPE_USER_LOGIN)
        ->count();

        if ($sessionCount >= 5) {
            return $this->pluginError(ErrorCodeService::ACCOUNT_COUNT_ERROR);
        }
        // One of the password or verification code is required
        if (empty($password) && empty($verifyCode)) {
            return $this->pluginError(ErrorCodeService::ACCOUNT_CHECK_ERROR);
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
                if (! Hash::check($password, $user->password)) {
                    return $this->pluginError(ErrorCodeService::ACCOUNT_PASSWORD_INVALID);
                }
            }
        }

        if ($user->is_enable == 0) {
            return $this->pluginError(ErrorCodeService::USER_IS_ENABLE_ERROR);
        }
        $langTag = request()->header('langTag');
        $service = new FresnsUsersService();
        $data = $service->getUserDetail($user->id, $langTag);
        // Update the last_login_at field in the users table
        FresnsUsers::where('id', $user->id)->update(['last_login_at' => date('Y-m-d H:i:s', time())]);

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $user->id, null, $user->id);
        }

        return $this->pluginSuccess($data);
    }

    public function userDetailHandler($input)
    {
        $uid = $input['uid'];
        $uid = DB::table(FresnsUsersConfig::CFG_TABLE)->where('uuid', $uid)->value('id');

        $langTag = request()->header('langTag');
        $service = new FresnsUsersService();
        $data = $service->getUserDetail($uid, $langTag);

        return $this->pluginSuccess($data);
    }
}
