<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\EditorCreateDTO;
use App\Fresns\Api\Http\DTO\EditorDraftsDTO;
use App\Fresns\Api\Http\DTO\EditorQuickPublishDTO;
use App\Fresns\Api\Http\DTO\EditorUpdateDTO;
use App\Fresns\Api\Services\CommentService;
use App\Fresns\Api\Services\PostService;
use App\Fresns\Api\Services\UserService;
use App\Fresns\Words\Content\DTO\MapDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Archive;
use App\Models\ArchiveUsage;
use App\Models\CommentLog;
use App\Models\Extend;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Plugin;
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
    // config
    public function config($type)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        switch ($type) {
            case 'post':
                $config['editor'] = ConfigUtility::getEditorConfigByType($authUser->id, 'post', $langTag);
                $config['publish'] = ConfigUtility::getPublishConfigByType($authUser->id, 'post', $langTag, $timezone);
                $config['edit'] = ConfigUtility::getEditConfigByType('post');
                break;

            case 'comment':
                $config['editor'] = ConfigUtility::getEditorConfigByType($authUser->id, 'comment', $langTag);
                $config['publish'] = ConfigUtility::getPublishConfigByType($authUser->id, 'comment', $langTag, $timezone);
                $config['edit'] = ConfigUtility::getEditConfigByType('comment');
                break;

            default:
                throw new ApiException(30002);
                break;
        }

        return $this->success($config);
    }

    // drafts
    public function drafts($type, Request $request)
    {
        $dtoRequest = new EditorDraftsDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $status = [1, 2, 4];
        if ($dtoRequest->status == 1) {
            $status = [1, 4];
        }
        if ($dtoRequest->status == 2) {
            $status = [2];
        }

        $draftList = [];
        switch ($type) {
            case 'post':
                $drafts = PostLog::with(['parentPost', 'group', 'creator'])
                    ->where('user_id', $authUser->id)
                    ->whereIn('state', $status)
                    ->latest()
                    ->paginate($dtoRequest->pageSize ?? 15);

                $service = new PostService();
                foreach ($drafts as $draft) {
                    $draftList[] = $service->postLogData($draft, 'list', $langTag, $timezone, $authUser->id);
                }
                break;

            case 'comment':
                $drafts = CommentLog::with(['parentComment', 'post', 'creator'])
                    ->where('user_id', $authUser->id)
                    ->whereIn('state', $status)
                    ->latest()
                    ->paginate($dtoRequest->pageSize ?? 15);

                $service = new CommentService();
                foreach ($drafts as $draft) {
                    $draftList[] = $service->commentLogData($draft, 'list', $langTag, $timezone, $authUser->id);
                }
                break;

            default:
                throw new ApiException(30002);
                break;
        }

        return $this->fresnsPaginate($draftList, $drafts->total(), $drafts->perPage());
    }

    // create
    public function create($type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new EditorCreateDTO($requestData);

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $userRolePerm = PermissionUtility::getUserMainRole($authUser->id, $langTag)['permissions'];

        switch ($dtoRequest->type) {
            case 'post':
                if (! $userRolePerm['post_publish']) {
                    throw new ApiException(36104);
                }

                $checkLogCount = PostLog::where('user_id', $authUser->id)->whereIn('state', [1, 2, 4])->count();

                if ($checkLogCount >= $userRolePerm['post_draft_count']) {
                    throw new ApiException(38106);
                }
                break;

            case 'comment':
                if (! $userRolePerm['comment_publish']) {
                    throw new ApiException(36104);
                }

                $checkCommentPerm = PermissionUtility::checkPostCommentPerm($dtoRequest->commentPid, $authUser->id);
                if (! $checkCommentPerm['status']) {
                    throw new ApiException($checkCommentPerm['code']);
                }

                $checkLogCount = CommentLog::where('user_id', $authUser->id)->whereIn('state', [1, 2, 4])->count();

                if ($checkLogCount >= $userRolePerm['comment_draft_count']) {
                    throw new ApiException(38106);
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
            'createType' => $dtoRequest->createType,
            'editorUnikey' => $dtoRequest->editorUnikey,
            'postGid' => $dtoRequest->postGid,
            'postTitle' => $dtoRequest->postTitle,
            'postIsComment' => $dtoRequest->postIsComment,
            'postIsCommentPublic' => $dtoRequest->postIsCommentPublic,
            'postQuotePid' => $dtoRequest->postQuotePid,
            'commentPid' => $dtoRequest->commentPid,
            'commentCid' => $dtoRequest->commentCid,
            'content' => $dtoRequest->content,
            'isMarkdown' => $dtoRequest->isMarkdown,
            'isAnonymous' => $dtoRequest->isAnonymous,
            'map' => $dtoRequest->map,
            'extends' => $dtoRequest->extends,
            'archives' => $dtoRequest->archives,
        ];
        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->createDraft($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->errorResponse();
        }

        // session log
        $logType = match ($type) {
            'post' => SessionLog::TYPE_POST_CREATE_DRAFT,
            'comment' => SessionLog::TYPE_COMMENT_CREATE_DRAFT,
        };
        $sessionLog = [
            'type' => $logType,
            'pluginUnikey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'langTag' => $langTag,
            'aid' => $this->account()->aid,
            'uid' => $authUser->uid,
            'objectName' => \request()->path(),
            'objectAction' => 'Editor Create Draft',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => $fresnsResp->getData('logId'),
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'moreJson' => null,
        ];

        // upload session log
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        switch ($dtoRequest->type) {
            case 'post':
                $service = new PostService();

                $postLog = PostLog::with(['parentPost', 'group', 'creator'])->where('id', $fresnsResp->getData('logId'))->first();
                $data['detail'] = $service->postLogData($postLog, 'detail', $langTag, $timezone, $authUser->id);
                break;

            case 'comment':
                $service = new CommentService();

                $commentLog = CommentLog::with(['parentComment', 'post', 'creator'])->where('id', $fresnsResp->getData('logId'))->first();
                $data['detail'] = $service->commentLogData($commentLog, 'detail', $langTag, $timezone, $authUser->id);
                break;
        }

        $edit['isEdit'] = false;
        $edit['editableStatus'] = true;
        $edit['editableTime'] = null;
        $edit['deadlineTime'] = null;
        $data['edit'] = $edit;

        CacheHelper::forgetFresnsKey("fresns_api_user_panel_drafts_{$authUser->uid}", 'fresnsUsers');

        return $this->success($data);
    }

    // generate
    public function generate($type, $fsid)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        if ($type != 'post' && $type != 'comment') {
            throw new ApiException(30002);
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
            return $fresnsResp->errorResponse();
        }

        // session log
        $logType = match ($type) {
            'post' => SessionLog::TYPE_POST_CREATE_DRAFT,
            'comment' => SessionLog::TYPE_COMMENT_CREATE_DRAFT,
        };
        $sessionLog = [
            'type' => $logType,
            'pluginUnikey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'langTag' => $langTag,
            'aid' => $this->account()->aid,
            'uid' => $authUser->uid,
            'objectName' => \request()->path(),
            'objectAction' => 'Editor Generate Draft',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => $fresnsResp->getData('logId'),
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'moreJson' => null,
        ];

        // upload session log
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        switch ($type) {
            case 'post':
                $service = new PostService();

                $postLog = PostLog::with(['parentPost', 'group', 'creator'])->where('id', $fresnsResp->getData('logId'))->first();
                $data['detail'] = $service->postLogData($postLog, 'detail', $langTag, $timezone, $authUser->id);
                break;

            case 'comment':
                $service = new CommentService();

                $commentLog = CommentLog::with(['parentComment', 'post', 'creator'])->where('id', $fresnsResp->getData('logId'))->first();
                $data['detail'] = $service->commentLogData($commentLog, 'detail', $langTag, $timezone, $authUser->id);
                break;
        }

        $edit['isEdit'] = true;
        $edit['editableStatus'] = $fresnsResp->getData('editableStatus');
        $edit['editableTime'] = $fresnsResp->getData('editableTime');
        $edit['deadlineTime'] = $fresnsResp->getData('deadlineTime');
        $data['edit'] = $edit;

        CacheHelper::forgetFresnsKey("fresns_api_user_panel_drafts_{$authUser->uid}", 'fresnsUsers');

        return $this->success($data);
    }

    // detail
    public function detail($type, $draftId)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $draft = match ($type) {
            'post' => PostLog::with(['parentPost', 'group', 'creator'])->where('id', $draftId)->where('user_id', $authUser->id)->first(),
            'comment' => CommentLog::with(['parentComment', 'post', 'creator'])->where('id', $draftId)->where('user_id', $authUser->id)->first(),
            default => null,
        };

        if (empty($draft)) {
            throw new ApiException(38100);
        }

        if ($draft->state == 2) {
            throw new ApiException(38101);
        }

        if ($draft->state == 3) {
            throw new ApiException(38102);
        }

        $isEdit = false;
        $editableStatus = true;
        $editableTime = null;
        $deadlineTime = null;

        $editTimeConfig = ConfigHelper::fresnsConfigByItemKey("{$type}_edit_time_limit");

        switch ($type) {
            case 'post':
                $service = new PostService();
                $data['detail'] = $service->postLogData($draft, 'detail', $langTag, $timezone, $authUser->id);

                if ($draft->post_id) {
                    $isEdit = true;

                    $post = PrimaryHelper::fresnsModelById('post', $draft->post_id);

                    $checkContentEditPerm = PermissionUtility::checkContentEditPerm($post->created_at, $editTimeConfig, $timezone, $langTag);
                    $editableStatus = $checkContentEditPerm['editableStatus'];
                    $editableTime = $checkContentEditPerm['editableTime'];
                    $deadlineTime = $checkContentEditPerm['deadlineTime'];
                }
                break;

            case 'comment':
                $service = new CommentService();
                $data['detail'] = $service->commentLogData($draft, 'detail', $langTag, $timezone, $authUser->id);

                if ($draft->comment_id) {
                    $isEdit = true;

                    $comment = PrimaryHelper::fresnsModelById('comment', $draft->comment_id);

                    $checkContentEditPerm = PermissionUtility::checkContentEditPerm($comment->created_at, $editTimeConfig, $timezone, $langTag);
                    $editableStatus = $checkContentEditPerm['editableStatus'];
                    $editableTime = $checkContentEditPerm['editableTime'];
                    $deadlineTime = $checkContentEditPerm['deadlineTime'];
                }
                break;
        }

        $edit['isEdit'] = $isEdit;
        $edit['editableStatus'] = $editableStatus;
        $edit['editableTime'] = $editableTime;
        $edit['deadlineTime'] = $deadlineTime;
        $data['edit'] = $edit;

        return $this->success($data);
    }

    // update
    public function update($type, $draftId, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $requestData['draftId'] = $draftId;
        $dtoRequest = new EditorUpdateDTO($requestData);

        $authUser = $this->user();

        $draft = match ($type) {
            'post' => PostLog::where('id', $draftId)->where('user_id', $authUser->id)->first(),
            'comment' => CommentLog::where('id', $draftId)->where('user_id', $authUser->id)->first(),
            default => null,
        };

        if (empty($draft)) {
            throw new ApiException(38100);
        }

        if ($draft->state == 2) {
            throw new ApiException(38101);
        }

        if ($draft->state == 3) {
            throw new ApiException(38102);
        }

        // editorUnikey
        if ($dtoRequest->editorUnikey) {
            if ($dtoRequest->editorUnikey == 'Fresns' || $dtoRequest->editorUnikey == 'fresns') {
                $draft->update([
                    'is_plugin_editor' => 0,
                    'editor_unikey' => null,
                ]);
            } else {
                $editorPlugin = Plugin::where('unikey', $dtoRequest->editorUnikey)->first();
                if (empty($editorPlugin)) {
                    throw new ApiException(32101);
                }

                if (! $editorPlugin->is_enable) {
                    throw new ApiException(32102);
                }

                $draft->update([
                    'is_plugin_editor' => 1,
                    'editor_unikey' => $dtoRequest->editorUnikey,
                ]);
            }
        }

        // is post
        if ($dtoRequest->type == 'post') {
            // postGid
            if ($request->has('postGid')) {
                $group = PrimaryHelper::fresnsModelByFsid('group', $dtoRequest->postGid);

                if ($group) {
                    if (! $group->is_enable) {
                        throw new ApiException(37101);
                    }

                    $checkPerm = PermissionUtility::checkUserGroupPublishPerm($group->id, $group->permissions, $authUser->id);

                    if (! $checkPerm['allowPost']) {
                        throw new ApiException(36311);
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

            // postTitle
            if ($request->has('postTitle')) {
                if ($dtoRequest->postTitle) {
                    $postTitle = Str::of($dtoRequest->postTitle)->trim();
                    $checkBanWords = ValidationUtility::contentBanWords($postTitle);

                    if (! $checkBanWords) {
                        throw new ApiException(38206);
                    }

                    $draft->update([
                        'title' => $postTitle,
                    ]);
                } else {
                    $draft->update([
                        'title' => null,
                    ]);
                }
            }

            // postIsComment
            if (isset($dtoRequest->postIsComment)) {
                $draft->update([
                    'is_comment' => $dtoRequest->postIsComment,
                ]);
            }

            // postIsCommentPublic
            if (isset($dtoRequest->postIsCommentPublic)) {
                $draft->update([
                    'is_comment_public' => $dtoRequest->postIsCommentPublic,
                ]);
            }

            // postQuotePid
            if ($request->has('postQuotePid')) {
                $draft->update([
                    'parent_post_id' => PrimaryHelper::fresnsPostIdByPid($dtoRequest->postQuotePid),
                ]);
            }
        }

        // content
        if ($request->has('content')) {
            if ($dtoRequest->content) {
                $content = Str::of($dtoRequest->content)->trim();
                $checkBanWords = ValidationUtility::contentBanWords($content);

                if (! $checkBanWords) {
                    throw new ApiException(38207);
                }

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
                'is_markdown' => $dtoRequest->isMarkdown,
            ]);
        }

        // isAnonymous
        if (isset($dtoRequest->isAnonymous)) {
            $draft->update([
                'is_anonymous' => $dtoRequest->isAnonymous,
            ]);
        }

        // map
        if ($dtoRequest->map) {
            new MapDTO($dtoRequest->map);

            $draft->update([
                'map_json' => $dtoRequest->map,
            ]);
        }

        // extends
        if ($dtoRequest->extends) {
            $usageType = match ($type) {
                'post' => ExtendUsage::TYPE_POST_LOG,
                'comment' => ExtendUsage::TYPE_COMMENT_LOG,
            };

            ContentUtility::saveExtendUsages($usageType, $draft->id, $dtoRequest->extends);
        }

        // archives
        if ($dtoRequest->archives) {
            $usageType = match ($type) {
                'post' => ArchiveUsage::TYPE_POST_LOG,
                'comment' => ArchiveUsage::TYPE_COMMENT_LOG,
            };

            ContentUtility::saveArchiveUsages($usageType, $draft->id, $dtoRequest->archives);
        }

        // deleteMap
        if ($dtoRequest->deleteMap) {
            $draft->update([
                'map_json' => null,
            ]);
        }

        // deleteFile
        if ($dtoRequest->deleteFile) {
            $file = File::where('fid', $dtoRequest->deleteFile)->first();

            if (empty($file)) {
                throw new ApiException(36400);
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

        // deleteExtend
        if ($dtoRequest->deleteExtend) {
            $extend = Extend::where('eid', $dtoRequest->deleteExtend)->first();

            if (empty($extend)) {
                throw new ApiException(36400);
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
                throw new ApiException(36400);
            }

            if (! $extendUsage->can_delete) {
                throw new ApiException(36401);
            }

            $extendUsage->delete();
        }

        // deleteArchive
        if ($dtoRequest->deleteArchive) {
            $archive = Archive::where('code', $dtoRequest->deleteArchive)->first();

            if (empty($archive)) {
                throw new ApiException(32304);
            }

            $usageType = match ($type) {
                'post' => ArchiveUsage::TYPE_POST_LOG,
                'comment' => ArchiveUsage::TYPE_COMMENT_LOG,
            };

            $archiveUsage = ArchiveUsage::where('usage_type', $usageType)
                ->where('usage_id', $draft->id)
                ->where('archive_id', $archive->id)
                ->first();

            if (empty($archiveUsage)) {
                throw new ApiException(36400);
            }

            $archiveUsage->delete();
        }

        return $this->success();
    }

    // publish
    public function publish($type, $draftId)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $draft = match ($type) {
            'post' => PostLog::with('creator')->where('id', $draftId)->where('user_id', $authUser->id)->first(),
            'comment' => CommentLog::with('creator')->where('id', $draftId)->where('user_id', $authUser->id)->first(),
            default => null,
        };

        if (empty($draft)) {
            throw new ApiException(38100);
        }

        if ($draft->state == 2) {
            throw new ApiException(38103);
        }

        if ($draft->state == 3) {
            throw new ApiException(38104);
        }

        $mainId = match ($type) {
            'post' => $draft->post_id,
            'comment' => $draft->comment_id,
            default => null,
        };

        // check publish prem
        $publishService = new UserService;
        $publishService->checkPublishPerm($type, $authUser->id, $mainId, $langTag, $timezone);

        // session log
        $sessionLogType = match ($type) {
            'post' => SessionLog::TYPE_POST_REVIEW,
            'comment' => SessionLog::TYPE_COMMENT_REVIEW,
        };
        $sessionLog = [
            'type' => $sessionLogType,
            'pluginUnikey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'langTag' => $this->langTag(),
            'aid' => $this->account()->aid,
            'uid' => $authUser->uid,
            'objectName' => \request()->path(),
            'objectAction' => 'Editor Publish',
            'objectResult' => SessionLog::STATE_UNKNOWN,
            'objectOrderId' => $draft->id,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'moreJson' => null,
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
            // upload session log
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            // change state
            $draft->update([
                'state' => 2,
                'submit_at' => now(),
            ]);

            // review notice
            $contentReviewService = ConfigHelper::fresnsConfigByItemKey('content_review_service');
            \FresnsCmdWord::plugin($contentReviewService)->reviewNotice($wordBody);

            // Review
            throw new ApiException(38200);
        }

        if ($checkDraftCode) {
            throw new ApiException($checkDraftCode);
        }

        $draft->update([
            'submit_at' => now(),
        ]);

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->contentPublishByDraft($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->errorResponse();
        }

        // upload session log
        $sessionLogType = match ($type) {
            'post' => SessionLog::TYPE_POST_PUBLISH,
            'comment' => SessionLog::TYPE_COMMENT_PUBLISH,
        };
        $sessionLog['type'] = $sessionLogType;
        $sessionLog['objectResult'] = SessionLog::STATE_SUCCESS;
        $sessionLog['objectOrderId'] = $fresnsResp->getData('id');
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        CacheHelper::forgetFresnsKey("fresns_api_user_panel_drafts_{$authUser->uid}", 'fresnsUsers');

        return $this->success();
    }

    // recall
    public function recall($type, $draftId)
    {
        $authUser = $this->user();

        switch ($type) {
            case 'post':
                $draft = PostLog::where('user_id', $authUser->id)->where('id', $draftId)->first();
                break;

            case 'comment':
                $draft = CommentLog::where('user_id', $authUser->id)->where('id', $draftId)->first();
                break;

            default:
                throw new ApiException(30002);
                break;
        }

        if (empty($draft)) {
            throw new ApiException(38100);
        }

        if ($draft->state != 2) {
            throw new ApiException(36501);
        }

        $draft->update([
            'state' => 1,
        ]);

        return $this->success();
    }

    // delete
    public function delete($type, $draftId)
    {
        $authUser = $this->user();

        switch ($type) {
            case 'post':
                $draft = PostLog::where('user_id', $authUser->id)->where('id', $draftId)->first();
                break;

            case 'comment':
                $draft = CommentLog::where('user_id', $authUser->id)->where('id', $draftId)->first();
                break;

            default:
                throw new ApiException(30002);
                break;
        }

        if (empty($draft)) {
            throw new ApiException(38100);
        }

        if ($draft->state == 2) {
            throw new ApiException(36404);
        }

        if ($draft->state == 3) {
            throw new ApiException(36405);
        }

        $draft->delete();

        CacheHelper::forgetFresnsKey("fresns_api_user_panel_drafts_{$authUser->uid}", 'fresnsUsers');

        return $this->success();
    }

    // quick publish
    public function quickPublish($type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new EditorQuickPublishDTO($requestData);

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        // check draft content
        $validDraft = [
            'userId' => $authUser->id,
            'postId' => null,
            'postGroupId' => PrimaryHelper::fresnsGroupIdByGid($dtoRequest->postGid),
            'postTitle' => $dtoRequest->postTitle,
            'commentId' => null,
            'commentPostId' => PrimaryHelper::fresnsPostIdByPid($dtoRequest->commentPid),
            'content' => $dtoRequest->content,
        ];
        $checkDraftCode = ValidationUtility::draft($dtoRequest->type, $validDraft);

        if ($checkDraftCode && $checkDraftCode != 38200) {
            throw new ApiException($checkDraftCode);
        }

        // check publish prem
        $publishService = new UserService;
        $publishService->checkPublishPerm($dtoRequest->type, $authUser->id, null, $langTag, $timezone);

        if ($dtoRequest->image) {
            $fileConfig = FileHelper::fresnsFileStorageConfigByType(File::TYPE_IMAGE);

            if (! $fileConfig['storageConfigStatus']) {
                throw new ApiException(32104);
            }

            if (! $fileConfig['service']) {
                throw new ApiException(32104);
            }

            $servicePlugin = Plugin::where('unikey', $fileConfig['service'])->isEnable()->first();

            if (! $servicePlugin) {
                throw new ApiException(32102);
            }
        }

        $map = null;
        if ($dtoRequest->map) {
            $map = json_decode($dtoRequest->map, true);
            new MapDTO($map);
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
            'createType' => 1,
            'postGid' => $dtoRequest->postGid,
            'postTitle' => $dtoRequest->postTitle,
            'postIsComment' => $dtoRequest->postIsComment,
            'postIsCommentPublic' => $dtoRequest->postIsCommentPublic,
            'postQuotePid' => $dtoRequest->postQuotePid,
            'commentPid' => $dtoRequest->commentPid,
            'commentCid' => $dtoRequest->commentCid,
            'content' => $dtoRequest->content,
            'isMarkdown' => $dtoRequest->isMarkdown,
            'isAnonymous' => $dtoRequest->isAnonymous,
            'map' => $map,
            'extends' => $extends,
            'archives' => $archives,
            'requireReview' => ($checkDraftCode == 38200),
        ];
        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->contentQuickPublish($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->errorResponse();
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
                'usageType' => $usageType,
                'platformId' => $this->platformId(),
                'tableName' => $tableName,
                'tableColumn' => 'id',
                'tableId' => $tableId,
                'tableKey' => null,
                'aid' => $this->account()->aid,
                'uid' => $authUser->uid,
                'type' => File::TYPE_IMAGE,
                'moreJson' => null,
                'file' => $dtoRequest->image,
            ];

            \FresnsCmdWord::plugin('Fresns')->uploadFile($fileWordBody);
        }

        // session log
        $sessionLog = [
            'type' => $logType,
            'pluginUnikey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'langTag' => $this->langTag(),
            'aid' => $this->account()->aid,
            'uid' => $authUser->uid,
            'objectName' => \request()->path(),
            'objectAction' => "Editor quick publish {$dtoRequest->type}",
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => $tableId,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'moreJson' => null,
        ];

        // upload session log
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        CacheHelper::forgetFresnsKey("fresns_api_user_panel_drafts_{$authUser->uid}", 'fresnsUsers');

        $data = [
            'type' => $dtoRequest->type,
            'draftId' => $fresnsResp->getData('logId'),
            'fsid' => $fsid,
        ];

        if (! $fsid) {
            throw new ApiException(38200, 'Fresns', $data);
        }

        return $this->success($data);
    }
}
