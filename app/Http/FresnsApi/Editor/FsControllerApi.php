<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Editor;

use App\Helpers\DateHelper;
use App\Helpers\StrHelper;
use App\Http\Center\Common\ErrorCodeService;
use App\Http\Center\Common\GlobalService;
use App\Http\Center\Common\LogService;
use App\Http\Center\Common\ValidateService;
use App\Http\Center\Helper\CmdRpcHelper;
use App\Http\Center\Helper\PluginHelper;
use App\Http\Center\Scene\FileSceneConfig;
use App\Http\Center\Scene\FileSceneService;
use App\Http\FresnsApi\Base\FresnsBaseApiController;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsCmd\FresnsCmdWords;
use App\Http\FresnsCmd\FresnsCmdWordsConfig;
use App\Http\FresnsDb\FresnsCodeMessages\FresnsCodeMessagesConfig;
use App\Http\FresnsDb\FresnsCodeMessages\FresnsCodeMessagesService;
use App\Http\FresnsDb\FresnsCommentLogs\FresnsCommentLogs;
use App\Http\FresnsDb\FresnsCommentLogs\FresnsCommentLogsService;
use App\Http\FresnsDb\FresnsComments\FresnsComments;
use App\Http\FresnsDb\FresnsComments\FresnsCommentsService;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigs;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguagesService;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRelsService;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRolesConfig;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRolesService;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsPlugins\FresnsPluginsService;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsagesConfig;
use App\Http\FresnsDb\FresnsPostLogs\FresnsPostLogs;
use App\Http\FresnsDb\FresnsPostLogs\FresnsPostLogsService;
use App\Http\FresnsDb\FresnsPosts\FresnsPosts;
use App\Http\FresnsDb\FresnsPosts\FresnsPostsService;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogs;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogsService;
use App\Http\FresnsDb\FresnsUsers\FresnsUsersConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FsControllerApi extends FresnsBaseApiController
{
    public function __construct()
    {
        $this->service = new FsService();
        $this->checkRequest();
        $this->initData();
        parent::__construct();
    }

    // Create a new log
    public function create(Request $request)
    {
        $rule = [
            'type' => 'required|in:1,2',
        ];
        ValidateService::validateRule($request, $rule);
        $type = $request->input('type');
        $deviceInfo = $request->header('deviceInfo');
        $platform = $this->platform;
        $user_id = GlobalService::getGlobalKey('user_id');
        $mid = GlobalService::getGlobalKey('member_id');
        if ($deviceInfo) {
            if ($type == 1) {
                $logsId = FresnsSessionLogsService::addSessionLogs($request->getRequestUri(), 'Create draft post', $user_id, $mid, null, 'Create a new draft', 11);
            } else {
                $logsId = FresnsSessionLogsService::addSessionLogs($request->getRequestUri(), 'Create draft comment', $user_id, $mid, null, 'Create a new draft', 12);
            }
        }

        // In case of private mode, this feature is not available when it expires (members > expired_at).
        $checkInfo = FsChecker::checkCreate($mid);
        if (is_array($checkInfo)) {
            FresnsSessionLogs::where('id', $logsId)->update(['object_result' => FsConfig::OBJECT_DEFAIL]);

            return $this->errorCheckInfo($checkInfo);
        }

        // type = 1.post / 2.comment
        $type = $request->input('type');
        // Empty means create a blank log, with a value means edit existing content
        $uuid = $request->input('uuid', '');
        // type=2 / Dedicated，Indicates a comment under that post
        $pid = $request->input('pid', '');
        switch ($type) {
            // type=1
            case '1':
                // uuid=Empty
                // Create blank logs without quantity checking, post logs can have more than one.
                if (empty($uuid)) {
                    // Verify added permissions
                    $createdCheck = FsChecker::checkPermission($type, 1, $user_id, $mid);
                    if (is_array($createdCheck)) {
                        FresnsSessionLogs::where('id', $logsId)->update(['object_result' => FsConfig::OBJECT_DEFAIL]);

                        return $this->errorCheckInfo($createdCheck);
                    }

                    $postInput = [
                        'member_id' => $mid,
                        'platform_id' => $platform,
                    ];
                    $postLogId = DB::table('post_logs')->insertGetId($postInput);
                } else {
                    // uuid=valuable
                    // Check state=1, 2, 4 for the presence of the post ID log.
                    $postInfo = FresnsPosts::where('uuid', $uuid)->first();
                    // Verify editing privileges
                    $createdCheck = FsChecker::checkPermission($type, 2, $user_id, $mid, $postInfo['id']);
                    if (is_array($createdCheck)) {
                        FresnsSessionLogs::where('id', $logsId)->update(['object_result' => FsConfig::OBJECT_DEFAIL]);

                        return $this->errorCheckInfo($createdCheck);
                    }
                    $postLog = FresnsPostLogs::where('post_id', $postInfo['id'])->where('member_id', $mid)->where('state', '!=', 3)->first();
                    if (! $postLog) {
                        $postLogId = ContentLogsService::postLogInsert($uuid, $mid);
                    } else {
                        $postLogId = $postLog['id'];
                    }
                }
                FresnsSessionLogs::where('id', $logsId)->update([
                    'object_result' => FsConfig::OBJECT_SUCCESS,
                    'object_order_id' => $postLogId,
                ]);
                $FresnsPostLogsService = new FresnsPostLogsService();
                $request->offsetSet('id', $postLogId);
                $request->offsetUnset('type');
                $FresnsPostLogsService->setResource(FresnsPostLogsResourceDetail::class);
                $detail = $FresnsPostLogsService->searchData();
                break;
            // type=2
            default:
                // uuid=Empty
                // means create a blank log, the pid must be filled, check if the pid exists for the log comment.
                if (empty($uuid)) {
                    if (empty($pid)) {
                        $this->errorInfo(ErrorCodeService::MEMBER_FAIL, ['info' => 'pid required']);
                    }
                    // Verify added permissions
                    $createdCheck = FsChecker::checkPermission($type, 1, $user_id, $mid);
                    if (is_array($createdCheck)) {
                        return $this->errorCheckInfo($createdCheck);
                    }
                    $postInfo = FresnsPosts::where('uuid', $pid)->first();
                    $commentLog = FresnsCommentLogs::where('member_id', $mid)->where('post_id', $postInfo['id'])->where('state', '!=', 3)->first();
                    // Exists and cannot be recreated. (Only one log comment on the same post returns directly to the current log details).
                    if ($commentLog) {
                        $commentLogId = $commentLog['id'];
                    } else {
                        // Does not exist, create a new log.
                        $commentLogInput = [
                            'member_id' => $mid,
                            'post_id' => $postInfo['id'],
                            'platform_id' => $platform,
                        ];
                        $commentLogId = DB::table('comment_logs')->insertGetId($commentLogInput);
                    }
                } else {
                    // uuid=valuable
                    // Check state=1, 2, 4 for the presence of this comment ID log.
                    $commentInfo = FresnsComments::where('uuid', $uuid)->first();
                    // Verify editing privileges
                    $createdCheck = FsChecker::checkPermission($type, 2, $user_id, $mid, $commentInfo['id']);
                    if (is_array($createdCheck)) {
                        return $this->errorCheckInfo($createdCheck);
                    }
                    $commentLog = FresnsCommentLogs::where('comment_id', $commentInfo['id'])->where('member_id', $mid)->where('state', '!=', 3)->first();
                    if (! $commentLog) {
                        $commentLogId = ContentLogsService::commentLogInsert($uuid, $mid);
                    } else {
                        $commentLogId = $commentLog['id'];
                    }
                }
                FresnsSessionLogs::where('id', $logsId)->update([
                    'object_result' => FsConfig::OBJECT_SUCCESS,
                    'object_order_id' => $commentLogId,
                ]);
                $FresnsCommentLogsService = new FresnsCommentLogsService();
                $request->offsetSet('id', $commentLogId);
                $request->offsetUnset('type');
                $FresnsCommentLogsService->setResource(FresnsCommentLogsResourceDetail::class);
                $detail = $FresnsCommentLogsService->searchData();
                break;
        }
        $data = [
            'detail' => $detail['list'][0] ?? null,
        ];
        $this->success($data);
    }

    // Get log details
    public function detail(Request $request)
    {
        $rule = [
            'type' => 'required|in:1,2',
            'logId' => 'required',
        ];
        ValidateService::validateRule($request, $rule);

        $mid = GlobalService::getGlobalKey('member_id');
        $type = $request->input('type');
        // $logId = $request->input('logId');

        switch ($type) {
            case '1':
                $FresnsPostLogsService = new FresnsPostLogsService();
                $request->offsetUnset('type');
                $request->offsetSet('member_id', $mid);
                $FresnsPostLogsService->setResource(FresnsPostLogsResourceDetail::class);
                $detail = $FresnsPostLogsService->searchData();
                break;
            default:
                $FresnsCommentLogsService = new FresnsCommentLogsService();
                $request->offsetUnset('type');
                $request->offsetSet('member_id', $mid);
                $FresnsCommentLogsService->setResource(FresnsCommentLogsResourceDetail::class);
                $detail = $FresnsCommentLogsService->searchData();
                break;
        }

        $data = [
            'detail' => $detail['list'][0] ?? null,
        ];
        $this->success($data);
    }

    // Get log list
    public function lists(Request $request)
    {
        $rule = [
            'type' => 'required|in:1,2',
            'status' => 'required|in:1,2',
            'class' => 'in:1,2',
        ];
        ValidateService::validateRule($request, $rule);

        $mid = GlobalService::getGlobalKey('member_id');
        $type = $request->input('type');
        $status = $request->input('status');
        $class = $request->input('class');
        if ($type == 1) {
            if (! empty($class)) {
                if ($class == 1) {
                    $idArr = FresnsPostLogs::where('post_id', null)->pluck('id')->toArray();
                } else {
                    $idArr = FresnsPostLogs::where('post_id', '!=', null)->pluck('id')->toArray();
                }
                $request->offsetSet('ids', implode(',', $idArr));
            }
            $page = $request->input('page', 1);
            $pageSize = $request->input('pageSize', 30);
            $FresnsPostLogsService = new FresnsPostLogsService();
            $request->offsetUnset('type');
            $request->offsetSet('member_id', $mid);
            $request->offsetSet('currentPage', $page);
            $request->offsetSet('pageSize', $pageSize);
            $FresnsPostLogsService->setResource(FresnsPostLogsResource::class);
            $list = $FresnsPostLogsService->searchData();
        } else {
            if (! empty($class)) {
                if ($class == 1) {
                    $idArr = FresnsCommentLogs::where('comment_id', null)->pluck('id')->toArray();
                } else {
                    $idArr = FresnsCommentLogs::where('comment_id', '!=', null)->pluck('id')->toArray();
                }
                $request->offsetSet('ids', implode(',', $idArr));
            }
            $page = $request->input('page', 1);
            $pageSize = $request->input('pageSize', 30);
            $FresnsCommentLogsService = new FresnsCommentLogsService();
            $request->offsetUnset('type');
            $request->offsetSet('member_id', $mid);
            $request->offsetSet('currentPage', $page);
            $request->offsetSet('pageSize', $pageSize);
            $FresnsCommentLogsService->setResource(FresnsCommentLogsResource::class);
            $list = $FresnsCommentLogsService->searchData();
        }
        $data = [
            'list' => $list['list'],
            'pagination' => $list['pagination'],
        ];
        $this->success($data);
    }

    // update post and comment log
    public function update(Request $request)
    {
        $rule = [
            'logType' => 'required|in:1,2',
            'logId' => 'required',
            'isMarkdown' => 'in:0,1',
            'isAnonymous' => 'in:0,1',
            'isPluginEdit' => 'in:0,1',
            'fileJson' => 'json',
            'extendsJson' => 'json',
            'locationJson' => 'json',
            'allowJson' => 'json',
            'commentSetJson' => 'json',
            'memberListJson' => 'json',
        ];
        ValidateService::validateRule($request, $rule);

        $mid = GlobalService::getGlobalKey('member_id');
        $logType = $request->input('logType');
        $logId = $request->input('logId');
        $checkInfo = FsChecker::checkDrast($mid);
        if (is_array($checkInfo)) {
            return $this->errorCheckInfo($checkInfo);
        }
        if ($logType == 1) {
            ContentLogsService::updatePostLog($mid);
        } else {
            ContentLogsService::updateCommentLog($mid);
        }
        $this->success();
    }

    // submit log
    public function submit(Request $request)
    {
        $rule = [
            'type' => 'required|in:1,2',
            'logId' => 'required',
        ];
        ValidateService::validateRule($request, $rule);
        $deviceInfo = $request->header('deviceInfo');
        $platform = $this->platform;
        $type = $request->input('type');
        $logsId = 0;
        $mid = GlobalService::getGlobalKey('member_id');
        $user_id = GlobalService::getGlobalKey('user_id');
        if ($deviceInfo) {
            if ($type == 1) {
                $logsId = FresnsSessionLogsService::addSessionLogs("App\Http\FresnsDb\FresnsPosts", 'Publish Post Content', $user_id, $mid, null, 'Officially Published Post Content', 13);
            } else {
                $logsId = FresnsSessionLogsService::addSessionLogs("App\Http\FresnsDb\FresnsComments", 'Publish Comment Content', $user_id, $mid, null, 'Officially Published Comment Content', 14);
            }
        }
        $type = $request->input('type');
        $draftId = $request->input('logId');
        $FresnsPostsService = new FresnsPostsService();
        $fresnsCommentService = new FresnsCommentsService();
        $checkInfo = FsChecker::checkSubmit($mid);
        if (is_array($checkInfo)) {
            FresnsSessionLogs::where('id', $logsId)->update(['object_result' => FsConfig::OBJECT_DEFAIL]);

            return $this->errorCheckInfo($checkInfo);
        }
        switch ($type) {
            case 1:
                // Determine if it is an update or a new addition
                $draftPost = FresnsPostLogs::find($draftId);
                if (! $draftPost['post_id']) {
                    // Verify added permissions
                    $createdCheck = FsChecker::checkPermission(1, 1, $user_id, $mid);
                    if (is_array($createdCheck)) {
                        FresnsSessionLogs::where('id', $logsId)->update(['object_result' => FsConfig::OBJECT_DEFAIL]);

                        return $this->errorCheckInfo($createdCheck);
                    }
                } else {
                    // Verify added permissions
                    $createdCheck = FsChecker::checkPermission(1, 2, $user_id, $mid, $draftPost['post_id']);
                    if (is_array($createdCheck)) {
                        FresnsSessionLogs::where('id', $logsId)->update(['object_result' => FsConfig::OBJECT_DEFAIL]);

                        return $this->errorCheckInfo($createdCheck);
                    }
                }
                // Determine if review is required
                if ($type == 1) {
                    $draft = FresnsPostLogs::find($draftId);
                } else {
                    $draft = FresnsCommentLogs::find($draftId);
                }
                $checkAudit = FsChecker::checkAudit($type, $mid, $draft['content']);
                if ($checkAudit) {
                    // Need to review: modify the log state to be reviewed (state), enter the time to submit the review (submit_at), do not move the other, and then operate after the review is passed.
                    if ($type == 1) {
                        FresnsPostLogs::where('id', $draftId)->update([
                            'state' => 2,
                            'submit_at' => date('Y-m-d H:i:s', time()),
                        ]);
                    } else {
                        FresnsCommentLogs::where('id', $draftId)->update([
                            'state' => 2,
                            'submit_at' => date('Y-m-d H:i:s', time()),
                        ]);
                    }
                    $this->success();
                }
                // Call Release
                // $result = $FresnsPostsService->releaseByDraft($draftId, $logsId);
                $cmd = FresnsCmdWordsConfig::FRESNS_CMD_DIRECT_RELEASE_CONTENT;
                $input = [
                    'type' => $type,
                    'logId' => $draftId,
                    'sessionLogsId' => $logsId,
                ];
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $this->errorCheckInfo($resp);
                }
                break;
            case 2:
                // Determine if it is an update or a new addition
                $draftComment = FresnsCommentLogs::find($draftId);
                if (! $draftComment['comment_id']) {
                    // Verify added permissions
                    $createdCheck = FsChecker::checkPermission(2, 1, $user_id, $mid);
                    if (is_array($createdCheck)) {
                        FresnsSessionLogs::where('id', $logsId)->update(['object_result' => FsConfig::OBJECT_DEFAIL]);

                        return $this->errorCheckInfo($createdCheck);
                    }
                } else {
                    // Verify editing privileges
                    $createdCheck = FsChecker::checkPermission(2, 2, $user_id, $mid, $draftComment['comment_id']);
                    if (is_array($createdCheck)) {
                        FresnsSessionLogs::where('id', $logsId)->update(['object_result' => FsConfig::OBJECT_DEFAIL]);

                        return $this->errorCheckInfo($createdCheck);
                    }
                }
                // Determine if review is required
                if ($type == 1) {
                    $draft = FresnsPostLogs::find($draftId);
                } else {
                    $draft = FresnsCommentLogs::find($draftId);
                }
                $checkAudit = FsChecker::checkAudit($type, $mid, $draft['content']);
                if ($checkAudit) {
                    // Need to review: modify the log state to be reviewed (state), enter the time to submit the review (submit_at), do not move the other, and then operate after the review is passed.
                    if ($type == 1) {
                        FresnsPostLogs::where('id', $draftId)->update([
                            'state' => 2,
                            'submit_at' => date('Y-m-d H:i:s', time()),
                        ]);
                    } else {
                        FresnsCommentLogs::where('id', $draftId)->update([
                            'state' => 2,
                            'submit_at' => date('Y-m-d H:i:s', time()),
                        ]);
                    }
                    $this->success();
                }
                // $result = $fresnsCommentService->releaseByDraft($draftId, 0, $logsId);
                $cmd = FresnsCmdWordsConfig::FRESNS_CMD_DIRECT_RELEASE_CONTENT;
                $input = [
                    'type' => $type,
                    'logId' => $draftId,
                    'commentCid' => 0,
                    'sessionLogsId' => $logsId,
                ];
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $this->errorCheckInfo($resp);
                }
                break;
        }
        $this->success();
    }

    // Fast Publishing
    public function publish(Request $request)
    {
        $rule = [
            'type' => 'required|in:1,2',
            'content' => 'required',
            'isMarkdown' => 'required|in:0,1',
            'content' => 'required',
            'isAnonymous' => 'required | in:0,1',
        ];
        ValidateService::validateRule($request, $rule);
        $deviceInfo = $request->header('deviceInfo');
        $platform = $this->platform;
        $type = $request->input('type');
        $uid = GlobalService::getGlobalKey('user_id');
        $member_id = GlobalService::getGlobalKey('member_id');
        $logsId = 0;
        if ($deviceInfo) {
            if ($type == 1) {
                $logsId = FresnsSessionLogsService::addSessionLogs($request->getRequestUri(), 'Publish Post Content', $uid, $member_id, null, 'Officially Published Post Content', 13);
            } else {
                $logsId = FresnsSessionLogsService::addSessionLogs($request->getRequestUri(), 'Publish Comment Content', $uid, $member_id, null, 'Officially Published Comment Content', 14);
            }
        }
        LogService::Info('logsId', $logsId);
        $commentCid = $request->input('commentCid');
        $file = request()->file('file');

        $fileInfo = $request->input('fileInfo');
        $checkInfo = FsChecker::checkPublish($member_id);
        if (is_array($checkInfo)) {
            FresnsSessionLogs::where('id', $logsId)->update(['object_result' => FsConfig::OBJECT_DEFAIL]);

            return $this->errorCheckInfo($checkInfo);
        }
        if (! empty($file)) {
            $pluginUniKey = ApiConfigHelper::getConfigByItemKey('images_service');
            // Perform Upload
            $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
            if (empty($pluginClass)) {
                LogService::error('Plugin not found');
                FresnsSessionLogs::where('id', $logsId)->update(['object_result' => FsConfig::OBJECT_DEFAIL]);
                $this->error(ErrorCodeService::PLUGINS_CONFIG_ERROR);
            }
            // Is Plugin
            $isPlugin = PluginHelper::pluginCanUse($pluginUniKey);
            if ($isPlugin == false) {
                LogService::error('Plugin not found');
                $this->error(ErrorCodeService::PLUGINS_IS_ENABLE_ERROR);
            }

            $paramsExist = false;
            $configMapInDB = FresnsConfigs::whereIn('item_key', ['images_secret_id', 'images_secret_key', 'images_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
            $paramsExist = ValidateService::validParamExist($configMapInDB, ['images_secret_id', 'images_secret_key', 'images_bucket_domain']);
            if ($paramsExist == false) {
                LogService::error('Plugin not found');
                FresnsSessionLogs::where('id', $logsId)->update(['object_result' => FsConfig::OBJECT_DEFAIL]);
                $this->error(ErrorCodeService::PLUGINS_PARAM_ERROR);
            }
            $uploadFile = $request->file('file');
            $suffix = $uploadFile->getClientOriginalExtension();
            $suffix = mb_strtolower($suffix);
            $images_ext = ApiConfigHelper::getConfigByItemKey('images_ext');
            $imagesExtArr = explode(',', $images_ext);
            if (! in_array($suffix, $imagesExtArr)) {
                $this->error(ErrorCodeService::UPLOAD_FILES_SUFFIX_ERROR);
            }
        }

        // In case of private mode, this feature is not available when it expires (members > expired_at).
        $checker = FsChecker::checkPermission($type, 1, $uid, $member_id);
        if (is_array($checker)) {
            FresnsSessionLogs::where('id', $logsId)->update(['object_result' => FsConfig::OBJECT_DEFAIL]);

            return $this->errorCheckInfo($checker);
        }
        $FresnsPostsService = new FresnsPostsService();
        $fresnsCommentService = new FresnsCommentsService();
        // Determine if review is required
        $checkAudit = FsChecker::checkAudit($type, $member_id, $request->input('content'));

        switch ($type) {
            case 1:
                $draftId = ContentLogsService::publishCreatedPost($request);
                if ($checkAudit) {
                    FresnsPostLogs::where('id', $draftId)->update([
                        'state' => 2,
                        'submit_at' => date('Y-m-d H:i:s', time()),
                    ]);
                    $this->success();
                }
                // Call Release
                // $data = $FresnsPostsService->releaseByDraft($draftId, $logsId);
                $cmd = FresnsCmdWordsConfig::FRESNS_CMD_DIRECT_RELEASE_CONTENT;
                $input = [
                    'type' => $type,
                    'logId' => $draftId,
                    'sessionLogsId' => $logsId,
                ];
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $this->errorCheckInfo($resp);
                }
                break;
            default:
                if ($commentCid) {
                    $commentInfo = FresnsComments::where('uuid', $commentCid)->first();
                    $commentCid = $commentInfo['id'];
                }
                if (empty($commentCid)) {
                    $commentCid = 0;
                }
                $draftId = ContentLogsService::publishCreatedComment($request);
                if ($checkAudit) {
                    FresnsCommentLogs::where('id', $draftId)->update([
                        'state' => 2,
                        'submit_at' => date('Y-m-d H:i:s', time()),
                    ]);
                    $this->success();
                }
                // $fresnsCommentService->releaseByDraft($draftId, $commentCid, $logsId);
                $cmd = FresnsCmdWordsConfig::FRESNS_CMD_DIRECT_RELEASE_CONTENT;
                $input = [
                    'type' => $type,
                    'logId' => $draftId,
                    'commentCid' => $commentCid,
                    'sessionLogsId' => $logsId,
                ];
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                if (CmdRpcHelper::isErrorCmdResp($resp)) {
                    $this->errorCheckInfo($resp);
                }
                break;
        }
        $this->success();
    }

    // Get Upload Token
    public function uploadToken(Request $request)
    {
        $rule = [
            'type' => 'required|in:1,2,3,4',
            'scene' => 'required|numeric|in:1,2,3,4,5,6,7,8,9,10,11',
        ];
        ValidateService::validateRule($request, $rule);

        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_GET_UPLOAD_TOKEN;
        $input['type'] = $request->input('type');
        $input['scene'] = $request->input('scene');
        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            $this->errorCheckInfo($resp);
        }
        $output = $resp['output'];

        $data['storageId'] = $output['storageId'] ?? 1;
        $data['token'] = $output['token'] ?? null;
        $data['expireTime'] = DateHelper::fresnsOutputTimeToTimezone($output['expireTime']) ?? null;

        $this->success($data);
    }

    // Upload File
    public function upload(Request $request)
    {
        $rule = [
            'type' => 'required|in:1,2,3,4',
            'tableType' => 'required',
            'tableName' => 'required',
            'tableField' => 'required',
            'mode' => 'required|in:1,2',
        ];
        ValidateService::validateRule($request, $rule);
        $type = $request->input('type');
        $mode = $request->input('mode');
        $tableId = $request->input('tableId');
        $tableKey = $request->input('tableKey');
        if ($mode == 2) {
            if (empty($tableId) && empty($tableKey)) {
                $input = [
                    'Parameter Error: ' => 'Fill in at least one of tableId or tableKey',
                ];
                $this->error(ErrorCodeService::CODE_PARAM_ERROR, $input);
            }
        }

        $memberId = GlobalService::getGlobalKey('member_id');

        $data = [];
        if ($mode == 1) {
            $type = $request->input('type');
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

            // Perform Upload
            $pluginClass = PluginHelper::findPluginClass($pluginUniKey);
            if (empty($pluginClass)) {
                LogService::error('Plugin not found');
                $this->error(ErrorCodeService::PLUGINS_CONFIG_ERROR);
            }

            $isPlugin = PluginHelper::pluginCanUse($pluginUniKey);
            if ($isPlugin == false) {
                LogService::error('Plugin not found');
                $this->error(ErrorCodeService::PLUGINS_IS_ENABLE_ERROR);
            }

            $file['file_type'] = $request->input('type', 1);
            $paramsExist = false;
            // Image
            if ($file['file_type'] == FileSceneConfig::FILE_TYPE_1) {
                $configMapInDB = FresnsConfigs::whereIn('item_key', ['images_secret_id', 'images_secret_key', 'images_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
                $paramsExist = ValidateService::validParamExist($configMapInDB, ['images_secret_id', 'images_secret_key', 'images_bucket_domain']);
            }
            // Video
            if ($file['file_type'] == FileSceneConfig::FILE_TYPE_2) {
                $configMapInDB = FresnsConfigs::whereIn('item_key', ['videos_secret_id', 'videos_secret_key', 'videos_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
                $paramsExist = ValidateService::validParamExist($configMapInDB, ['videos_secret_id', 'videos_secret_key', 'videos_bucket_domain']);
            }
            // Audio
            if ($file['file_type'] == FileSceneConfig::FILE_TYPE_3) {
                $configMapInDB = FresnsConfigs::whereIn('item_key', ['audios_secret_id', 'audios_secret_key', 'audios_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
                $paramsExist = ValidateService::validParamExist($configMapInDB, ['audios_secret_id', 'audios_secret_key', 'audios_bucket_domain']);
            }
            // Doc
            if ($file['file_type'] == FileSceneConfig::FILE_TYPE_4) {
                $configMapInDB = FresnsConfigs::whereIn('item_key', ['docs_secret_id', 'docs_secret_key', 'docs_bucket_domain'])->pluck('item_value', 'item_key')->toArray();
                $paramsExist = ValidateService::validParamExist($configMapInDB, ['docs_secret_id', 'docs_secret_key', 'docs_bucket_domain']);
            }
            if ($paramsExist == false) {
                LogService::error('Please configure the storage information first');
                $this->error(ErrorCodeService::PLUGINS_PARAM_ERROR);
            }

            // Confirm Catalog
            $options['file_type'] = $request->input('type');
            $options['table_type'] = $request->input('tableType');
            $storePath = FileSceneService::getEditorPath($options);

            if (! $storePath) {
                $this->error(ErrorCodeService::MEMBER_FAIL);
            }

            // Get an instance of UploadFile
            $uploadFile = $request->file('file');

            if (empty($uploadFile)) {
                $this->error(ErrorCodeService::FILE_EXIST_ERROR);
            }

            // Storage
            $fileSize = $uploadFile->getSize();
            $suffix = $uploadFile->getClientOriginalExtension();
            $checker = FsChecker::checkUploadPermission($memberId, $type, $fileSize, $suffix);
            if ($checker !== true) {
                $this->error($checker);
            }

            LogService::info('File Storage Local Success ', $file);
        } else {
            $fileInfo = $request->input('fileInfo');
            $isJson = StrHelper::isJson($fileInfo);
            if ($isJson == false) {
                $this->error(ErrorCodeService::FILE_INFO_JSON_ERROR);
            }
        }

        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_UPLOAD_FILE;
        $input['type'] = $request->input('type');
        $input['tableType'] = $request->input('tableType');
        $input['tableName'] = $request->input('tableName');
        $input['tableField'] = $request->input('tableField');
        $input['tableId'] = $request->input('tableId');
        $input['tableKey'] = $request->input('tableKey');
        $input['mode'] = $request->input('mode');
        $input['file'] = $request->file('file');
        $input['fileInfo'] = $request->input('fileInfo');
        $input['platform'] = $request->header('platform');
        $input['uid'] = $request->header('uid');
        $input['mid'] = $request->header('mid');
        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            $this->errorCheckInfo($resp);
        }

        $data = $resp['output'];

        $this->success($data);
    }

    // Editor Delete
    public function delete(Request $request)
    {
        $rule = [
            'type' => 'required|in:1,2',
            'logId' => 'required',
            'deleteType' => 'required|in:1,2,3',
        ];
        ValidateService::validateRule($request, $rule);

        $uid = GlobalService::getGlobalKey('user_id');
        $mid = GlobalService::getGlobalKey('member_id');
        $type = $request->input('type');
        $logId = $request->input('logId');
        $deleteType = $request->input('deleteType');

        // Check
        switch ($type) {
            case 1:
                $logs = FresnsPostLogs::where('id', $logId)->first();
                break;
            default:
                $logs = FresnsCommentLogs::where('id', $logId)->first();
                break;
        }

        if (empty($logs)) {
            if ($type == 1) {
                $this->error(ErrorCodeService::DELETE_POST_ERROR);
            } else {
                $this->error(ErrorCodeService::DELETE_COMMENT_ERROR);
            }
        }

        if ($logs['member_id'] != $mid) {
            $this->error(ErrorCodeService::CONTENT_AUTHOR_ERROR);
        }

        if ($deleteType == 2 || $deleteType == 3) {
            $rule = [
                'deleteUuid' => 'required',
            ];
            ValidateService::validateRule($request, $rule);
            $deleteUuid = $request->input('deleteUuid');
            if ($deleteType == 2) {
                $filesJson = json_decode($logs['files_json'], true);
                $filesIdArr = [];
                if (! empty($filesJson)) {
                    foreach ($filesJson as $v) {
                        $filesIdArr[] = $v['fid'];
                    }
                }
                if (! in_array($deleteUuid, $filesIdArr)) {
                    $this->error(ErrorCodeService::FILE_EXIST_ERROR);
                }
            }

            if ($deleteType == 3) {
                $extendsJson = json_decode($logs['extends_json'], true);
                $eidArr = [];
                if (! empty($extendsJson)) {
                    foreach ($extendsJson as $v) {
                        $eidArr[] = $v['eid'];
                    }
                }

                if (! in_array($deleteUuid, $eidArr)) {
                    $this->error(ErrorCodeService::EXTEND_EXIST_ERROR);
                }
            }
        }

        if ($logs['state'] == 3) {
            $this->error(ErrorCodeService::DELETE_CONTENT_ERROR);
        }

        $checkDelete = $this->service->deletePostComment($uid, $mid, $logs, $type);

        if ($checkDelete !== true) {
            $this->error($checkDelete);
        }

        $this->success();
    }

    // Withdraw content under review
    public function revoke(Request $request)
    {
        $rule = [
            'type' => 'required|in:1,2',
            'logId' => 'required',
        ];
        ValidateService::validateRule($request, $rule);

        $type = $request->input('type');
        $logId = $request->input('logId');
        // Post
        if ($type == 1) {
            $postLogs = FresnsPostLogs::find($logId);
            if (! $postLogs) {
                $this->error(ErrorCodeService::POST_LOG_EXIST_ERROR);
            }
            if ($postLogs['state'] != 2) {
                $this->error(ErrorCodeService::POST_REMOKE_ERROR);
            }
            FresnsPostLogs::where('id', $logId)->update(['state' => 1, 'submit_at' => null]);
        } else {
            // comment
            $commentLogs = FresnsCommentLogs::find($logId);
            if (! $commentLogs) {
                $this->error(ErrorCodeService::COMMENT_LOG_EXIST_ERROR);
            }
            if ($commentLogs['state'] != 2) {
                $this->error(ErrorCodeService::COMMENT_REMOKE_ERROR);
            }
            FresnsCommentLogs::where('id', $logId)->update(['state' => 1, 'submit_at' => null]);
        }
        $this->success();
    }

    // Editor Configs
    public function configs(Request $request)
    {
        $rule = [
            'type' => 'required|in:1,2',
        ];
        ValidateService::validateRule($request, $rule);
        $type = $request->input('type');
        $uid = $request->header('uid');
        $langTag = ApiLanguageHelper::getLangTagByHeader();
        $plugin = FresnsCodeMessagesConfig::ERROR_CODE_DEFAULT_PLUGIN;
        $memberId = GlobalService::getGlobalKey('member_id');
        // Verify user and member status
        $user = DB::table(FresnsUsersConfig::CFG_TABLE)->where('uuid', $uid)->first();
        // Verify member role permissions
        $roleId = FresnsMemberRoleRelsService::getMemberRoleRels($memberId);
        // Get site model, determine member expiration time
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        $isExpired = false;
        if ($site_mode == 'private') {
            $expiredAt = FresnsMembers::where('id', $memberId)->value('expired_at');
            if ($expiredAt) {
                if ($expiredAt <= date('Y-m-d H:i:s')) {
                    $isExpired = true;
                }
            }
        }
        $memberRoles = FresnsMemberRoles::where('id', $roleId)->first();
        $memberRolesName = FresnsLanguagesService::getLanguageByTableId(FresnsMemberRolesConfig::CFG_TABLE, 'name', $memberRoles['id'], $langTag);
        $memberPermissionJson = $memberRoles['permission'];
        $memberPermissionArr = json_decode($memberPermissionJson, true);
        $permissionMap = FresnsMemberRolesService::getPermissionMap($memberPermissionArr);
        switch ($type) {
            // Post Editor
            case 1:
                // publishPerm
                $publishPerm = [];
                $errorCode = 0;
                if ($isExpired === false) {
                    $status = true;
                    $errorCode = $this->service->publishPostPerm($user, $memberPermissionJson);
                    if ($errorCode > 0) {
                        $status = false;
                    }
                } else {
                    $status = false;
                }
                $publishPerm['status'] = $status;
                $publishPerm['review'] = $permissionMap['post_review'] ?? false;
                $tips = [];
                if ($isExpired == true) {
                    $tips['expired_at'] = FresnsCodeMessagesService::getCodeMessage($plugin, $langTag, ErrorCodeService::MEMBER_EXPIRED_ERROR);
                } else {
                    if ($errorCode > 0) {
                        $message = FresnsCodeMessagesService::getCodeMessage($plugin, $langTag, $errorCode);
                        if (empty($message)) {
                            $message = ErrorCodeService::getMsg($errorCode);
                        }
                        switch ($errorCode) {
                            case '30403':
                                $tips['post_publish'] = $message;
                                break;
                            case '30700':
                                $tips['post_email_verify'] = $message;
                                break;
                            case '30701':
                                $tips['post_phone_verify'] = $message;
                                break;
                            case '30702':
                                $tips['post_prove_verify'] = $message;
                                break;
                            default:
                                // code...
                                break;
                        }
                    }
                }
                $publishPerm['tips'] = ! empty($tips) ? $tips : null;

                // editPerm
                $editPerm = [];
                $editPerm['status'] = ApiConfigHelper::getConfigByItemKey('post_edit');
                $editPerm['timeLimit'] = intval(ApiConfigHelper::getConfigByItemKey('post_edit_timelimit'));
                $editPerm['editSticky'] = ApiConfigHelper::getConfigByItemKey('post_edit_sticky');
                $editPerm['editEssence'] = ApiConfigHelper::getConfigByItemKey('post_edit_essence');

                // roleLimit
                $roleLimit = [];
                $status = $this->service->postRoleLimit($permissionMap);
                $roleLimit['status'] = $status;
                $roleLimit['roleName'] = $memberRolesName;
                $roleLimit['limitType'] = $permissionMap['post_limit_type'];
                $roleLimit['limitTimeStart'] = $permissionMap['post_limit_type'] == 1 ? $permissionMap['post_limit_period_start'] : $permissionMap['post_limit_cycle_start'];
                $roleLimit['limitTimeEnd'] = $permissionMap['post_limit_type'] == 1 ? $permissionMap['post_limit_period_end'] : $permissionMap['post_limit_cycle_end'];
                $roleLimit['limitRule'] = $permissionMap['post_limit_rule'];

                // globalLimit
                $globalLimit = [];
                $status = $this->service->postGlobalLimit($roleId);
                $globalLimit['status'] = $status;
                $postLimitType = ApiConfigHelper::getConfigByItemKey('post_limit_type');
                $globalLimit['limitType'] = $postLimitType;
                $globalLimit['limitTimeStart'] = $postLimitType == 1 ? ApiConfigHelper::getConfigByItemKey('post_limit_period_start') : ApiConfigHelper::getConfigByItemKey('post_limit_cycle_start');
                $globalLimit['limitTimeEnd'] = $postLimitType == 1 ? ApiConfigHelper::getConfigByItemKey('post_limit_period_end') : ApiConfigHelper::getConfigByItemKey('post_limit_cycle_end');
                $globalLimit['limitRule'] = ApiConfigHelper::getConfigByItemKey('post_limit_rule');
                $globalLimit['limitPrompt'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'post_limit_prompt', $langTag);

                // toolbar
                $toolbar = [];

                // toolbar > emoji
                $toolbar['emoji'] = ApiConfigHelper::getConfigByItemKey('post_editor_emoji');

                // toolbar > image
                // status: If the configs table key value is false, output it directly; if it is true, output the member master role permission parameter configuration value.
                $image = [];
                $postEditorImage = ApiConfigHelper::getConfigByItemKey('post_editor_image');
                $image['status'] = $postEditorImage;
                $image['maxSizze'] = '';
                if ($postEditorImage) {
                    if ($permissionMap) {
                        $image['status'] = $permissionMap['post_editor_image'];
                        $image['maxSize'] = $permissionMap['images_max_size'];
                    }
                }
                // Get storage service plugin upload page
                $imageService = ApiConfigHelper::getConfigByItemKey('images_service');
                $image['url'] = FresnsPluginsService::getPluginUrlByUnikey($imageService);
                $image['extensions'] = ApiConfigHelper::getConfigByItemKey('images_ext');
                if (empty($image['maxSize'])) {
                    $image['maxSize'] = ApiConfigHelper::getConfigByItemKey('images_max_size');
                }
                $toolbar['image'] = $image;

                // toolbar > video
                // status: If the configs table key value is false, output it directly; if it is true, output the member master role permission parameter configuration value.
                $video = [];
                $postEditorVideo = ApiConfigHelper::getConfigByItemKey('post_editor_video');
                $video['status'] = $postEditorVideo;
                $video['maxSize'] = '';
                $video['maxTime'] = '';
                if ($postEditorVideo) {
                    if ($permissionMap) {
                        $video['status'] = $permissionMap['post_editor_video'];
                        $video['maxSize'] = $permissionMap['videos_max_size'];
                        $video['maxTime'] = $permissionMap['videos_max_time'];
                    }
                }
                // Get storage service plugin upload page
                $videoService = ApiConfigHelper::getConfigByItemKey('videos_service');
                $video['url'] = FresnsPluginsService::getPluginUrlByUnikey($videoService);
                $video['extensions'] = ApiConfigHelper::getConfigByItemKey('videos_ext');
                if (empty($video['maxSize'])) {
                    $video['maxSize'] = ApiConfigHelper::getConfigByItemKey('videos_max_size');
                }
                if (empty($video['maxTime'])) {
                    $video['maxTime'] = ApiConfigHelper::getConfigByItemKey('videos_max_time');
                }
                $toolbar['video'] = $video;

                // toolbar > audio
                // status: If the configs table key value is false, output it directly; if it is true, output the member master role permission parameter configuration value.
                $audio = [];
                $postEditorAudio = ApiConfigHelper::getConfigByItemKey('post_editor_audio');
                $audio['status'] = $postEditorAudio;
                $audio['maxSize'] = '';
                $audio['maxTime'] = '';
                if ($postEditorAudio) {
                    if ($permissionMap) {
                        $audio['status'] = $permissionMap['post_editor_audio'];
                        $audio['maxSize'] = $permissionMap['audios_max_size'];
                        $audio['maxTime'] = $permissionMap['audios_max_time'];
                    }
                }
                // Get storage service plugin upload page
                $audioService = ApiConfigHelper::getConfigByItemKey('audios_service');
                $audio['url'] = FresnsPluginsService::getPluginUrlByUnikey($audioService);
                $audio['extensions'] = ApiConfigHelper::getConfigByItemKey('audios_ext');
                if (empty($audio['maxSize'])) {
                    $audio['maxSize'] = ApiConfigHelper::getConfigByItemKey('audios_max_size');
                }
                if (empty($audio['maxTime'])) {
                    $audio['maxTime'] = ApiConfigHelper::getConfigByItemKey('audios_max_time');
                }
                $toolbar['audio'] = $audio;

                // toolbar > doc
                // status: If the configs table key value is false, output it directly; if it is true, output the member master role permission parameter configuration value.
                $doc = [];
                $postEditorDoc = ApiConfigHelper::getConfigByItemKey('post_editor_doc');
                $doc['status'] = $postEditorDoc;
                $doc['maxSize'] = '';
                $doc['maxTime'] = '';
                if ($postEditorDoc) {
                    if ($permissionMap) {
                        $doc['status'] = $permissionMap['post_editor_doc'];
                        $doc['maxSize'] = $permissionMap['docs_max_size'];
                        $doc['maxTime'] = $permissionMap['docs_max_time'] ?? false;
                    }
                }
                // Get storage service plugin upload page
                $docService = ApiConfigHelper::getConfigByItemKey('docs_service');
                $doc['url'] = FresnsPluginsService::getPluginUrlByUnikey($docService);
                $doc['extensions'] = ApiConfigHelper::getConfigByItemKey('docs_ext');
                if (empty($doc['maxSize'])) {
                    $doc['maxSize'] = ApiConfigHelper::getConfigByItemKey('docs_max_size');
                }
                $toolbar['doc'] = $doc;

                // toolbar > title
                $title = [];
                $title['status'] = ApiConfigHelper::getConfigByItemKey('post_editor_title');
                $title['view'] = intval(ApiConfigHelper::getConfigByItemKey('post_editor_title_view'));
                $title['required'] = ApiConfigHelper::getConfigByItemKey('post_editor_title_required');
                $title['wordCount'] = intval(ApiConfigHelper::getConfigByItemKey('post_editor_title_word_count'));
                $toolbar['title'] = $title;

                // toolbar > mention
                $toolbar['mention'] = ApiConfigHelper::getConfigByItemKey('post_editor_mention');

                // toolbar > hashtag
                $hashtag = [];
                $hashtag['status'] = ApiConfigHelper::getConfigByItemKey('post_editor_hashtag');
                $hashtag['showMode'] = intval(ApiConfigHelper::getConfigByItemKey('hashtag_show'));
                $toolbar['hashtag'] = $hashtag;

                // toolbar > expand
                $expand = [];
                $expand['status'] = ApiConfigHelper::getConfigByItemKey('post_editor_expand');
                $list = [];
                $FsPluginUsagesArr = FresnsPluginUsages::where('type', 3)->where('scene', 'like', '%1%')->get()->toArray();
                foreach ($FsPluginUsagesArr as $FsUsage) {
                    $arr = [];
                    $arr['plugin'] = $FsUsage['plugin_unikey'];
                    $arr['name'] = ApiLanguageHelper::getLanguagesByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $FsUsage['id']);
                    $arr['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($FsUsage['icon_file_id'], $FsUsage['icon_file_url']);
                    $arr['url'] = FresnsPluginsService::getPluginUsagesUrl($FsUsage['plugin_unikey'], $FsUsage['id']);
                    $arr['number'] = $FsUsage['editor_number'];
                    $list[] = $arr;
                }
                $expand['list'] = $list;
                $toolbar['expand'] = $expand;

                // features
                $features = [];
                // features > group
                $postGroup = [];
                $postGroup['status'] = ApiConfigHelper::getConfigByItemKey('post_editor_group');
                $postGroup['required'] = ApiConfigHelper::getConfigByItemKey('post_editor_group_required');
                $features['postGroup'] = $postGroup;
                // features > lbs
                $isLbs = [];
                $isLbs['status'] = ApiConfigHelper::getConfigByItemKey('post_editor_lbs');
                $maps = [];
                $FsPluginUsagesArr = FresnsPluginUsages::where('type', 9)->get()->toArray();
                foreach ($FsPluginUsagesArr as $FsUsage) {
                    $arr = [];
                    $arr['plugin'] = $FsUsage['plugin_unikey'];
                    $arr['name'] = ApiLanguageHelper::getLanguagesByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $FsUsage['id']);
                    $arr['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($FsUsage['icon_file_id'], $FsUsage['icon_file_url']);
                    $arr['url'] = FresnsPluginsService::getPluginUsagesUrl($FsUsage['plugin_unikey'], $FsUsage['id']);
                    $maps[] = $arr;
                }
                $isLbs['maps'] = $maps;
                $features['isLbs'] = $isLbs;
                // features > anonymous
                $features['isAnonymous'] = ApiConfigHelper::getConfigByItemKey('post_editor_anonymous');
                // features > word count
                $features['contentWordCount'] = intval(ApiConfigHelper::getConfigByItemKey('post_editor_word_count'));

                // Config Data
                $data = [
                    'publishPerm' => $publishPerm,
                    'editPerm' => $editPerm,
                    'roleLimit' => $roleLimit,
                    'globalLimit' => $globalLimit,
                    'toolbar' => $toolbar,
                    'features' => $features,
                ];
                break;

            // Comment Editor
            default:
                // publishPerm
                $publishPerm = [];
                $errorCode = 0;
                if ($isExpired === false) {
                    $status = true;
                    $errorCode = $this->service->publishCommentPerm($user, $memberPermissionJson);
                    if ($errorCode > 0) {
                        $status = false;
                    }
                } else {
                    $status = false;
                }
                $publishPerm['status'] = $status;
                $publishPerm['review'] = $permissionMap['post_review'] ?? false;
                $tips = [];
                if ($isExpired == true) {
                    $tips['expired_at'] = FresnsCodeMessagesService::getCodeMessage($plugin, $langTag, ErrorCodeService::MEMBER_EXPIRED_ERROR);
                } else {
                    if ($errorCode > 0) {
                        $message = FresnsCodeMessagesService::getCodeMessage($plugin, $langTag, $errorCode);
                        if (empty($message)) {
                            $message = ErrorCodeService::getMsg($errorCode);
                        }
                        switch ($errorCode) {
                            case '30403':
                                $tips['comment_publish'] = $message;
                                break;
                            case '30700':
                                $tips['comment_email_verify'] = $message;
                                break;
                            case '30701':
                                $tips['comment_phone_verify'] = $message;
                                break;
                            case '30702':
                                $tips['comment_prove_verify'] = $message;
                                break;
                            default:
                                // code...
                                break;
                        }
                    }
                }
                $publishPerm['tips'] = ! empty($tips) ? $tips : null;

                // editPerm
                $editPerm = [];
                $editPerm['status'] = ApiConfigHelper::getConfigByItemKey('comment_edit');
                $editPerm['timeLimit'] = intval(ApiConfigHelper::getConfigByItemKey('comment_edit_timelimit'));
                $editPerm['editSticky'] = ApiConfigHelper::getConfigByItemKey('comment_edit_sticky');

                // roleLimit
                $roleLimit = [];
                $status = $this->service->commentRoleLimit($permissionMap);
                $roleLimit['status'] = $status;
                $roleLimit['roleName'] = $memberRolesName;
                $roleLimit['limitType'] = $permissionMap['comment_limit_type'];
                $roleLimit['limitTimeStart'] = $permissionMap['comment_limit_type'] == 1 ? $permissionMap['comment_limit_period_start'] : $permissionMap['comment_limit_cycle_start'];
                $roleLimit['limitTimeEnd'] = $permissionMap['comment_limit_type'] == 1 ? $permissionMap['comment_limit_period_end'] : $permissionMap['comment_limit_cycle_end'];
                $roleLimit['limitRule'] = $permissionMap['comment_limit_rule'];

                // globalLimit
                $globalLimit = [];
                $status = $this->service->commentGlobalLimit($roleId);
                $globalLimit['status'] = $status;
                $commentLimitType = ApiConfigHelper::getConfigByItemKey('comment_limit_type');
                $globalLimit['limitType'] = $commentLimitType;
                $globalLimit['limitTimeStart'] = $commentLimitType == 1 ? ApiConfigHelper::getConfigByItemKey('comment_limit_period_start') : ApiConfigHelper::getConfigByItemKey('comment_limit_cycle_start');
                $globalLimit['limitTimeEnd'] = $commentLimitType == 1 ? ApiConfigHelper::getConfigByItemKey('comment_limit_period_end') : ApiConfigHelper::getConfigByItemKey('comment_limit_cycle_end');
                $globalLimit['limitRule'] = ApiConfigHelper::getConfigByItemKey('comment_limit_rule');
                $globalLimit['limitPrompt'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'comment_limit_prompt', $langTag);

                // toolbar
                $toolbar = [];

                // toolbar > emoji
                $toolbar['emoji'] = ApiConfigHelper::getConfigByItemKey('comment_editor_emoji');

                // toolbar > image
                $image = [];
                $commentEditorImage = ApiConfigHelper::getConfigByItemKey('comment_editor_image');
                $image['status'] = $commentEditorImage;
                $image['maxSizze'] = '';
                if ($commentEditorImage) {
                    if ($permissionMap) {
                        $image['status'] = $permissionMap['comment_editor_image'] ?? false;
                        $image['maxSize'] = $permissionMap['images_max_size'];
                    }
                }
                $imageService = ApiConfigHelper::getConfigByItemKey('images_service');
                $image['url'] = FresnsPluginsService::getPluginUrlByUnikey($imageService);
                $image['extensions'] = ApiConfigHelper::getConfigByItemKey('images_ext');
                if (empty($image['maxSize'])) {
                    $image['maxSize'] = ApiConfigHelper::getConfigByItemKey('images_max_size');
                }
                $toolbar['image'] = $image;

                // toolbar > video
                $video = [];
                $commentEditorVideo = ApiConfigHelper::getConfigByItemKey('comment_editor_video');
                $video['status'] = $commentEditorVideo;
                $video['maxSize'] = '';
                $video['maxTime'] = '';
                if ($commentEditorVideo) {
                    if ($permissionMap) {
                        $video['status'] = $permissionMap['comment_editor_video'] ?? false;
                        $video['maxSize'] = $permissionMap['videos_max_size'];
                        $video['maxTime'] = $permissionMap['videos_max_time'];
                    }
                }
                $videoService = ApiConfigHelper::getConfigByItemKey('videos_service');
                $video['url'] = FresnsPluginsService::getPluginUrlByUnikey($videoService);
                $video['extensions'] = ApiConfigHelper::getConfigByItemKey('videos_ext');
                if (empty($video['maxSize'])) {
                    $video['maxSize'] = ApiConfigHelper::getConfigByItemKey('videos_max_size');
                }
                if (empty($video['maxTime'])) {
                    $video['maxTime'] = ApiConfigHelper::getConfigByItemKey('videos_max_time');
                }
                $toolbar['video'] = $video;

                // toolbar > audio
                $audio = [];
                $commentEditorAudio = ApiConfigHelper::getConfigByItemKey('comment_editor_audio');
                $audio['status'] = $commentEditorAudio;
                $audio['maxSize'] = '';
                $audio['maxTime'] = '';
                if ($commentEditorAudio) {
                    if ($permissionMap) {
                        $audio['status'] = $permissionMap['comment_editor_audio'] ?? false;
                        $audio['maxSize'] = $permissionMap['audios_max_size'];
                        $audio['maxTime'] = $permissionMap['audios_max_time'];
                    }
                }
                $audioService = ApiConfigHelper::getConfigByItemKey('audios_service');
                $audio['url'] = FresnsPluginsService::getPluginUrlByUnikey($audioService);
                $audio['extensions'] = ApiConfigHelper::getConfigByItemKey('audios_ext');
                if (empty($audio['maxSize'])) {
                    $audio['maxSize'] = ApiConfigHelper::getConfigByItemKey('audios_max_size');
                }
                if (empty($audio['maxTime'])) {
                    $audio['maxTime'] = ApiConfigHelper::getConfigByItemKey('audios_max_time');
                }
                $toolbar['audio'] = $audio;

                // toolbar > doc
                $doc = [];
                $commentEditorDoc = ApiConfigHelper::getConfigByItemKey('comment_editor_doc');
                $doc['status'] = $commentEditorDoc;
                $doc['maxSize'] = '';
                $doc['maxTime'] = '';
                if ($commentEditorDoc) {
                    if ($permissionMap) {
                        $doc['status'] = $permissionMap['comment_editor_doc'] ?? false;
                        $doc['maxSize'] = $permissionMap['docs_max_size'];
                        $doc['maxTime'] = $permissionMap['docs_max_time'] ?? false;
                    }
                }
                $docService = ApiConfigHelper::getConfigByItemKey('docs_service');
                $doc['url'] = FresnsPluginsService::getPluginUrlByUnikey($docService);
                $doc['extensions'] = ApiConfigHelper::getConfigByItemKey('docs_ext');
                if (empty($doc['maxSize'])) {
                    $doc['maxSize'] = ApiConfigHelper::getConfigByItemKey('docs_max_size');
                }
                $toolbar['doc'] = $doc;

                // toolbar > mention
                $toolbar['mention'] = ApiConfigHelper::getConfigByItemKey('comment_editor_mention');

                // toolbar > hashtag
                $hashtag = [];
                $hashtag['status'] = ApiConfigHelper::getConfigByItemKey('comment_editor_hashtag');
                $hashtag['showMode'] = intval(ApiConfigHelper::getConfigByItemKey('hashtag_show'));
                $toolbar['hashtag'] = $hashtag;

                // toolbar > expand
                $expand = [];
                $expand['status'] = ApiConfigHelper::getConfigByItemKey('comment_editor_expand');
                $list = [];
                $FsPluginUsagesArr = FresnsPluginUsages::where('type', 3)->where('scene', 'like', '%2%')->get()->toArray();
                foreach ($FsPluginUsagesArr as $FsUsage) {
                    $arr = [];
                    $arr['plugin'] = $FsUsage['plugin_unikey'];
                    $arr['name'] = ApiLanguageHelper::getLanguagesByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $FsUsage['id']);
                    $arr['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($FsUsage['icon_file_id'], $FsUsage['icon_file_url']);
                    $arr['url'] = FresnsPluginsService::getPluginUsagesUrl($FsUsage['plugin_unikey'], $FsUsage['id']);
                    $arr['number'] = $FsUsage['editor_number'];
                    $list[] = $arr;
                }
                $expand['list'] = $list;
                $toolbar['expand'] = $expand;

                // features
                $features = [];

                // features > lbs
                $isLbs = [];
                $isLbs['status'] = ApiConfigHelper::getConfigByItemKey('comment_editor_lbs');
                $maps = [];
                $FsPluginUsagesArr = FresnsPluginUsages::where('type', 9)->get()->toArray();
                foreach ($FsPluginUsagesArr as $FsUsage) {
                    $arr = [];
                    $arr['plugin'] = $FsUsage['plugin_unikey'];
                    $arr['name'] = ApiLanguageHelper::getLanguagesByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $FsUsage['id']);
                    $arr['icon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($FsUsage['icon_file_id'], $FsUsage['icon_file_url']);
                    $arr['url'] = FresnsPluginsService::getPluginUsagesUrl($FsUsage['plugin_unikey'], $FsUsage['id']);
                    $maps[] = $arr;
                }
                $isLbs['maps'] = $maps;
                $features['isLbs'] = $isLbs;

                // features > anonymous
                $features['isAnonymous'] = ApiConfigHelper::getConfigByItemKey('comment_editor_anonymous');

                // features > word count
                $features['contentWordCount'] = intval(ApiConfigHelper::getConfigByItemKey('comment_editor_word_count'));

                // Config Data
                $data = [
                    'publishPerm' => $publishPerm,
                    'editPerm' => $editPerm,
                    'roleLimit' => $roleLimit,
                    'globalLimit' => $globalLimit,
                    'toolbar' => $toolbar,
                    'features' => $features,
                ];
                break;
        }
        $this->success($data);
    }
}
