<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\EditorCreateDTO;
use App\Fresns\Api\Http\DTO\EditorDirectPublishDTO;
use App\Fresns\Api\Http\DTO\EditorDraftsDTO;
use App\Fresns\Api\Http\DTO\EditorUpdateDTO;
use App\Fresns\Api\Services\CommentService;
use App\Fresns\Api\Services\PostService;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Models\CommentLog;
use App\Models\Extend;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Plugin;
use App\Models\PostLog;
use App\Models\SessionLog;
use App\Utilities\ConfigUtility;
use App\Utilities\PermissionUtility;
use App\Utilities\ValidationUtility;
use Carbon\Carbon;
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
            // post
            case 'post':
                $config['editor'] = ConfigUtility::getEditorConfigByType($authUser->id, 'post', $langTag);
                $config['publish'] = ConfigUtility::getPublishConfigByType($authUser->id, 'post', $langTag, $timezone);
                $config['edit'] = ConfigUtility::getEditConfigByType('post');
            break;

            // comment
            case 'comment':
                $config['editor'] = ConfigUtility::getEditorConfigByType($authUser->id, 'comment', $langTag);
                $config['publish'] = ConfigUtility::getPublishConfigByType($authUser->id, 'comment', $langTag, $timezone);
                $config['edit'] = ConfigUtility::getEditConfigByType('comment');
            break;

            // default
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
            // post
            case 'post':
                $drafts = PostLog::with('creator')
                    ->where('user_id', $authUser->id)
                    ->whereIn('state', $status)
                    ->latest()
                    ->paginate($request->get('pageSize', 15));

                $service = new PostService();
                foreach ($drafts as $draft) {
                    $draftList[] = $service->postLogData($draft, 'list', $langTag, $timezone);
                }
            break;

            // comment
            case 'comment':
                $drafts = CommentLog::with('user')
                    ->where('user_id', $authUser->id)
                    ->whereIn('state', $status)
                    ->latest()
                    ->paginate($request->get('pageSize', 15));

                $service = new CommentService();
                foreach ($drafts as $draft) {
                    $draftList[] = $service->commentLogData($draft, 'list', $langTag, $timezone);
                }
            break;

            // default
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

        $userRolePerm = PermissionUtility::getUserMainRolePerm($authUser->id);

        switch ($dtoRequest->type) {
            // post
            case 'post':
                if (! $userRolePerm['post_publish']) {
                    throw new ApiException(36104);
                }

                $checkLogCount = PostLog::where('user_id', $authUser->id)->whereIn('state', [1, 2, 4])->count();

                if ($checkLogCount >= $userRolePerm['post_draft_count']) {
                    throw new ApiException(38106);
                }
            break;

            // comment
            case 'comment':
                if (! $userRolePerm['comment_publish']) {
                    throw new ApiException(36104);
                }

                $checkCommentPerm = PermissionUtility::checkPostCommentPerm($dtoRequest->pid, $authUser->id);
                if (! $checkCommentPerm) {
                    throw new ApiException(38108);
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
            'commentPid' => $dtoRequest->commentPid,
            'commentCid' => $dtoRequest->commentCid,
            'content' => $dtoRequest->content,
            'isMarkdown' => $dtoRequest->isMarkdown,
            'isAnonymous' => $dtoRequest->isAnonymous,
            'mapJson' => $dtoRequest->mapJson,
            'eid' => $dtoRequest->eid,
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
            // post
            case 'post':
                $service = new PostService();

                $postLog = PostLog::where('id', $fresnsResp->getData('logId'))->first();
                $data['detail'] = $service->postLogData($postLog, 'detail', $langTag, $timezone);
            break;

            // comment
            case 'comment':
                $service = new CommentService();

                $commentLog = CommentLog::where('id', $fresnsResp->getData('logId'))->first();
                $data['detail'] = $service->commentLogData($commentLog, 'detail', $langTag, $timezone);
            break;
        }

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
            // post
            case 'post':
                $service = new PostService();

                $postLog = PostLog::where('id', $fresnsResp->getData('logId'))->first();
                $data['detail'] = $service->postLogData($postLog, 'detail', $langTag, $timezone);
            break;

            // comment
            case 'comment':
                $service = new CommentService();

                $commentLog = CommentLog::where('id', $fresnsResp->getData('logId'))->first();
                $data['detail'] = $service->commentLogData($commentLog, 'detail', $langTag, $timezone);
            break;
        }

        $edit['isEdit'] = true;
        $edit['editableStatus'] = $fresnsResp->getData('editableStatus');
        $edit['editableTime'] = $fresnsResp->getData('editableTime');
        $edit['deadlineTime'] = $fresnsResp->getData('deadlineTime');
        $data['edit'] = $edit;

        return $this->success($data);
    }

    // detail
    public function detail($type, $draftId)
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
            // post
            case 'post':
                $service = new PostService();
                $data['detail'] = $service->postLogData($draft, 'detail', $langTag, $timezone);

                if ($draft->post_id) {
                    $isEdit = true;

                    $post = PrimaryHelper::fresnsModelById('post', $draft->post_id);

                    $checkContentEditPerm = PermissionUtility::checkContentEditPerm($post->created_at, $editTimeConfig, $timezone, $langTag);
                    $editableStatus = $checkContentEditPerm['editableStatus'];
                    $editableTime = $checkContentEditPerm['editableTime'];
                    $deadlineTime = $checkContentEditPerm['deadlineTime'];
                }
            break;

            // comment
            case 'comment':
                $service = new CommentService();
                $data['detail'] = $service->commentLogData($draft, 'detail', $langTag, $timezone);

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

                if ($editorPlugin->is_enable == 0) {
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
            if ($dtoRequest->postGid) {
                $group = PrimaryHelper::fresnsModelByFsid('group', $dtoRequest->postGid);

                if (empty($group)) {
                    throw new ApiException(37100);
                }

                if ($group->is_enable == 0) {
                    throw new ApiException(37101);
                }

                $checkPerm = PermissionUtility::checkUserGroupPublishPerm($group->id, $group->permissions, $authUser->id);

                if (! $checkPerm['allowPost']) {
                    throw new ApiException(36311);
                }

                $draft->update([
                    'group_id' => $group->id,
                ]);
            }

            // postTitle
            if ($dtoRequest->postTitle) {
                $postTitle = Str::of($dtoRequest->postTitle)->trim();
                $checkBanWords = ValidationUtility::contentBanWords($postTitle);

                if (! $checkBanWords) {
                    throw new ApiException(38206);
                }

                $draft->update([
                    'title' => $postTitle,
                ]);
            }

            // postIsComment
            if ($dtoRequest->postIsComment) {
                $draft->update([
                    'is_comment' => $dtoRequest->postIsComment,
                ]);
            }

            // postIsCommentPublic
            if ($dtoRequest->postIsCommentPublic) {
                $draft->update([
                    'is_comment_public' => $dtoRequest->postIsCommentPublic,
                ]);
            }
        }

        // content
        if ($dtoRequest->content) {
            $content = Str::of($dtoRequest->content)->trim();
            $checkBanWords = ValidationUtility::contentBanWords($content);

            if (! $checkBanWords) {
                throw new ApiException(38207);
            }

            $draft->update([
                'content' => $content,
            ]);
        }

        // isMarkdown
        if ($dtoRequest->isMarkdown) {
            $draft->update([
                'is_markdown' => $dtoRequest->isMarkdown,
            ]);
        }

        // isAnonymous
        if ($dtoRequest->isAnonymous) {
            $draft->update([
                'is_anonymous' => $dtoRequest->isAnonymous,
            ]);
        }

        // mapJson
        if ($dtoRequest->mapJson) {
            $draft->update([
                'map_json' => $dtoRequest->mapJson,
            ]);
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

            if ($extendUsage->can_delete == 0) {
                throw new ApiException(36401);
            }

            $extendUsage->delete();
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

        if ($type == 'comment') {
            $checkCommentPerm = PermissionUtility::checkPostCommentPerm($draft->post_id, $authUser->id);

            if (! $checkCommentPerm) {
                throw new ApiException(38108);
            }
        }

        $editorConfig = ConfigHelper::fresnsConfigByItemKeys([
            "{$type}_editor_title_length",
            "{$type}_editor_content_length",
            "{$type}_edit_time_limit",
            'content_review_service',
        ]);

        if ($draft->title) {
            $titleLength = Str::length($draft->title);
            if ($titleLength > $editorConfig['post_editor_title_length']) {
                throw new ApiException(38203);
            }

            $checkTitleBanWords = ValidationUtility::contentBanWords($draft->title);
            if (! $checkTitleBanWords) {
                throw new ApiException(38206);
            }
        }

        if (! $draft->content) {
            throw new ApiException(38204);
        } else {
            $contentLength = Str::length($draft->content);
            if ($contentLength > $editorConfig["{$type}_editor_content_length"]) {
                throw new ApiException(38205);
            }

            $checkContentBanWords = ValidationUtility::contentBanWords($draft->content);
            if (! $checkContentBanWords) {
                throw new ApiException(38207);
            }
        }

        $publishConfig = ConfigUtility::getPublishConfigByType($authUser->id, $type, $langTag, $timezone);

        if (! $publishConfig['perm']['publish']) {
            return $this->failure([
                36104,
                ConfigUtility::getCodeMessage(36104, 'Fresns', $langTag),
                $publishConfig['perm']['tips'],
            ]);
        }

        if ($publishConfig['limit']['status']) {
            switch ($publishConfig['limit']['type']) {
                // period Y-m-d H:i:s
                case 1:
                    $dbDateTime = DateHelper::fresnsDatabaseCurrentDateTime();
                    $newDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $dbDateTime);
                    $periodStart = Carbon::createFromFormat('Y-m-d H:i:s', $publishConfig['limit']['periodStart']);
                    $periodEnd = Carbon::createFromFormat('Y-m-d H:i:s', $publishConfig['limit']['periodEnd']);

                    $isInTime = $newDateTime->between($periodStart, $periodEnd);
                    if ($isInTime) {
                        throw new ApiException(36304);
                    }
                break;

                // cycle H:i
                case 2:
                    $dbDateTime = DateHelper::fresnsDatabaseCurrentDateTime();
                    $newDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $dbDateTime);
                    $dbDate = date('Y-m-d', $dbDateTime);
                    $cycleStart = "{$dbDate} {$publishConfig['limit']['cycleStart']}:00"; // Y-m-d H:i:s
                    $cycleEnd = "{$dbDate} {$publishConfig['limit']['cycleEnd']}:00"; // Y-m-d H:i:s

                    $periodStart = Carbon::createFromFormat('Y-m-d H:i:s', $cycleStart); // 2022-07-01 22:30:00
                    $periodEnd = Carbon::createFromFormat('Y-m-d H:i:s', $cycleEnd); // 2022-07-01 08:30:00

                    if ($periodEnd->lt($periodStart)) {
                        // next day 2022-07-02 08:30:00
                        $periodEnd = $periodEnd->addDay();
                    }

                    $isInTime = $newDateTime->between($periodStart, $periodEnd);
                    if ($isInTime) {
                        throw new ApiException(36304);
                    }
                break;
            }
        }

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

        switch ($type) {
            // post
            case 'post':
                if (! $draft->post_id) {
                    $post = PrimaryHelper::fresnsModelById('post', $draft->post_id);

                    if ($post?->created_at) {
                        $checkContentEditPerm = PermissionUtility::checkContentEditPerm($post->created_at, $editorConfig['post_edit_time_limit'], $timezone, $langTag);

                        if (! $checkContentEditPerm['editableStatus']) {
                            throw new ApiException(36309);
                        }
                    }
                }

                if ($draft->group_id) {
                    $group = PrimaryHelper::fresnsModelById('group', $draft->group_id);

                    if (! $group) {
                        throw new ApiException(37100);
                    }

                    if ($group->is_enable == 0) {
                        throw new ApiException(37101);
                    }

                    $checkGroup = PermissionUtility::checkUserGroupPublishPerm($draft->group_id, $group->permissions, $draft->user_id);

                    if (! $checkGroup['allowPost']) {
                        throw new ApiException(36311);
                    }

                    if ($checkGroup['reviewPost']) {
                        // upload session log
                        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

                        // change state
                        $draft->update([
                            'state' => 2,
                            'submit_at' => now(),
                        ]);

                        // review notice
                        \FresnsCmdWord::plugin($editorConfig['content_review_service'])->reviewNotice($wordBody);

                        // Review
                        throw new ApiException(38200);
                    }
                }
            break;

            // comment
            case 'comment':
                if (! $draft->comment_id) {
                    $comment = PrimaryHelper::fresnsModelById('comment', $draft->comment_id);

                    $checkContentEditPerm = PermissionUtility::checkContentEditPerm($comment->created_at, $editorConfig['comment_edit_time_limit'], $timezone, $langTag);

                    if (! $checkContentEditPerm['editableStatus']) {
                        throw new ApiException(36309);
                    }
                }

                $post = PrimaryHelper::fresnsModelById('post', $draft->post_id);
                if (! $post->group_id) {
                    $group = PrimaryHelper::fresnsModelById('group', $draft->group_id);

                    if (! $group) {
                        throw new ApiException(37100);
                    }

                    if ($group->is_enable == 0) {
                        throw new ApiException(37101);
                    }

                    $checkGroup = PermissionUtility::checkUserGroupPublishPerm($draft->group_id, $group->permissions, $draft->user_id);

                    if (! $checkGroup['allowComment']) {
                        throw new ApiException(36312);
                    }

                    if ($checkGroup['reviewComment']) {
                        // upload session log
                        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

                        // change state
                        $draft->update([
                            'state' => 2,
                            'submit_at' => now(),
                        ]);

                        // review notice
                        \FresnsCmdWord::plugin($editorConfig['content_review_service'])->reviewNotice($wordBody);

                        // Review
                        throw new ApiException(38200);
                    }
                }
            break;
        }

        $checkReview = ValidationUtility::contentReviewWords($draft->content);
        if (! $checkReview) {
            // upload session log
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            // change state
            $draft->update([
                'state' => 2,
                'submit_at' => now(),
            ]);

            // review notice
            \FresnsCmdWord::plugin($editorConfig['content_review_service'])->reviewNotice($wordBody);

            // Review
            throw new ApiException(38200);
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

        return $this->success();
    }

    // recall
    public function recall($type, $draftId)
    {
        $authUser = $this->user();

        switch ($type) {
            // post
            case 'post':
                $draft = PostLog::where('user_id', $authUser->id)->where('id', $draftId)->first();
            break;

            // comment
            case 'comment':
                $draft = CommentLog::where('user_id', $authUser->id)->where('id', $draftId)->first();
            break;

            // default
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
            // post
            case 'post':
                $draft = PostLog::where('user_id', $authUser->id)->where('id', $draftId)->first();
            break;

            // comment
            case 'comment':
                $draft = CommentLog::where('user_id', $authUser->id)->where('id', $draftId)->first();
            break;

            // default
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

        return $this->success();
    }

    // directPublish
    public function directPublish(Request $request)
    {
        $dtoRequest = new EditorDirectPublishDTO($request->all());

        $authUser = $this->user();

        $fileConfig = FileHelper::fresnsFileStorageConfigByType(File::TYPE_IMAGE);
        if ($dtoRequest->file) {
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

        $wordType = match ($dtoRequest->type) {
            'post' => 1,
            'comment' => 2,
        };

        $wordBody = [
            'uid' => $authUser->uid,
            'type' => $wordType,
            'createType' => $wordType,
            'postGid' => $dtoRequest->postGid,
            'postTitle' => $dtoRequest->postTitle,
            'postIsComment' => $dtoRequest->postIsComment,
            'postIsCommentPublic' => $dtoRequest->postIsCommentPublic,
            'commentPid' => $dtoRequest->commentPid,
            'commentCid' => $dtoRequest->commentCid,
            'content' => $dtoRequest->content,
            'isMarkdown' => $dtoRequest->isMarkdown,
            'isAnonymous' => $dtoRequest->isAnonymous,
            'mapJson' => $dtoRequest->mapJson,
            'eid' => $dtoRequest->eid,
        ];
        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->contentDirectPublish($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->errorResponse();
        }

        $usageType = match ($fresnsResp->getData('type')) {
            1 => FileUsage::TYPE_POST,
            2 => FileUsage::TYPE_COMMENT,
        };

        $fsid = $fresnsResp->getData('fsid') ?? null;

        if (! $fsid) {
            $tableName = match ($fresnsResp->getData('type')) {
                1 => 'post_logs',
                2 => 'comment_logs',
            };

            $tableId = $fresnsResp->getData('logId');

            $logType = match ($fresnsResp->getData('type')) {
                1 => SessionLog::TYPE_POST_REVIEW,
                2 => SessionLog::TYPE_COMMENT_REVIEW,
            };
        } else {
            $tableName = match ($fresnsResp->getData('type')) {
                1 => 'posts',
                2 => 'comments',
            };

            $tableId = $fresnsResp->getData('id');

            $logType = match ($fresnsResp->getData('type')) {
                1 => SessionLog::TYPE_POST_PUBLISH,
                2 => SessionLog::TYPE_COMMENT_PUBLISH,
            };
        }

        // upload file
        if ($dtoRequest->file) {
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
                'file' => $dtoRequest->file,
            ];

            \FresnsCmdWord::plugin('Fresns')->uploadFile($fileWordBody);
        }

        // session log
        $sessionLog = [
            'type' => $logType,
            'pluginUnikey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'langTag' => $this->langTag(),
            'aid' => $this->account()->aid,
            'uid' => $authUser->uid,
            'objectName' => \request()->path(),
            'objectAction' => 'Editor Create Post Log',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => $tableId,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'moreJson' => null,
        ];

        // upload session log
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        if ($fsid) {
            return $this->success();
        } else {
            throw new ApiException(38200);
        }
    }
}
