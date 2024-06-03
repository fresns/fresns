<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Fresns\Api\Exceptions\ResponseException;
use App\Fresns\Api\Http\DTO\EditorDraftCreateDTO;
use App\Fresns\Api\Http\DTO\EditorDraftDetailDTO;
use App\Fresns\Api\Http\DTO\EditorDraftListDTO;
use App\Fresns\Api\Http\DTO\EditorDraftUpdateDTO;
use App\Fresns\Api\Http\DTO\EditorQuickPublishDTO;
use App\Fresns\Api\Services\ContentService;
use App\Fresns\Words\Content\DTO\LocationInfoDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Models\App;
use App\Models\ArchiveUsage;
use App\Models\CommentLog;
use App\Models\Extend;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\PostLog;
use App\Models\SessionLog;
use App\Utilities\ConfigUtility;
use App\Utilities\ContentUtility;
use App\Utilities\PermissionUtility;
use App\Utilities\ValidationUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EditorController extends Controller
{
    // editor configs
    public function configs(string $type)
    {
        if (! in_array($type, ['post', 'comment'])) {
            throw new ResponseException(30002);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $config['editor'] = ConfigUtility::getEditorConfigByType($type, $authUser->id, $langTag);
        $config['publish'] = ConfigUtility::getPublishConfigByType($type, $authUser->id, $langTag, $timezone);
        $config['edit'] = ConfigUtility::getEditConfigByType($type);

        return $this->success($config);
    }

    // quick publish
    public function publish(string $type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new EditorQuickPublishDTO($requestData);

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $commentPostId = null;
        if ($dtoRequest->type == 'comment') {
            $commentPost = ContentUtility::getCommentPost($dtoRequest->commentPid, $dtoRequest->commentCid);

            if (empty($commentPost)) {
                return $this->failure(38108, ConfigUtility::getCodeMessage(38108));
            }

            $commentPostId = $commentPost->id;
        }

        // check draft content
        $validDraft = [
            'userId' => $authUser->id,
            'postId' => null,
            'postGroupId' => PrimaryHelper::fresnsPrimaryId('group', $dtoRequest->gid),
            'postTitle' => $dtoRequest->title,
            'commentId' => null,
            'commentPostId' => $commentPostId,
            'content' => $dtoRequest->content,
        ];
        $checkDraftCode = ValidationUtility::draft($dtoRequest->type, $validDraft);

        if ($checkDraftCode && $checkDraftCode != 38200) {
            throw new ResponseException($checkDraftCode);
        }

        // check publish prem
        ContentService::checkPublishPerm($dtoRequest->type, $authUser->id, null, $langTag, $timezone);

        if ($dtoRequest->image) {
            $fileConfig = FileHelper::fresnsFileStorageConfigByType(File::TYPE_IMAGE);

            if (! $fileConfig['storageConfigStatus']) {
                throw new ResponseException(32105);
            }

            $servicePlugin = App::where('fskey', $fileConfig['service'])->isEnabled()->first();

            if (! $servicePlugin) {
                throw new ResponseException(32102);
            }
        }

        $locationInfo = null;
        if ($dtoRequest->locationInfo) {
            $locationInfo = json_decode($dtoRequest->locationInfo, true);
            new LocationInfoDTO($locationInfo);
        }

        $extends = $dtoRequest->extends ? json_decode($dtoRequest->extends, true) : null;
        $archives = $dtoRequest->archives ? json_decode($dtoRequest->archives, true) : null;

        $wordType = match ($dtoRequest->type) {
            'post' => 1,
            'comment' => 2,
        };

        $wordBody = [
            'uid' => $authUser->uid,
            'type' => $wordType,
            'commentPid' => $dtoRequest->commentPid,
            'commentCid' => $dtoRequest->commentCid,
            'quotePid' => $dtoRequest->quotePid,
            'gid' => $dtoRequest->gid,
            'title' => $dtoRequest->title,
            'content' => $dtoRequest->content,
            'isMarkdown' => $dtoRequest->isMarkdown,
            'isAnonymous' => $dtoRequest->isAnonymous,
            'commentPolicy' => $dtoRequest->commentPolicy,
            'commentPrivate' => $dtoRequest->commentPrivate,
            'gtid' => $dtoRequest->gtid,
            'locationInfo' => $locationInfo,
            'extends' => $extends,
            'archives' => $archives,
            'requireReview' => ($checkDraftCode == 38200),
        ];
        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->contentQuickPublish($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getErrorResponse();
        }

        $usageType = match ($fresnsResp->getData('type')) {
            1 => FileUsage::TYPE_POST,
            2 => FileUsage::TYPE_COMMENT,
        };

        $fsid = $fresnsResp->getData('fsid');

        if ($fsid) {
            $tableName = match ($fresnsResp->getData('type')) {
                1 => 'posts',
                2 => 'comments',
            };

            $tableId = $fresnsResp->getData('id');

            $logType = match ($fresnsResp->getData('type')) {
                1 => SessionLog::TYPE_POST_PUBLISH,
                2 => SessionLog::TYPE_COMMENT_PUBLISH,
            };
        } else {
            $tableName = match ($fresnsResp->getData('type')) {
                1 => 'post_logs',
                2 => 'comment_logs',
            };

            $tableId = $fresnsResp->getData('logId');

            $logType = match ($fresnsResp->getData('type')) {
                1 => SessionLog::TYPE_POST_REVIEW,
                2 => SessionLog::TYPE_COMMENT_REVIEW,
            };
        }

        // upload file
        if ($dtoRequest->image) {
            $fileWordBody = [
                'file' => $dtoRequest->image,
                'type' => File::TYPE_IMAGE,
                'warningType' => null,

                'usageType' => $usageType,
                'platformId' => $this->platformId(),
                'tableName' => $tableName,
                'tableColumn' => 'id',
                'tableId' => $tableId,
                'tableKey' => null,
                'moreInfo' => null,
                'aid' => $this->account()->aid,
                'uid' => $authUser->uid,
            ];

            \FresnsCmdWord::plugin('Fresns')->uploadFile($fileWordBody);
        }

        // session log
        $sessionLog = [
            'type' => $logType,
            'fskey' => 'Fresns',
            'appId' => $this->appId(),
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'langTag' => $this->langTag(),
            'aid' => $this->account()->aid,
            'uid' => $authUser->uid,
            'actionName' => \request()->path(),
            'actionDesc' => "Editor quick publish {$dtoRequest->type}",
            'actionState' => SessionLog::STATE_SUCCESS,
            'actionId' => $tableId,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'loginToken' => null,
            'moreInfo' => null,
        ];

        // create session log
        \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

        CacheHelper::forgetFresnsKey("fresns_user_overview_drafts_{$authUser->uid}", 'fresnsUsers');

        $data = [
            'type' => $dtoRequest->type,
            'draftId' => $fresnsResp->getData('logId'),
            'fsid' => $fsid,
        ];

        if (! $fsid) {
            // review notice
            $contentReviewService = ConfigHelper::fresnsConfigByItemKey('content_review_service');
            if ($contentReviewService) {
                $noticeWordBody = [
                    'type' => $wordType,
                    'logId' => $fresnsResp->getData('logId'),
                ];
                \FresnsCmdWord::plugin($contentReviewService)->reviewNotice($noticeWordBody);
            }

            throw new ResponseException(38200, 'Fresns', $data);
        }

        return $this->success($data);
    }

    // edit post or comment
    public function edit(string $type, string $fsid)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        if ($type != 'post' && $type != 'comment') {
            throw new ResponseException(30002);
        }

        $content = PrimaryHelper::fresnsModelByFsid($type, $fsid);

        if (empty($content)) {
            $code = match ($type) {
                'post' => 37400,
                'comment' => 37500,
            };

            throw new ResponseException($code);
        }

        if ($content->user_id != $authUser->id) {
            throw new ResponseException(38101);
        }

        $wordType = match ($type) {
            'post' => 1,
            'comment' => 2,
        };

        $wordBody = [
            'type' => $wordType,
            'fsid' => $fsid,
        ];
        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->generateDraft($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getErrorResponse();
        }

        // session log
        $logType = match ($type) {
            'post' => SessionLog::TYPE_POST_CREATE_DRAFT,
            'comment' => SessionLog::TYPE_COMMENT_CREATE_DRAFT,
        };
        $sessionLog = [
            'type' => $logType,
            'fskey' => 'Fresns',
            'appId' => $this->appId(),
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'langTag' => $langTag,
            'aid' => $this->account()->aid,
            'uid' => $authUser->uid,
            'actionName' => \request()->path(),
            'actionDesc' => 'Editor Generate Draft',
            'actionState' => SessionLog::STATE_SUCCESS,
            'actionId' => $fresnsResp->getData('logId'),
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'loginToken' => null,
            'moreInfo' => null,
        ];

        // create session log
        \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

        switch ($type) {
            case 'post':
                $draftModel = PostLog::with(['quotedPost', 'group', 'geotag'])->where('id', $fresnsResp->getData('logId'))->first();
                break;

            case 'comment':
                $draftModel = CommentLog::with(['parentComment', 'post', 'geotag'])->where('id', $fresnsResp->getData('logId'))->first();
                break;
        }

        $data['detail'] = $draftModel->getDraftInfo($langTag, $timezone);

        $edit['isEditDraft'] = true;
        $edit['editableStatus'] = $fresnsResp->getData('editableStatus');
        $edit['editableTime'] = $fresnsResp->getData('editableTime');
        $edit['deadlineTime'] = $fresnsResp->getData('deadlineTime');
        $data['controls'] = $edit;

        CacheHelper::forgetFresnsKey("fresns_user_overview_drafts_{$authUser->uid}", 'fresnsUsers');

        return $this->success($data);
    }

    // draft create
    public function draftCreate(string $type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new EditorDraftCreateDTO($requestData);

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $userRolePerm = PermissionUtility::getUserMainRole($authUser->id, $langTag)['permissions'];

        switch ($dtoRequest->type) {
            case 'post':
                if (! $userRolePerm['post_publish']) {
                    throw new ResponseException(36104);
                }

                $checkLogCount = PostLog::where('user_id', $authUser->id)->whereNot('state', PostLog::STATE_SUCCESS)->count();

                if ($checkLogCount >= $userRolePerm['post_draft_count']) {
                    throw new ResponseException(38107);
                }
                break;

            case 'comment':
                if (! $userRolePerm['comment_publish']) {
                    throw new ResponseException(36104);
                }

                $commentPost = ContentUtility::getCommentPost($dtoRequest->commentPid, $dtoRequest->commentCid);
                if (empty($commentPost)) {
                    return $this->failure(38108, ConfigUtility::getCodeMessage(38108));
                }

                $checkCommentPerm = PermissionUtility::checkPostCommentPerm($dtoRequest->commentPid, $authUser->id);
                if (! $checkCommentPerm['status']) {
                    throw new ResponseException($checkCommentPerm['code']);
                }

                $checkLogCount = CommentLog::where('user_id', $authUser->id)->whereNot('state', CommentLog::STATE_SUCCESS)->count();

                if ($checkLogCount >= $userRolePerm['comment_draft_count']) {
                    throw new ResponseException(38107);
                }
                break;
        }

        $wordType = match ($dtoRequest->type) {
            'post' => 1,
            'comment' => 2,
        };

        $wordBody = [
            'uid' => $authUser->uid,
            'type' => $wordType,
            'createType' => 1,
            'editorFskey' => null,
            'commentPid' => $dtoRequest->commentPid,
            'commentCid' => $dtoRequest->commentCid,
            'quotePid' => $dtoRequest->quotePid,
            'gid' => $dtoRequest->gid,
            'title' => $dtoRequest->title,
            'content' => $dtoRequest->content,
            'isMarkdown' => $dtoRequest->isMarkdown,
            'isAnonymous' => $dtoRequest->isAnonymous,
            'commentPolicy' => $dtoRequest->commentPolicy,
            'commentPrivate' => $dtoRequest->commentPrivate,
            'gtid' => $dtoRequest->gtid,
            'locationInfo' => $dtoRequest->locationInfo,
            'extends' => $dtoRequest->extends,
            'archives' => $dtoRequest->archives,
        ];
        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->createDraft($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getErrorResponse();
        }

        // session log
        $logType = match ($type) {
            'post' => SessionLog::TYPE_POST_CREATE_DRAFT,
            'comment' => SessionLog::TYPE_COMMENT_CREATE_DRAFT,
        };
        $sessionLog = [
            'type' => $logType,
            'fskey' => 'Fresns',
            'appId' => $this->appId(),
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'langTag' => $langTag,
            'aid' => $this->account()->aid,
            'uid' => $authUser->uid,
            'actionName' => \request()->path(),
            'actionDesc' => 'Editor Create Draft',
            'actionState' => SessionLog::STATE_SUCCESS,
            'actionId' => $fresnsResp->getData('logId'),
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'loginToken' => null,
            'moreInfo' => null,
        ];

        // create session log
        \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

        switch ($dtoRequest->type) {
            case 'post':
                $draftModel = PostLog::with(['quotedPost', 'group', 'geotag'])->where('id', $fresnsResp->getData('logId'))->first();
                break;

            case 'comment':
                $draftModel = CommentLog::with(['parentComment', 'post', 'geotag'])->where('id', $fresnsResp->getData('logId'))->first();
                break;
        }

        $data['detail'] = $draftModel->getDraftInfo($langTag, $timezone);

        $edit['isEditDraft'] = false;
        $edit['editableStatus'] = true;
        $edit['editableTime'] = null;
        $edit['deadlineTime'] = null;
        $data['controls'] = $edit;

        CacheHelper::forgetFresnsKey("fresns_user_overview_drafts_{$authUser->uid}", 'fresnsUsers');

        return $this->success($data);
    }

    // draft list
    public function draftList(string $type, Request $request)
    {
        if (! in_array($type, ['post', 'comment'])) {
            throw new ResponseException(30002);
        }

        $dtoRequest = new EditorDraftListDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        switch ($type) {
            case 'post':
                $draftQuery = PostLog::with(['quotedPost', 'group', 'geotag'])->where('user_id', $authUser->id);
                break;

            case 'comment':
                $draftQuery = CommentLog::with(['parentComment', 'post', 'geotag'])->where('user_id', $authUser->id);
                break;
        }

        if ($dtoRequest->status) {
            if ($dtoRequest->status == 1) {
                $draftQuery->whereIn('state', [PostLog::STATE_DRAFT, PostLog::STATE_FAILURE]);
            } else {
                $draftQuery->where('state', PostLog::STATE_UNDER_REVIEW);
            }
        } else {
            $draftQuery->whereNot('state', PostLog::STATE_SUCCESS);
        }

        $drafts = $draftQuery->latest()->paginate($dtoRequest->pageSize ?? 15);

        $groupOptions = [
            'viewType' => 'quoted',
            'filter' => [
                'type' => $dtoRequest->filterGroupType,
                'keys' => $dtoRequest->filterGroupKeys,
            ],
        ];
        $geotagOptions = [
            'viewType' => 'quoted',
            'filter' => [
                'type' => $dtoRequest->filterGeotagType,
                'keys' => $dtoRequest->filterGeotagKeys,
            ],
        ];

        $draftList = [];
        foreach ($drafts as $draft) {
            $draftList[] = $draft->getDraftInfo($langTag, $timezone, $groupOptions, $geotagOptions);
        }

        return $this->fresnsPaginate($draftList, $drafts->total(), $drafts->perPage());
    }

    // draft detail
    public function draftDetail(string $type, string $did, Request $request)
    {
        if (! in_array($type, ['post', 'comment'])) {
            throw new ResponseException(30002);
        }

        $dtoRequest = new EditorDraftDetailDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $draft = match ($type) {
            'post' => PostLog::with(['quotedPost', 'group', 'geotag'])->where('hpid', $did)->where('user_id', $authUser->id)->first(),
            'comment' => CommentLog::with(['parentComment', 'post', 'geotag'])->where('hcid', $did)->where('user_id', $authUser->id)->first(),
        };

        if (empty($draft)) {
            throw new ResponseException(38100);
        }

        $parentModel = match ($type) {
            'post' => PrimaryHelper::fresnsModelById('post', $draft->post_id),
            'comment' => PrimaryHelper::fresnsModelById('comment', $draft->comment_id),
        };

        $isEditDraft = false;
        $editableStatus = true;
        $editableTime = null;
        $deadlineTime = null;

        if ($parentModel) {
            $isEditDraft = true;

            $editTimeConfig = ConfigHelper::fresnsConfigByItemKey("{$type}_edit_time_limit");

            $checkContentEditPerm = PermissionUtility::checkContentEditPerm($parentModel->created_at, $editTimeConfig, $timezone, $langTag);

            $editableStatus = $checkContentEditPerm['editableStatus'];
            $editableTime = $checkContentEditPerm['editableTime'];
            $deadlineTime = $checkContentEditPerm['deadlineTime'];
        }

        $controls = [
            'isEditDraft' => $isEditDraft,
            'editableStatus' => $editableStatus,
            'editableTime' => $editableTime,
            'deadlineTime' => $deadlineTime,
        ];

        $groupOptions = [
            'viewType' => 'quoted',
            'filter' => [
                'type' => $dtoRequest->filterGroupType,
                'keys' => $dtoRequest->filterGroupKeys,
            ],
        ];
        $geotagOptions = [
            'viewType' => 'quoted',
            'filter' => [
                'type' => $dtoRequest->filterGeotagType,
                'keys' => $dtoRequest->filterGeotagKeys,
            ],
        ];

        $data['detail'] = $draft->getDraftInfo($langTag, $timezone, $groupOptions, $geotagOptions);
        $data['controls'] = $controls;

        return $this->success($data);
    }

    // draft update
    public function draftUpdate(string $type, string $did, Request $request)
    {
        if (! in_array($type, ['post', 'comment'])) {
            throw new ResponseException(30002);
        }

        $dtoRequest = new EditorDraftUpdateDTO($request->all());

        $authUser = $this->user();

        $draft = match ($type) {
            'post' => PostLog::where('hpid', $did)->where('user_id', $authUser->id)->first(),
            'comment' => CommentLog::where('hcid', $did)->where('user_id', $authUser->id)->first(),
        };

        if (empty($draft)) {
            throw new ResponseException(38100);
        }

        if ($draft->state == PostLog::STATE_UNDER_REVIEW) {
            throw new ResponseException(38102);
        }

        if ($draft->state == PostLog::STATE_SUCCESS) {
            throw new ResponseException(38103);
        }

        $permissions = $draft->permissions;

        switch ($type) {
            case 'post':
                // quotePid
                if ($request->has('quotePid')) {
                    $draft->update([
                        'quoted_post_id' => PrimaryHelper::fresnsPrimaryId('post', $dtoRequest->quotePid),
                    ]);
                }

                // gid
                if ($request->has('gid')) {
                    if ($dtoRequest->gid) {
                        $group = PrimaryHelper::fresnsModelByFsid('group', $dtoRequest->gid);

                        if (! $group) {
                            throw new ResponseException(37100);
                        }

                        if (! $group->is_enabled) {
                            throw new ResponseException(37101);
                        }

                        $checkPerm = PermissionUtility::checkUserGroupPublishPerm($group->id, $group->permissions, $authUser->id);

                        if (! $checkPerm['allowPost']) {
                            throw new ResponseException(36311);
                        }

                        $draft->update([
                            'group_id' => $group->id,
                        ]);
                    } else {
                        $draft->update([
                            'group_id' => null,
                        ]);
                    }
                }

                // title
                if ($request->has('title')) {
                    if ($dtoRequest->title) {
                        $title = Str::of($dtoRequest->title)->trim();

                        $draft->update([
                            'title' => $title,
                        ]);
                    } else {
                        $draft->update([
                            'title' => null,
                        ]);
                    }
                }

                // commentPolicy
                if ($dtoRequest->commentPolicy) {
                    $permissions['commentConfig']['policy'] = $dtoRequest->commentPolicy;

                    $draft->update([
                        'permissions' => $permissions,
                    ]);
                }

                // commentPrivate
                if (isset($dtoRequest->commentPrivate)) {
                    $permissions['commentConfig']['privacy'] = $dtoRequest->commentPrivate ? 'private' : 'public';

                    $draft->update([
                        'permissions' => $permissions,
                    ]);
                }
                break;

            case 'comment':
                // commentPrivate
                if (isset($dtoRequest->commentPrivate)) {
                    $draft->update([
                        'is_private' => $dtoRequest->commentPrivate,
                    ]);
                }
                break;
        }

        // content
        if ($request->has('content')) {
            if ($dtoRequest->content) {
                $content = Str::of($dtoRequest->content)->trim();

                $draft->update([
                    'content' => $content,
                ]);
            } else {
                $draft->update([
                    'content' => null,
                ]);
            }
        }

        // isMarkdown
        if (isset($dtoRequest->isMarkdown)) {
            $draft->update([
                'is_markdown' => $dtoRequest->isMarkdown ? 1 : 0,
            ]);
        }

        // isAnonymous
        if (isset($dtoRequest->isAnonymous)) {
            $draft->update([
                'is_anonymous' => $dtoRequest->isAnonymous ? 1 : 0,
            ]);
        }

        // gtid
        if ($request->has('gtid')) {
            if ($dtoRequest->gtid) {
                $geotag = PrimaryHelper::fresnsModelByFsid('geotag', $dtoRequest->gtid);

                if (! $geotag) {
                    throw new ResponseException(37300);
                }

                if (! $geotag->is_enabled) {
                    throw new ResponseException(37301);
                }

                $draft->update([
                    'geotag_id' => $geotag->id,
                ]);
            } else {
                $draft->update([
                    'geotag_id' => null,
                ]);
            }
        }

        // locationInfo
        if ($dtoRequest->locationInfo) {
            new LocationInfoDTO($dtoRequest->locationInfo);

            $draft->update([
                'location_info' => $dtoRequest->locationInfo,
            ]);
        }

        // archives
        if ($dtoRequest->archives) {
            $usageType = match ($type) {
                'post' => ArchiveUsage::TYPE_POST_LOG,
                'comment' => ArchiveUsage::TYPE_COMMENT_LOG,
            };

            ContentUtility::saveArchiveUsages($usageType, $draft->id, $dtoRequest->archives);
        }

        // extends
        if ($dtoRequest->extends) {
            $usageType = match ($type) {
                'post' => ExtendUsage::TYPE_POST_LOG,
                'comment' => ExtendUsage::TYPE_COMMENT_LOG,
            };

            ContentUtility::saveExtendUsages($usageType, $draft->id, $dtoRequest->extends);
        }

        // file info
        if ($dtoRequest->fileInfo) {
            $fileIdArr = [];
            $sortOrderArr = [];
            $moreInfoArr = [];

            foreach ($dtoRequest->fileInfo as $info) {
                $fid = $info['fid'] ?? null;

                $id = PrimaryHelper::fresnsPrimaryId('file', $fid);
                if (empty($id)) {
                    continue;
                }

                $fileIdArr[] = $id;

                if (array_key_exists('sortOrder', $info) && is_numeric($info['sortOrder'])) {
                    $sortOrderArr[$id] = $info['sortOrder'];
                }

                if (array_key_exists('moreInfo', $info)) {
                    $moreInfoArr[$id] = $info['moreInfo'];
                }
            }

            $usageType = match ($type) {
                'post' => FileUsage::TYPE_POST,
                'comment' => FileUsage::TYPE_COMMENT,
            };

            $tableName = match ($type) {
                'post' => 'post_logs',
                'comment' => 'comment_logs',
            };

            $fileIdArr = array_unique($fileIdArr);

            $fileUsages = FileUsage::whereIn('file_id', $fileIdArr)
                ->where('usage_type', $usageType)
                ->where('table_name', $tableName)
                ->where('table_column', 'id')
                ->where('table_id', $draft->id)
                ->get();

            foreach ($fileUsages as $fileUsage) {
                $fileId = $fileUsage->file_id;

                if (array_key_exists($fileId, $sortOrderArr)) {
                    $fileUsage->update([
                        'sort_order' => $sortOrderArr[$fileId],
                    ]);
                }

                if (array_key_exists($fileId, $moreInfoArr)) {
                    $fileUsage->update([
                        'more_info' => $moreInfoArr[$fileId],
                    ]);
                }
            }
        }

        // deleteLocation
        if ($dtoRequest->deleteLocation) {
            $draft->update([
                'location_info' => null,
            ]);
        }

        // deleteExtend
        if ($dtoRequest->deleteExtend) {
            $extend = Extend::where('eid', $dtoRequest->deleteExtend)->first();

            if (empty($extend)) {
                throw new ResponseException(36400);
            }

            $usageType = match ($type) {
                'post' => ExtendUsage::TYPE_POST_LOG,
                'comment' => ExtendUsage::TYPE_COMMENT_LOG,
            };

            $extendUsage = ExtendUsage::where('usage_type', $usageType)
                ->where('usage_id', $draft->id)
                ->where('extend_id', $extend->id)
                ->first();

            if (empty($extendUsage)) {
                throw new ResponseException(36400);
            }

            if (! $extendUsage->can_delete) {
                throw new ResponseException(36401);
            }

            $extendUsage->delete();
        }

        // deleteFile
        if ($dtoRequest->deleteFile) {
            $file = File::where('fid', $dtoRequest->deleteFile)->first();

            if (empty($file)) {
                throw new ResponseException(36400);
            }

            $tableName = match ($type) {
                'post' => 'post_logs',
                'comment' => 'comment_logs',
            };

            FileUsage::where('file_id', $file->id)
                ->where('table_name', $tableName)
                ->where('table_column', 'id')
                ->where('table_id', $draft->id)
                ->delete();
        }

        return $this->success();
    }

    // draft publish
    public function draftPublish(string $type, string $did)
    {
        if (! in_array($type, ['post', 'comment'])) {
            throw new ResponseException(30002);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $draft = match ($type) {
            'post' => PostLog::where('hpid', $did)->where('user_id', $authUser->id)->first(),
            'comment' => CommentLog::where('hcid', $did)->where('user_id', $authUser->id)->first(),
        };

        if (empty($draft)) {
            throw new ResponseException(38100);
        }

        if ($draft->state == PostLog::STATE_UNDER_REVIEW) {
            throw new ResponseException(38104);
        }

        if ($draft->state == PostLog::STATE_SUCCESS) {
            throw new ResponseException(38105);
        }

        $mainId = match ($type) {
            'post' => $draft->post_id,
            'comment' => $draft->comment_id,
        };

        // check publish prem
        ContentService::checkPublishPerm($type, $authUser->id, $mainId, $langTag, $timezone);

        // session log
        $sessionLogType = match ($type) {
            'post' => SessionLog::TYPE_POST_REVIEW,
            'comment' => SessionLog::TYPE_COMMENT_REVIEW,
        };
        $sessionLog = [
            'type' => $sessionLogType,
            'fskey' => 'Fresns',
            'appId' => $this->appId(),
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'langTag' => $this->langTag(),
            'aid' => $this->account()->aid,
            'uid' => $authUser->uid,
            'actionName' => \request()->path(),
            'actionDesc' => 'Editor Publish',
            'actionState' => SessionLog::STATE_UNKNOWN,
            'actionId' => $draft->id,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'loginToken' => null,
            'moreInfo' => null,
        ];

        // cmd word
        $wordType = match ($type) {
            'post' => 1,
            'comment' => 2,
        };
        $wordBody = [
            'type' => $wordType,
            'logId' => $draft->id,
        ];

        // check draft content
        $validDraft = [
            'userId' => $authUser->id,
            'postId' => $draft->post_id,
            'postGroupId' => $draft?->group_id,
            'postTitle' => $draft?->title,
            'commentId' => $draft?->comment_id,
            'commentPostId' => $draft->post_id,
            'content' => $draft->content,
        ];
        $checkDraftCode = ValidationUtility::draft($type, $validDraft);

        if ($checkDraftCode == 38200) {
            // create session log
            \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

            // change state
            $draft->update([
                'state' => PostLog::STATE_UNDER_REVIEW,
                'submit_at' => now(),
            ]);

            // review notice
            $contentReviewService = ConfigHelper::fresnsConfigByItemKey('content_review_service');
            if ($contentReviewService) {
                \FresnsCmdWord::plugin($contentReviewService)->reviewNotice($wordBody);
            }

            // Review
            throw new ResponseException(38200);
        }

        if ($checkDraftCode) {
            throw new ResponseException($checkDraftCode);
        }

        $draft->update([
            'submit_at' => now(),
        ]);

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->contentPublishByDraft($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getErrorResponse();
        }

        // create session log
        $sessionLogType = match ($type) {
            'post' => SessionLog::TYPE_POST_PUBLISH,
            'comment' => SessionLog::TYPE_COMMENT_PUBLISH,
        };
        $sessionLog['type'] = $sessionLogType;
        $sessionLog['actionState'] = SessionLog::STATE_SUCCESS;
        $sessionLog['actionId'] = $fresnsResp->getData('id');

        \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

        CacheHelper::forgetFresnsKey("fresns_user_overview_drafts_{$authUser->uid}", 'fresnsUsers');

        $data = [
            'fsid' => $fresnsResp->getData('fsid'),
        ];

        return $this->success($data);
    }

    // draft recall (draft under review)
    public function draftRecall(string $type, string $did)
    {
        if (! in_array($type, ['post', 'comment'])) {
            throw new ResponseException(30002);
        }

        $authUser = $this->user();

        $draft = match ($type) {
            'post' => PostLog::where('hpid', $did)->where('user_id', $authUser->id)->first(),
            'comment' => CommentLog::where('hcid', $did)->where('user_id', $authUser->id)->first(),
        };

        if (empty($draft)) {
            throw new ResponseException(38100);
        }

        if ($draft->state != PostLog::STATE_UNDER_REVIEW) {
            throw new ResponseException(36501);
        }

        $draft->update([
            'state' => PostLog::STATE_DRAFT,
        ]);

        return $this->success();
    }

    // draft delete
    public function draftDelete(string $type, string $did)
    {
        if (! in_array($type, ['post', 'comment'])) {
            throw new ResponseException(30002);
        }

        $authUser = $this->user();

        $draft = match ($type) {
            'post' => PostLog::where('hpid', $did)->where('user_id', $authUser->id)->first(),
            'comment' => CommentLog::where('hcid', $did)->where('user_id', $authUser->id)->first(),
        };

        if (empty($draft)) {
            throw new ResponseException(38100);
        }

        if ($draft->state == PostLog::STATE_UNDER_REVIEW) {
            throw new ResponseException(36404);
        }

        if ($draft->state == PostLog::STATE_SUCCESS) {
            throw new ResponseException(36405);
        }

        // session log
        $sessionLogType = match ($type) {
            'post' => SessionLog::TYPE_POST_LOG_DELETE,
            'comment' => SessionLog::TYPE_COMMENT_LOG_DELETE,
        };

        $sessionLog = [
            'type' => $sessionLogType,
            'fskey' => 'Fresns',
            'appId' => $this->appId(),
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'langTag' => $this->langTag(),
            'aid' => $this->account()->aid,
            'uid' => $authUser->uid,
            'actionName' => \request()->path(),
            'actionDesc' => 'Draft Delete',
            'actionState' => SessionLog::STATE_SUCCESS,
            'actionId' => $draft->id,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'loginToken' => null,
            'moreInfo' => null,
        ];
        // create session log
        \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

        $draft->delete();

        CacheHelper::forgetFresnsKey("fresns_user_overview_drafts_{$authUser->uid}", 'fresnsUsers');
        CacheHelper::forgetFresnsMultilingual("fresns_publish_{$type}_config_{$authUser->id}", 'fresnsUsers');

        return $this->success();
    }
}
