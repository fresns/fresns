<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Info;

use App\Helpers\DateHelper;
use App\Helpers\StrHelper;
use App\Http\Center\Common\ErrorCodeService;
use App\Http\Center\Common\GlobalService;
use App\Http\Center\Common\ValidateService;
use App\Http\Center\Helper\CmdRpcHelper;
use App\Http\FresnsApi\Base\FresnsBaseApiController;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsCmd\FresnsCmdWords;
use App\Http\FresnsCmd\FresnsCmdWordsConfig;
use App\Http\FresnsDb\FresnsCommentLogs\FresnsCommentLogs;
use App\Http\FresnsDb\FresnsCommentLogs\FresnsCommentLogsConfig;
use App\Http\FresnsDb\FresnsComments\FresnsComments;
use App\Http\FresnsDb\FresnsComments\FresnsCommentsConfig;
use App\Http\FresnsDb\FresnsDialogMessages\FresnsDialogMessages;
use App\Http\FresnsDb\FresnsDialogs\FresnsDialogs;
use App\Http\FresnsDb\FresnsEmojis\FresnsEmojisConfig;
use App\Http\FresnsDb\FresnsEmojis\FresnsEmojisService;
use App\Http\FresnsDb\FresnsExtends\FresnsExtends;
use App\Http\FresnsDb\FresnsExtends\FresnsExtendsConfig;
use App\Http\FresnsDb\FresnsFileAppends\FresnsFileAppends;
use App\Http\FresnsDb\FresnsFileLogs\FresnsFileLogs;
use App\Http\FresnsDb\FresnsFiles\FresnsFiles;
use App\Http\FresnsDb\FresnsGroups\FresnsGroups;
use App\Http\FresnsDb\FresnsGroups\FresnsGroupsConfig;
use App\Http\FresnsDb\FresnsHashtags\FresnsHashtags;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguages;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollows;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRelsService;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRolesService;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsNotifies\FresnsNotifies;
use App\Http\FresnsDb\FresnsPluginCallbacks\FresnsPluginCallbacks;
use App\Http\FresnsDb\FresnsPluginCallbacks\FresnsPluginCallbacksService;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsagesService;
use App\Http\FresnsDb\FresnsPostLogs\FresnsPostLogs;
use App\Http\FresnsDb\FresnsPostLogs\FresnsPostLogsConfig;
use App\Http\FresnsDb\FresnsPosts\FresnsPosts;
use App\Http\FresnsDb\FresnsPosts\FresnsPostsConfig;
use App\Http\FresnsDb\FresnsStopWords\FresnsStopWordsService;
use App\Http\FresnsDb\FresnsUsers\FresnsUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FsControllerApi extends FresnsBaseApiController
{
    public function __construct()
    {
        $this->service = new FsService();
        parent::__construct();
    }

    // Configs
    public function configs(Request $request)
    {
        $itemTag = $request->input('itemTag');
        $itemKey = $request->input('itemKey');
        $data = [];
        if (empty($itemTag) && empty($itemKey)) {
            $data = ApiConfigHelper::getConfigsListsApi();
        } else {
            if (! empty($itemTag)) {
                $itemTagArr = explode(',', $itemTag);
                foreach ($itemTagArr as $v) {
                    $data = array_merge($data, ApiConfigHelper::getConfigByItemTagApi($v));
                }
            }
            if (! empty($itemKey)) {
                $itemKeyArr = explode(',', $itemKey);
                foreach ($itemKeyArr as $v) {
                    $data = array_merge($data, ApiConfigHelper::getConfigByItemKeyApi($v));
                }
            }
        }

        $item = [];
        $item['list'] = $data;
        $pagination['total'] = count($data);
        $pagination['current'] = 1;
        $pagination['pageSize'] = count($data);
        $pagination['lastPage'] = 1;
        $item['pagination'] = $pagination;

        $this->success($item);
    }

    // Emojis
    public function emojis(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $currentPage = $request->input('page', 1);
        $request->offsetSet('currentPage', $currentPage);
        $request->offsetSet('pageSize', $pageSize);
        $request->offsetSet('type', FresnsEmojisConfig::TYPE_GROUP);
        $request->offsetSet('is_enable', 1);
        $FresnsEmojisService = new FresnsEmojisService();

        $FresnsEmojisService->setResource(FresnsEmojisResource::class);
        $data = $FresnsEmojisService->searchData();

        $this->success($data);
    }

    // Stop Words
    public function stopWords(Request $request)
    {
        $currentPage = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 100);
        $request->offsetSet('currentPage', $currentPage);
        $request->offsetSet('pageSize', $pageSize);

        $FresnsStopWordsService = new FresnsStopWordsService();

        $FresnsStopWordsService->setResource(FresnsStopWordsResource::class);
        $data = $FresnsStopWordsService->searchData();

        $this->success($data);
    }

    // Upload Log
    public function uploadLog(Request $request)
    {
        $rule = [
            'objectName' => 'required',
            'objectAction' => 'required',
            'objectResult' => 'required|numeric',
            // 'objectOrderId'    => 'numeric',
            // 'deviceInfo'    => 'required|json',
            'moreJson' => 'json',
        ];
        ValidateService::validateRule($request, $rule);

        $langTag = ApiLanguageHelper::getLangTagByHeader();

        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_UPLOAD_SESSION_LOG;
        $input['platform'] = $request->header('platform');
        $input['version'] = $request->header('version');
        $input['versionInt'] = $request->header('versionInt');
        $input['objectName'] = $request->input('objectName');
        $input['objectAction'] = $request->input('objectAction');
        $input['objectResult'] = $request->input('objectResult');
        $input['objectType'] = $request->input('objectType');
        $input['langTag'] = $langTag;
        $input['objectOrderId'] = $request->input('objectOrderId');
        $input['deviceInfo'] = $request->header('deviceInfo');
        $input['uid'] = $this->uid;
        $input['mid'] = $this->mid;
        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            $this->errorCheckInfo($resp);
        }

        $this->success();
    }

    // Input Tips
    public function inputTips(Request $request)
    {
        $rule = [
            'queryType' => 'required|numeric|in:1,2,3,4,5',
            'queryKey' => 'required',
        ];
        ValidateService::validateRule($request, $rule);

        $queryType = $request->input('queryType');
        $queryKey = $request->input('queryKey');
        $langTag = ApiLanguageHelper::getLangTagByHeader();
        $mid = GlobalService::getGlobalKey('member_id');
        $data = [];

        $followIdArr = [];
        if ($mid && $queryType != 5) {
            $followIdArr = FresnsMemberFollows::where('member_id', $mid)->where('follow_type',
                $queryType)->pluck('follow_id')->toArray();
        }

        switch ($queryType) {

            case 1:
                $idArr = FresnsMembers::where('name', 'LIKE', "%$queryKey%")->orWhere('nickname', 'LIKE', "%$queryKey%")->pluck('id')->toArray();
                $idArr = FsService::getMemberFollows($queryType, $idArr, $mid);
                $memberArr = FresnsMembers::whereIn('id', $idArr)->where('is_enable', 1)->get()->toArray();

                foreach ($memberArr as $v) {
                    $item = [];
                    $item['id'] = $v['uuid'];
                    $item['name'] = $v['name'];
                    $item['nickname'] = $v['nickname'];
                    $followStatus = 0;
                    if (in_array($v['id'], $followIdArr)) {
                        $followStatus = 1;
                    }
                    $item['followStatus'] = $followStatus;
                    if (empty($v['avatar_file_url']) && empty($v['avatar_file_id'])) {
                        $defaultAvatar = ApiConfigHelper::getConfigByItemKey('default_avatar');
                        $memberAvatar = ApiFileHelper::getImageAvatarUrl($defaultAvatar);
                    } else {
                        $memberAvatar = ApiFileHelper::getImageAvatarUrlByFileIdUrl($v['avatar_file_id'], $v['avatar_file_url']);
                    }
                    $item['image'] = $memberAvatar;
                    $item['title'] = '';
                    $item['titleColor'] = '';
                    $item['descPrimary'] = '';
                    $item['descPrimaryColor'] = '';
                    $item['descSecondary'] = '';
                    $item['descSecondaryColor'] = '';
                    $data[] = $item;
                }
                break;
            case 2:
                $langIdArr = FresnsLanguages::where('table_name', FresnsGroupsConfig::CFG_TABLE)->where('table_field', 'name')->where('lang_content', 'LIKE', "%$queryKey%")->where('lang_tag', $langTag)->pluck('table_id')->toArray();
                $idArr = FsService::getMemberFollows($queryType, $langIdArr, $mid);
                $groupsArr = FresnsGroups::whereIn('id', $idArr)->where('is_enable', 1)->get()->toArray();
                $lenguagesMap = FresnsLanguages::where('table_name', FresnsGroupsConfig::CFG_TABLE)->where('table_field', 'name')->where('lang_tag', $langTag)->whereIn('table_id', $idArr)->pluck('lang_content', 'table_id')->toArray();

                foreach ($groupsArr as $v) {
                    $item = [];
                    $item['id'] = $v['uuid'];
                    $item['name'] = $lenguagesMap[$v['id']] ?? '';
                    $item['nickname'] = '';
                    $followStatus = 0;
                    if (in_array($v['id'], $followIdArr)) {
                        $followStatus = 1;
                    }
                    $item['followStatus'] = $followStatus;
                    $item['image'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['cover_file_id'], $v['cover_file_url']);
                    $item['title'] = '';
                    $item['titleColor'] = '';
                    $item['descPrimary'] = '';
                    $item['descPrimaryColor'] = '';
                    $item['descSecondary'] = '';
                    $item['descSecondaryColor'] = '';
                    $data[] = $item;
                }
                break;
            case 3:
                $idArr = FresnsHashtags::where('name', 'LIKE', "%$queryKey%")->pluck('id')->toArray();
                $idArr = FsService::getMemberFollows($queryType, $idArr, $mid);
                $hashtagsArr = FresnsHashtags::whereIn('id', $idArr)->where('is_enable', 1)->get()->toArray();
                foreach ($hashtagsArr as $v) {
                    $item = [];
                    $item['id'] = $v['slug'];
                    $item['name'] = $v['name'];
                    $item['nickname'] = '';
                    $followStatus = 0;
                    if (in_array($v['id'], $followIdArr)) {
                        $followStatus = 1;
                    }
                    $item['followStatus'] = $followStatus;
                    $item['image'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['cover_file_id'], $v['cover_file_url']);
                    $item['title'] = '';
                    $item['titleColor'] = '';
                    $item['descPrimary'] = '';
                    $item['descPrimaryColor'] = '';
                    $item['descSecondary'] = '';
                    $item['descSecondaryColor'] = '';
                    $data[] = $item;
                }
                break;
            case 4:
                $idArr = FresnsPosts::where('title', 'LIKE', "%$queryKey%")->pluck('id')->toArray();
                $idArr = FsService::getMemberFollows($queryType, $idArr, $mid);
                $hashtagsArr = FresnsPosts::whereIn('id', $idArr)->where('is_enable', 1)->get()->toArray();
                foreach ($hashtagsArr as $v) {
                    $item = [];
                    $item['id'] = $v['uuid'];
                    $item['name'] = $v['title'];
                    $item['nickname'] = '';
                    $followStatus = 0;
                    if (in_array($v['id'], $followIdArr)) {
                        $followStatus = 1;
                    }
                    $item['followStatus'] = $followStatus;
                    $item['image'] = '';
                    $item['title'] = '';
                    $item['titleColor'] = '';
                    $item['descPrimary'] = '';
                    $item['descPrimaryColor'] = '';
                    $item['descSecondary'] = '';
                    $item['descSecondaryColor'] = '';
                    $data[] = $item;
                }
                break;
            case 5:
                $langIdArr = FresnsLanguages::where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_field', 'title')->where('lang_content', 'LIKE', "%$queryKey%")->where('lang_tag', $langTag)->pluck('table_id')->toArray();
                $idArr = FsService::getMemberFollows($queryType, $langIdArr, $mid);
                $extendArr = FresnsExtends::whereIn('id', $idArr)->get()->toArray();
                $lenguagesMap = FresnsLanguages::where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_field', 'title')->whereIn('table_id', $idArr)->where('lang_tag', $langTag)->pluck('lang_content', 'table_id')->toArray();
                $descSecondaryMap = FresnsLanguages::where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_field', 'desc_secondary')->whereIn('table_id', $idArr)->where('lang_tag', $langTag)->pluck('lang_content', 'table_id')->toArray();
                $descPrimaryMap = FresnsLanguages::where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_field', 'desc_primary')->whereIn('table_id', $idArr)->where('lang_tag', $langTag)->pluck('lang_content', 'table_id')->toArray();

                foreach ($extendArr as $v) {
                    $item = [];
                    $item['id'] = $v['uuid'];
                    $item['name'] = '';
                    $item['nickname'] = '';
                    $item['image'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['cover_file_id'], $v['cover_file_url']);
                    $item['title'] = $lenguagesMap[$v['id']] ?? '';
                    $item['titleColor'] = $v['title_color'];
                    $item['descPrimary'] = $descPrimaryMap[$v['id']] ?? '';
                    $item['descPrimaryColor'] = $v['desc_primary_color'];
                    $item['descSecondary'] = $descSecondaryMap[$v['id']] ?? '';
                    $item['descSecondaryColor'] = $v['desc_secondary_color'];
                    $data[] = $item;
                }
                break;
            default:
                // code...
                break;
        }

        $this->success($data);
    }

    // Extensions Info
    public function extensions(Request $request)
    {
        $mid = GlobalService::getGlobalKey('member_id');

        $rule = [
            'type' => [
                'required',
                'numeric',
                'in:3,4,9',
            ],
            'scene' => 'in:1,2,3',
        ];
        $type = $request->input('type');
        $scene = $request->input('scene');
        // When the plugin_usages > member_roles field has a value, you need to determine if the member requested by the current interface is among the eligible roles
        // If not present, it is not output. If the field has a value and the interface has no member parameters, it defaults to being an unprivileged user.
        if (! $mid) {
            $idArr = FresnsPluginUsages::where('member_roles', null)->pluck('id')->toArray();
            $request->offsetSet('ids', implode(',', $idArr));
        } else {
            $noRoleIdArr = FresnsPluginUsages::where('member_roles', null)->pluck('id')->toArray();
            // Query Role
            $memberRole = FresnsMemberRoleRels::where('member_id', $mid)->where('expired_at', '<', date('Y-m-d H:i:s', time()))->first();
            $RoleIdArr = [];
            if ($memberRole) {
                $memberRole = FresnsMemberRoleRels::where('member_id', $mid)->where('expired_at', '<', date('Y-m-d H:i:s', time()))->first();
                $RoleIdArr = FresnsPluginUsages::where('member_roles', 'like', '%'.$memberRole['role_id'].'%')->pluck('id')->toArray();
                $RoleIdArr = $RoleIdArr;
            }
            $idArr = array_merge($noRoleIdArr, $RoleIdArr);
            $request->offsetSet('ids', implode(',', $idArr));
        }
        ValidateService::validateRule($request, $rule);
        $currentPage = $request->input('page', 1) ?? 1;
        $pageSize = $request->input('pageSize', 30) ?? 30;
        $request->offsetSet('currentPage', $currentPage);
        $request->offsetSet('is_enable', 1);
        $request->offsetSet('pageSize', $pageSize);
        $FsPluginUsagesService = new FresnsPluginUsagesService();
        $FsPluginUsagesService->setResource(FresnsPluginUsagesResource::class);
        $list = $FsPluginUsagesService->searchData();
        $data = [
            'pagination' => $list['pagination'],
            'list' => $list['list'],
        ];
        $this->success($data);
    }

    // Send Verify Code
    public function sendVerifyCode(Request $request)
    {
        $rule = [
            'type' => [
                'required',
                'numeric',
                'in:1,2',
            ],
            'useType' => [
                'required',
                'numeric',
                'in:1,2,3,4,5',
            ],
            'templateId' => [
                'required',
                'numeric',
                'in:1,2,3,4,5,6,7',
            ],
            // 'account' => 'required'
        ];
        ValidateService::validateRule($request, $rule);
        $useType = $request->input('useType');
        $type = $request->input('type');
        $templateId = $request->input('templateId');
        $account = $request->input('account');
        $langTag = $request->header('langTag');
        $user_id = GlobalService::getGlobalKey('user_id');
        $checkInfo = FsChecker::checkVerifyCode($type, $useType, $account);
        if (is_array($checkInfo)) {
            return $this->errorCheckInfo($checkInfo);
        }

        $type = $request->input('type');
        $useType = $request->input('useType');
        $templateId = $request->input('templateId');
        $account = $request->input('account');

        $countryCode = $request->input('countryCode');
        if ($useType == 4) {
            $userInfo = FresnsUsers::find($user_id);
            if (empty($userInfo)) {
                $this->error(ErrorCodeService::USER_CHECK_ERROR);
            }
            if ($type == 1) {
                $account = $userInfo['email'];
            } else {
                $account = $userInfo['pure_phone'];
            }
        }
        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_SEND_CODE;
        $input = [
            'type' => $type,
            'templateId' => $templateId,
            'account' => $account,
            'langTag' => $langTag,
            'countryCode' => $countryCode,
        ];
        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            $this->errorCheckInfo($resp);
        }
        $this->success($resp['output']);
    }

    // Download File
    public function downloadFile(Request $request)
    {
        // Calibration parameters
        $rule = [
            'type' => 'required|in:1,2,3',
            'uuid' => 'required',
            'fid' => 'required',
        ];
        ValidateService::validateRule($request, $rule);

        $uid = GlobalService::getGlobalKey('user_id');
        $mid = GlobalService::getGlobalKey('member_id');

        // Verify that the content exists
        $type = $request->input('type');
        $uuid = $request->input('uuid');
        $fid = $request->input('fid');
        switch ($type) {
            case 1:
                // It is necessary to verify that the file belongs to the corresponding source target, such as whether the file belongs to the post.
                $typeData = FresnsPosts::where('uuid', $uuid)->first();
                if (empty($typeData)) {
                    $this->error(ErrorCodeService::FILE_EXIST_ERROR);
                }
                // Query the log table id corresponding to the main table
                $postLogsIdArr = FresnsPostLogs::where('post_id', $typeData['id'])->pluck('id')->toArray();
                $files = FresnsFiles::where('uuid', $fid)->where('table_name', FresnsPostLogsConfig::CFG_TABLE)->whereIn('table_id', $postLogsIdArr)->first();
                if (empty($files)) {
                    $this->error(ErrorCodeService::FILE_EXIST_ERROR);
                }
                // Post attachments need to determine if the post has permission enabled posts > is_allow
                if (! empty($typeData)) {
                    if ($typeData['is_allow'] == FresnsPostsConfig::IS_ALLOW_1) {
                        // If the post has read access, determine if the member requesting the download itself and the member's primary role are in the authorization list post_allows table
                        $count = DB::table('post_allows')->where('post_id', $typeData['id'])->where('type', 2)->where('object_id', $mid)->count();
                        if (empty($count)) {
                            $this->error(ErrorCodeService::POST_BROWSE_ERROR);
                        }
                    }
                }

                break;
            case 2:
                // It is necessary to verify that the file belongs to the corresponding source target, such as whether the file belongs to the post.
                $typeData = FresnsComments::where('uuid', $uuid)->first();
                if (empty($typeData)) {
                    $this->error(ErrorCodeService::FILE_EXIST_ERROR);
                }
                // Query the log table id corresponding to the main table
                $commentLogsIdArr = FresnsCommentLogs::where('post_id', $typeData['id'])->pluck('id')->toArray();
                $files = FresnsFiles::where('uuid', $fid)->where('table_name', FresnsCommentLogsConfig::CFG_TABLE)->whereIn('table_id', $commentLogsIdArr)->first();
                if (empty($files)) {
                    $this->error(ErrorCodeService::FILE_EXIST_ERROR);
                }
                break;
            default:
                $typeData = FresnsExtends::where('uuid', $uuid)->first();
                // It is necessary to verify that the file belongs to the corresponding source target, such as whether the file belongs to the post.
                if (empty($typeData)) {
                    $this->error(ErrorCodeService::FILE_EXIST_ERROR);
                }

                $files = FresnsFiles::where('uuid', $fid)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_id', $typeData['id'])->first();
                if (empty($files)) {
                    $this->error(ErrorCodeService::FILE_EXIST_ERROR);
                }
                break;
        }

        if (empty($typeData)) {
            $this->error(ErrorCodeService::FILE_EXIST_ERROR);
        }

        $roleId = FresnsMemberRoleRelsService::getMemberRoleRels($mid);
        $permission = FresnsMemberRoles::where('id', $roleId)->value('permission');
        if (empty($permission)) {
            $this->error(ErrorCodeService::ROLE_NO_CONFIG_ERROR);
        }
        $permissionArr = json_decode($permission, true);
        $permissionMap = FresnsMemberRolesService::getPermissionMap($permissionArr);
        if (empty($permissionMap)) {
            $this->error(ErrorCodeService::ROLE_NO_CONFIG_ERROR);
        }
        $downloadFileCount = $permissionMap['download_file_count'];
        // Calculate whether the maximum number of downloads has been reached in the last 24 hours
        $start = date('Y-m-d H:i:s', strtotime('-1 day'));
        $end = date('Y-m-d H:i:s', time());
        $logCount = FresnsFileLogs::where('user_id', $uid)->where('member_id', $mid)->where('created_at', '>=', $start)->where('created_at', '<=', $end)->count();
        if ($logCount >= $downloadFileCount) {
            $this->error(ErrorCodeService::ROLE_DOWNLOAD_ERROR);
        }

        $files = FresnsFiles::where('uuid', $fid)->first();
        $uuid = $files['uuid'];
        // If the checksum passes, populate the file_logs table with records
        $input = [
            'file_id' => $files['id'],
            'file_type' => $files['file_type'],
            'user_id' => $uid,
            'member_id' => $mid,
            'object_type' => $type,
            'object_id' => $files['table_id'],
        ];
        FresnsFileLogs::insert($input);
        $data = [];
        $filePath = $files['file_path'];
        $downloadUrl = '';
        switch ($files['file_type']) {
            case 1:
                $status = ApiConfigHelper::getConfigByItemKey('images_url_status');
                $domain = ApiConfigHelper::getConfigByItemKey('images_bucket_domain');
                $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_IMAGE;
                $input['fid'] = $uuid;
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $downloadUrl = $domain.$filePath;
                } else {
                    $output = $resp['output'];
                    $downloadUrl = $output['imageBigUrl'];
                    $originalUrl = $output['originalUrl'];
                }
                break;
            case 2:
                $status = ApiConfigHelper::getConfigByItemKey('videos_url_status');
                $domain = ApiConfigHelper::getConfigByItemKey('videos_bucket_domain');
                $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_VIDEO;
                $input['fid'] = $uuid;
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $downloadUrl = $domain.$filePath;
                } else {
                    $output = $resp['output'];
                    $downloadUrl = $output['videoUrl'];
                    $originalUrl = $output['originalUrl'];
                }
                break;
            case 3:
                $status = ApiConfigHelper::getConfigByItemKey('audios_url_status');
                $domain = ApiConfigHelper::getConfigByItemKey('audios_bucket_domain');
                $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_AUDIO;
                $input['fid'] = $uuid;
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $downloadUrl = $domain.$filePath;
                } else {
                    $output = $resp['output'];
                    $downloadUrl = $output['audioUrl'];
                    $originalUrl = $output['originalUrl'];
                }
                break;
            default:
                $status = ApiConfigHelper::getConfigByItemKey('docs_url_status');
                $domain = ApiConfigHelper::getConfigByItemKey('docs_bucket_domain');
                $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_DOC;
                $input['fid'] = $uuid;
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $downloadUrl = $domain.$filePath;
                } else {
                    $output = $resp['output'];
                    $downloadUrl = $output['docUrl'];
                    $originalUrl = $output['originalUrl'];
                }
                break;
        }

        $data['downloadUrl'] = $downloadUrl;
        if ($status == false || empty($originalUrl)) {
            $originalUrl = $domain.FresnsFileAppends::where('file_id', $files['id'])->value('file_original_path');
        }
        $data['originalUrl'] = $originalUrl;

        $this->success($data);
    }

    // Overview
    public function overview(Request $request)
    {
        $member_id = GlobalService::getGlobalKey('member_id');
        // Notifications of unread numbers
        $system_count = FresnsNotifies::where('member_id', $member_id)->where('source_type', FsConfig::SOURCE_TYPE_1)->where('status', FsConfig::NO_READ)->count();
        $follow_count = FresnsNotifies::where('member_id', $member_id)->where('source_type', FsConfig::SOURCE_TYPE_2)->where('status', FsConfig::NO_READ)->count();
        $like_count = FresnsNotifies::where('member_id', $member_id)->where('source_type', FsConfig::SOURCE_TYPE_3)->where('status', FsConfig::NO_READ)->count();
        $comment_count = FresnsNotifies::where('member_id', $member_id)->where('source_type', FsConfig::SOURCE_TYPE_4)->where('status', FsConfig::NO_READ)->count();
        $mention_count = FresnsNotifies::where('member_id', $member_id)->where('source_type', FsConfig::SOURCE_TYPE_5)->where('status', FsConfig::NO_READ)->count();
        $recommend_count = FresnsNotifies::where('member_id', $member_id)->where('source_type', FsConfig::SOURCE_TYPE_6)->where('status', FsConfig::NO_READ)->count();
        // Dialogs of unread numbers
        $aStatusNoRead = FresnsDialogs::where('a_member_id', $member_id)->where('a_status', FsConfig::NO_READ)->count();
        $bStatusNoRead = FresnsDialogs::where('b_member_id', $member_id)->where('b_status', FsConfig::NO_READ)->count();
        $dialogNoRead = $aStatusNoRead + $bStatusNoRead;
        // Dialog Messages of unread numbers
        $dialogMessage = FresnsDialogMessages::where('recv_member_id', $member_id)->where('recv_read_at', null)->count();
        $dialogUnread = [
            'dialog' => $dialogNoRead,
            'message' => $dialogMessage,
        ];
        $notifyUnread = [
            'system' => $system_count,
            'follow' => $follow_count,
            'like' => $like_count,
            'comment' => $comment_count,
            'mention' => $mention_count,
            'recommend' => $recommend_count,
        ];
        $data = [
            'dialogUnread' => $dialogUnread,
            'notifyUnread' => $notifyUnread,
        ];
        $this->success($data);
    }

    // Callback Info
    public function callbacks(Request $request)
    {
        // Calibration parameters
        $rule = [
            'unikey' => 'required',
            'uuid' => 'required',
        ];
        ValidateService::validateRule($request, $rule);
        $uuid = $request->input('uuid');
        $checkInfo = FsChecker::checkPluginCallback($uuid);
        if (is_array($checkInfo)) {
            return $this->errorCheckInfo($checkInfo);
        }
        $id = FresnsPluginCallbacks::where('uuid', $uuid)->first();
        $service = new FresnsPluginCallbacksService();
        $service->setResourceDetail(FresnsPluginCallbacksResource::class);
        $detail = $service->detail($id['id']);
        $data = $detail['detail'];
        $this->success($data);
    }
}
