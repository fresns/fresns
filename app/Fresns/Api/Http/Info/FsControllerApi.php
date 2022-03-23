<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Info;

use App\Fresns\Api\Helpers\StrHelper;
use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\Center\Common\ValidateService;
use App\Fresns\Api\Center\Helper\CmdRpcHelper;
use App\Fresns\Api\Http\Base\FsApiController;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Fresns\Api\Helpers\ApiLanguageHelper;
use App\Fresns\Api\FsCmd\FresnsCmdWords;
use App\Fresns\Api\FsCmd\FresnsCmdWordsConfig;
use App\Fresns\Api\FsDb\FresnsCommentLogs\FresnsCommentLogs;
use App\Fresns\Api\FsDb\FresnsCommentLogs\FresnsCommentLogsConfig;
use App\Fresns\Api\FsDb\FresnsComments\FresnsComments;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigs;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigsService;
use App\Fresns\Api\FsDb\FresnsDialogMessages\FresnsDialogMessages;
use App\Fresns\Api\FsDb\FresnsDialogs\FresnsDialogs;
use App\Fresns\Api\FsDb\FresnsStickers\FresnsStickersConfig;
use App\Fresns\Api\FsDb\FresnsStickers\FresnsStickersService;
use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtends;
use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtendsConfig;
use App\Fresns\Api\FsDb\FresnsFileAppends\FresnsFileAppends;
use App\Fresns\Api\FsDb\FresnsFileLogs\FresnsFileLogs;
use App\Fresns\Api\FsDb\FresnsFiles\FresnsFiles;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroups;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroupsConfig;
use App\Fresns\Api\FsDb\FresnsHashtags\FresnsHashtags;
use App\Fresns\Api\FsDb\FresnsLanguages\FresnsLanguages;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollows;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRoles;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRolesService;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRoles;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRolesService;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsNotifies\FresnsNotifies;
use App\Fresns\Api\FsDb\FresnsPluginCallbacks\FresnsPluginCallbacks;
use App\Fresns\Api\FsDb\FresnsPluginCallbacks\FresnsPluginCallbacksService;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsagesService;
use App\Fresns\Api\FsDb\FresnsPostLogs\FresnsPostLogs;
use App\Fresns\Api\FsDb\FresnsPostLogs\FresnsPostLogsConfig;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPosts;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPostsConfig;
use App\Fresns\Api\FsDb\FresnsBlockWords\FresnsBlockWordsService;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FsControllerApi extends FsApiController
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
        $pageSize = $request->input('pageSize', 100);
        $currentPage = $request->input('page', 1);

        $request->offsetSet('is_restful', 1);
        if (! empty($itemTag) || ! empty($itemKey)) {
            $intersectIdArr = [];
            if (! empty($itemTag)) {
                $itemTagArr = explode(',', $itemTag);
                $intersectIdArr[] = FresnsConfigs::whereIn('item_tag', $itemTagArr)->pluck('id')->toArray();
            }
            if (! empty($itemKey)) {
                $itemKeyArr = explode(',', $itemKey);
                $intersectIdArr[] = FresnsConfigs::whereIn('item_key', $itemKeyArr)->pluck('id')->toArray();
            }
            $idArr = StrHelper::SearchIntersect($intersectIdArr);

            $request->offsetSet('ids', $idArr);
        }
        $request->offsetSet('currentPage', $currentPage);
        $request->offsetSet('pageSize', $pageSize);
        $FresnsConfigsService = new FresnsConfigsService();

        $FresnsConfigsService->setResource(FresnsConfigsResource::class);
        $data = $FresnsConfigsService->searchData();

        $this->success($data);
    }

    // Stickers
    public function stickers(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $currentPage = $request->input('page', 1);
        $request->offsetSet('currentPage', $currentPage);
        $request->offsetSet('pageSize', $pageSize);
        $request->offsetSet('type', FresnsStickersConfig::TYPE_GROUP);
        $request->offsetSet('is_enable', 1);
        $FresnsStickersService = new FresnsStickersService();

        $FresnsStickersService->setResource(FresnsStickersResource::class);
        $data = $FresnsStickersService->searchData();

        $this->success($data);
    }

    // Block Words
    public function blockWords(Request $request)
    {
        $currentPage = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 100);
        $request->offsetSet('currentPage', $currentPage);
        $request->offsetSet('pageSize', $pageSize);

        $FresnsBlockWordsService = new FresnsBlockWordsService();

        $FresnsBlockWordsService->setResource(FresnsBlockWordsResource::class);
        $data = $FresnsBlockWordsService->searchData();

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
        $input['aid'] = $this->aid;
        $input['uid'] = $this->uid;
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
        $uid = GlobalService::getGlobalKey('user_id');
        $data = [];

        $followIdArr = [];
        if ($uid && $queryType != 5) {
            $followIdArr = FresnsUserFollows::where('user_id', $uid)->where('follow_type',
                $queryType)->pluck('follow_id')->toArray();
        }

        switch ($queryType) {

            case 1:
                $idArr = FresnsUsers::where('name', 'LIKE', "%$queryKey%")->orWhere('nickname', 'LIKE', "%$queryKey%")->pluck('id')->toArray();
                $idArr = FsService::getUserFollows($queryType, $idArr, $uid);
                $userArr = FresnsUsers::whereIn('id', $idArr)->where('is_enable', 1)->get()->toArray();

                foreach ($userArr as $v) {
                    $item = [];
                    $item['fsid'] = $v['uid'];
                    $item['name'] = $v['name'];
                    $item['nickname'] = $v['nickname'];
                    $followStatus = 0;
                    if (in_array($v['id'], $followIdArr)) {
                        $followStatus = 1;
                    }
                    $item['followStatus'] = $followStatus;
                    if (empty($v['avatar_file_url']) && empty($v['avatar_file_id'])) {
                        $defaultAvatar = ApiConfigHelper::getConfigByItemKey('default_avatar');
                        $userAvatar = ApiFileHelper::getImageAvatarUrl($defaultAvatar);
                    } else {
                        $userAvatar = ApiFileHelper::getImageAvatarUrlByFileIdUrl($v['avatar_file_id'], $v['avatar_file_url']);
                    }
                    $item['image'] = $userAvatar;
                    $item['title'] = null;
                    $item['titleColor'] = null;
                    $item['descPrimary'] = null;
                    $item['descPrimaryColor'] = null;
                    $item['descSecondary'] = null;
                    $item['descSecondaryColor'] = null;
                    $data[] = $item;
                }
                break;
            case 2:
                $langIdArr = FresnsLanguages::where('table_name', FresnsGroupsConfig::CFG_TABLE)->where('table_column', 'name')->where('lang_content', 'LIKE', "%$queryKey%")->where('lang_tag', $langTag)->pluck('table_id')->toArray();
                $idArr = FsService::getUserFollows($queryType, $langIdArr, $uid);
                $groupsArr = FresnsGroups::whereIn('id', $idArr)->where('is_enable', 1)->get()->toArray();
                $languagesMap = FresnsLanguages::where('table_name', FresnsGroupsConfig::CFG_TABLE)->where('table_column', 'name')->where('lang_tag', $langTag)->whereIn('table_id', $idArr)->pluck('lang_content', 'table_id')->toArray();

                foreach ($groupsArr as $v) {
                    $item = [];
                    $item['fsid'] = $v['gid'];
                    $item['name'] = $languagesMap[$v['id']] ?? null;
                    $item['nickname'] = null;
                    $followStatus = 0;
                    if (in_array($v['id'], $followIdArr)) {
                        $followStatus = 1;
                    }
                    $item['followStatus'] = $followStatus;
                    $item['image'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['cover_file_id'], $v['cover_file_url']);
                    $item['title'] = null;
                    $item['titleColor'] = null;
                    $item['descPrimary'] = null;
                    $item['descPrimaryColor'] = null;
                    $item['descSecondary'] = null;
                    $item['descSecondaryColor'] = null;
                    $data[] = $item;
                }
                break;
            case 3:
                $idArr = FresnsHashtags::where('name', 'LIKE', "%$queryKey%")->pluck('id')->toArray();
                $idArr = FsService::getUserFollows($queryType, $idArr, $uid);
                $hashtagsArr = FresnsHashtags::whereIn('id', $idArr)->where('is_enable', 1)->get()->toArray();
                foreach ($hashtagsArr as $v) {
                    $item = [];
                    $item['fsid'] = $v['slug'];
                    $item['name'] = $v['name'];
                    $item['nickname'] = null;
                    $followStatus = 0;
                    if (in_array($v['id'], $followIdArr)) {
                        $followStatus = 1;
                    }
                    $item['followStatus'] = $followStatus;
                    $item['image'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['cover_file_id'], $v['cover_file_url']);
                    $item['title'] = null;
                    $item['titleColor'] = null;
                    $item['descPrimary'] = null;
                    $item['descPrimaryColor'] = null;
                    $item['descSecondary'] = null;
                    $item['descSecondaryColor'] = null;
                    $data[] = $item;
                }
                break;
            case 4:
                $idArr = FresnsPosts::where('title', 'LIKE', "%$queryKey%")->pluck('id')->toArray();
                $idArr = FsService::getUserFollows($queryType, $idArr, $uid);
                $hashtagsArr = FresnsPosts::whereIn('id', $idArr)->where('is_enable', 1)->get()->toArray();
                foreach ($hashtagsArr as $v) {
                    $item = [];
                    $item['fsid'] = $v['pid'];
                    $item['name'] = $v['title'];
                    $item['nickname'] = null;
                    $followStatus = 0;
                    if (in_array($v['id'], $followIdArr)) {
                        $followStatus = 1;
                    }
                    $item['followStatus'] = $followStatus;
                    $item['image'] = null;
                    $item['title'] = null;
                    $item['titleColor'] = null;
                    $item['descPrimary'] = null;
                    $item['descPrimaryColor'] = null;
                    $item['descSecondary'] = null;
                    $item['descSecondaryColor'] = null;
                    $data[] = $item;
                }
                break;
            case 5:
                $langIdArr = FresnsLanguages::where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'title')->where('lang_content', 'LIKE', "%$queryKey%")->where('lang_tag', $langTag)->pluck('table_id')->toArray();
                $idArr = FsService::getUserFollows($queryType, $langIdArr, $uid);
                $extendArr = FresnsExtends::whereIn('id', $idArr)->get()->toArray();
                $languagesMap = FresnsLanguages::where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'title')->whereIn('table_id', $idArr)->where('lang_tag', $langTag)->pluck('lang_content', 'table_id')->toArray();
                $descSecondaryMap = FresnsLanguages::where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'desc_secondary')->whereIn('table_id', $idArr)->where('lang_tag', $langTag)->pluck('lang_content', 'table_id')->toArray();
                $descPrimaryMap = FresnsLanguages::where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'desc_primary')->whereIn('table_id', $idArr)->where('lang_tag', $langTag)->pluck('lang_content', 'table_id')->toArray();

                foreach ($extendArr as $v) {
                    $item = [];
                    $item['fsid'] = $v['eid'];
                    $item['name'] = null;
                    $item['nickname'] = null;
                    $item['image'] = ApiFileHelper::getImageSignUrlByFileIdUrl($v['cover_file_id'], $v['cover_file_url']);
                    $item['title'] = $languagesMap[$v['id']] ?? null;
                    $item['titleColor'] = $v['title_color'];
                    $item['descPrimary'] = $descPrimaryMap[$v['id']] ?? null;
                    $item['descPrimaryColor'] = $v['desc_primary_color'];
                    $item['descSecondary'] = $descSecondaryMap[$v['id']] ?? null;
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
        $uid = GlobalService::getGlobalKey('user_id');

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
        // When the plugin_usages > roles field has a value, you need to determine if the user requested by the current interface is among the eligible roles
        // If not present, it is not output. If the field has a value and the interface has no user parameters, it defaults to being an unprivileged account.
        if (! $uid) {
            $idArr = FresnsPluginUsages::where('roles', null)->pluck('id')->toArray();
            $request->offsetSet('ids', implode(',', $idArr));
        } else {
            $noRoleIdArr = FresnsPluginUsages::where('roles', null)->pluck('id')->toArray();
            // Query Role
            $userRole = FresnsUserRoles::where('user_id', $uid)->where('expired_at', '<', date('Y-m-d H:i:s', time()))->first();
            $RoleIdArr = [];
            if ($userRole) {
                $userRole = FresnsUserRoles::where('user_id', $uid)->where('expired_at', '<', date('Y-m-d H:i:s', time()))->first();
                $RoleIdArr = FresnsPluginUsages::where('roles', 'like', '%'.$userRole['role_id'].'%')->pluck('id')->toArray();
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
        $account_id = GlobalService::getGlobalKey('account_id');
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
            $accountInfo = FresnsAccounts::find($account_id);
            if (empty($accountInfo)) {
                $this->error(ErrorCodeService::ACCOUNT_CHECK_ERROR);
            }
            if ($type == 1) {
                $account = $accountInfo['email'];
            } else {
                $account = $accountInfo['pure_phone'];
                $countryCode = $accountInfo['country_code'];
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
            'fsid' => 'required',
            'fid' => 'required',
        ];
        ValidateService::validateRule($request, $rule);

        $aid = GlobalService::getGlobalKey('account_id');
        $uid = GlobalService::getGlobalKey('user_id');

        // Verify that the content exists
        $type = $request->input('type');
        $fsid = $request->input('fsid');
        $fid = $request->input('fid');
        switch ($type) {
            case 1:
                // It is necessary to verify that the file belongs to the corresponding source target, such as whether the file belongs to the post.
                $typeData = FresnsPosts::where('pid', $fsid)->first();
                if (empty($typeData)) {
                    $this->error(ErrorCodeService::FILE_EXIST_ERROR);
                }
                // Query the log table id corresponding to the main table
                $postLogsIdArr = FresnsPostLogs::where('post_id', $typeData['id'])->pluck('id')->toArray();
                $files = FresnsFiles::where('fid', $fid)->where('table_name', FresnsPostLogsConfig::CFG_TABLE)->whereIn('table_id', $postLogsIdArr)->first();
                if (empty($files)) {
                    $this->error(ErrorCodeService::FILE_EXIST_ERROR);
                }
                // Post attachments need to determine if the post has permission enabled posts > is_allow
                if (! empty($typeData)) {
                    if ($typeData['is_allow'] == FresnsPostsConfig::IS_ALLOW_1) {
                        // If the post has read access, determine if the user requesting the download itself and the user's primary role are in the authorization list post_allows table
                        $count = DB::table('post_allows')->where('post_id', $typeData['id'])->where('type', 2)->where('object_id', $uid)->count();
                        if (empty($count)) {
                            $this->error(ErrorCodeService::POST_BROWSE_ERROR);
                        }
                    }
                }

                break;
            case 2:
                // It is necessary to verify that the file belongs to the corresponding source target, such as whether the file belongs to the post.
                $typeData = FresnsComments::where('cid', $fsid)->first();
                if (empty($typeData)) {
                    $this->error(ErrorCodeService::FILE_EXIST_ERROR);
                }
                // Query the log table id corresponding to the main table
                $commentLogsIdArr = FresnsCommentLogs::where('post_id', $typeData['id'])->pluck('id')->toArray();
                $files = FresnsFiles::where('fid', $fid)->where('table_name', FresnsCommentLogsConfig::CFG_TABLE)->whereIn('table_id', $commentLogsIdArr)->first();
                if (empty($files)) {
                    $this->error(ErrorCodeService::FILE_EXIST_ERROR);
                }
                break;
            default:
                $typeData = FresnsExtends::where('eid', $fsid)->first();
                // It is necessary to verify that the file belongs to the corresponding source target, such as whether the file belongs to the post.
                if (empty($typeData)) {
                    $this->error(ErrorCodeService::FILE_EXIST_ERROR);
                }

                $files = FresnsFiles::where('fid', $fid)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_id', $typeData['id'])->first();
                if (empty($files)) {
                    $this->error(ErrorCodeService::FILE_EXIST_ERROR);
                }
                break;
        }

        if (empty($typeData)) {
            $this->error(ErrorCodeService::FILE_EXIST_ERROR);
        }

        $roleId = FresnsUserRolesService::getUserRoles($uid);
        $permission = FresnsRoles::where('id', $roleId)->value('permission');
        if (empty($permission)) {
            $this->error(ErrorCodeService::ROLE_NO_CONFIG_ERROR);
        }
        $permissionArr = json_decode($permission, true);
        $permissionMap = FresnsRolesService::getPermissionMap($permissionArr);
        if (empty($permissionMap)) {
            $this->error(ErrorCodeService::ROLE_NO_CONFIG_ERROR);
        }
        $downloadFileCount = $permissionMap['download_file_count'];
        // Calculate whether the maximum number of downloads has been reached in the last 24 hours
        $start = date('Y-m-d H:i:s', strtotime('-1 day'));
        $end = date('Y-m-d H:i:s', time());
        $logCount = FresnsFileLogs::where('account_id', $uid)->where('user_id', $uid)->where('created_at', '>=', $start)->where('created_at', '<=', $end)->count();
        if ($logCount >= $downloadFileCount) {
            $this->error(ErrorCodeService::ROLE_DOWNLOAD_ERROR);
        }

        $files = FresnsFiles::where('fid', $fid)->first();
        $fid = $files['fid'];
        // If the checksum passes, populate the file_logs table with records
        $input = [
            'file_id' => $files['id'],
            'file_type' => $files['file_type'],
            'account_id' => $aid,
            'user_id' => $uid,
            'object_type' => $type,
            'object_id' => $files['table_id'],
        ];
        FresnsFileLogs::insert($input);
        $data = [];
        $filePath = $files['file_path'];
        $downloadUrl = null;
        switch ($files['file_type']) {
            case 1:
                $status = ApiConfigHelper::getConfigByItemKey('image_url_status');
                $domain = ApiConfigHelper::getConfigByItemKey('image_bucket_domain');
                $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_IMAGE;
                $input['fid'] = $fid;
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
                $status = ApiConfigHelper::getConfigByItemKey('video_url_status');
                $domain = ApiConfigHelper::getConfigByItemKey('video_bucket_domain');
                $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_VIDEO;
                $input['fid'] = $fid;
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
                $status = ApiConfigHelper::getConfigByItemKey('audio_url_status');
                $domain = ApiConfigHelper::getConfigByItemKey('audio_bucket_domain');
                $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_AUDIO;
                $input['fid'] = $fid;
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
                $status = ApiConfigHelper::getConfigByItemKey('document_url_status');
                $domain = ApiConfigHelper::getConfigByItemKey('document_bucket_domain');
                $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_DOC;
                $input['fid'] = $fid;
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
        $user_id = GlobalService::getGlobalKey('user_id');
        // Notifications of unread numbers
        $system_count = FresnsNotifies::where('user_id', $user_id)->where('source_type', FsConfig::SOURCE_TYPE_1)->where('status', FsConfig::NO_READ)->count();
        $follow_count = FresnsNotifies::where('user_id', $user_id)->where('source_type', FsConfig::SOURCE_TYPE_2)->where('status', FsConfig::NO_READ)->count();
        $like_count = FresnsNotifies::where('user_id', $user_id)->where('source_type', FsConfig::SOURCE_TYPE_3)->where('status', FsConfig::NO_READ)->count();
        $comment_count = FresnsNotifies::where('user_id', $user_id)->where('source_type', FsConfig::SOURCE_TYPE_4)->where('status', FsConfig::NO_READ)->count();
        $mention_count = FresnsNotifies::where('user_id', $user_id)->where('source_type', FsConfig::SOURCE_TYPE_5)->where('status', FsConfig::NO_READ)->count();
        $recommend_count = FresnsNotifies::where('user_id', $user_id)->where('source_type', FsConfig::SOURCE_TYPE_6)->where('status', FsConfig::NO_READ)->count();
        // Dialogs of unread numbers
        $aStatusNoRead = FresnsDialogs::where('a_user_id', $user_id)->where('a_status', FsConfig::NO_READ)->count();
        $bStatusNoRead = FresnsDialogs::where('b_user_id', $user_id)->where('b_status', FsConfig::NO_READ)->count();
        $dialogNoRead = $aStatusNoRead + $bStatusNoRead;
        // Dialog Messages of unread numbers
        $dialogMessage = FresnsDialogMessages::where('recv_user_id', $user_id)->where('recv_read_at', null)->count();
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
