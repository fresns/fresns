<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content;

use App\Fresns\Words\Content\DTO\AddContentMoreInfoDTO;
use App\Fresns\Words\Content\DTO\ContentPublishByDraftDTO;
use App\Fresns\Words\Content\DTO\ContentQuickPublishDTO;
use App\Fresns\Words\Content\DTO\CreateDraftDTO;
use App\Fresns\Words\Content\DTO\GenerateDraftDTO;
use App\Fresns\Words\Content\DTO\LocationInfoDTO;
use App\Fresns\Words\Content\DTO\LogicalDeletionContentDTO;
use App\Fresns\Words\Content\DTO\PhysicalDeletionContentDTO;
use App\Fresns\Words\Content\DTO\SetContentCloseDeleteDTO;
use App\Fresns\Words\Content\DTO\SetContentStickyAndDigestDTO;
use App\Fresns\Words\Content\DTO\SetPostAffiliateUserDTO;
use App\Fresns\Words\Content\DTO\SetPostAuthDTO;
use App\Helpers\AppHelper;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\App;
use App\Models\ArchiveUsage;
use App\Models\Comment;
use App\Models\CommentLog;
use App\Models\DomainLinkUsage;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\GeotagUsage;
use App\Models\HashtagUsage;
use App\Models\Mention;
use App\Models\OperationUsage;
use App\Models\Post;
use App\Models\PostAuth;
use App\Models\PostLog;
use App\Models\PostUser;
use App\Utilities\ConfigUtility;
use App\Utilities\ContentUtility;
use App\Utilities\InteractionUtility;
use App\Utilities\PermissionUtility;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Content
{
    const TABLE_MAIN = 1;
    const TABLE_LOG = 2;

    const TYPE_POST = 1;
    const TYPE_COMMENT = 2;

    use CmdWordResponseTrait;

    // createDraft
    public function createDraft($wordBody)
    {
        $dtoWordBody = new CreateDraftDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        $author = PrimaryHelper::fresnsModelByFsid('user', $dtoWordBody->uid);
        if (! $author) {
            return $this->failure(
                35201,
                ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
            );
        }
        if (! $author->is_enabled) {
            return $this->failure(
                35202,
                ConfigUtility::getCodeMessage(35202, 'Fresns', $langTag)
            );
        }

        $permissions = [];

        if ($dtoWordBody->editorFskey) {
            $editorPlugin = App::where('fskey', $dtoWordBody->editorFskey)->whereIn('type', [App::TYPE_PLUGIN, App::TYPE_APP_REMOTE])->first();

            $permissions['editor'] = [
                'isAppEditor' => $editorPlugin ? true : false,
                'editorFskey' => $editorPlugin->fskey,
            ];
        }

        $content = null;
        if ($dtoWordBody->content) {
            $content = Str::of($dtoWordBody->content)->trim();
        }

        switch ($dtoWordBody->type) {
            case Content::TYPE_POST:
                $title = null;
                if ($dtoWordBody->title) {
                    $title = Str::of($dtoWordBody->title)->trim();
                }

                $checkLog = PostLog::with(['fileUsages', 'extendUsages'])->where('user_id', $author->id)->where('create_type', 1)->where('state', PostLog::STATE_DRAFT)->first();

                $permissions['commentConfig']['policy'] = $dtoWordBody->commentPolicy;
                $permissions['commentConfig']['privacy'] = $dtoWordBody->commentPrivate ? 'private' : 'public';

                $logData = [
                    'user_id' => $author->id,
                    'create_type' => $dtoWordBody->createType,
                    'quoted_post_id' => PrimaryHelper::fresnsPrimaryId('post', $dtoWordBody->quotePid),
                    'group_id' => PrimaryHelper::fresnsPrimaryId('group', $dtoWordBody->gid),
                    'geotag_id' => PrimaryHelper::fresnsPrimaryId('geotag', $dtoWordBody->gtid),
                    'title' => $title,
                    'content' => $content,
                    'is_markdown' => $dtoWordBody->isMarkdown ?? 0,
                    'is_anonymous' => $dtoWordBody->isAnonymous ?? 0,
                    'location_info' => $dtoWordBody->locationInfo,
                    'permissions' => $permissions,
                ];

                if (empty($checkLog)) {
                    $logModel = PostLog::create($logData);
                } else {
                    if (empty($checkLog->content) && $checkLog->fileUsages->isEmpty() && $checkLog->extendUsages->isEmpty()) {
                        $checkLog->update($logData);
                        $logModel = $checkLog;
                    } else {
                        $logModel = PostLog::create($logData);
                    }
                }
                break;

            case Content::TYPE_COMMENT:
                // comment
                $checkPost = Post::where('pid', $dtoWordBody->commentPid)->first();
                if (empty($checkPost)) {
                    return $this->failure(
                        37400,
                        ConfigUtility::getCodeMessage(37400, 'Fresns', $langTag)
                    );
                }

                $checkLog = CommentLog::with(['fileUsages', 'extendUsages'])->where('user_id', $author->id)->where('create_type', 1)->where('state', CommentLog::STATE_DRAFT)->first();

                $logData = [
                    'user_id' => $author->id,
                    'create_type' => $dtoWordBody->createType,
                    'post_id' => $checkPost->id,
                    'parent_comment_id' => PrimaryHelper::fresnsPrimaryId('comment', $checkPost->commentCid),
                    'geotag_id' => PrimaryHelper::fresnsPrimaryId('geotag', $dtoWordBody->gtid),
                    'content' => $content,
                    'is_markdown' => $dtoWordBody->isMarkdown ?? 0,
                    'is_anonymous' => $dtoWordBody->isAnonymous ?? 0,
                    'is_private' => $dtoWordBody->commentPrivate,
                    'location_info' => $dtoWordBody->locationInfo,
                    'permissions' => $permissions,
                ];

                if (empty($checkLog)) {
                    $logModel = CommentLog::create($logData);
                } else {
                    if ($checkLog->post_id == $checkPost->id) {
                        $checkLog->update($logData);
                        $logModel = $checkLog;
                    } else {
                        if (empty($checkLog->content) && $checkLog->fileUsages->isEmpty() && $checkLog->extendUsages->isEmpty()) {
                            $checkLog->update($logData);
                            $logModel = $checkLog;
                        } else {
                            $logModel = CommentLog::create($logData);
                        }
                    }
                }
                break;
        }

        // archives
        if ($dtoWordBody->archives) {
            $usageType = match ($dtoWordBody->type) {
                Content::TYPE_POST => ArchiveUsage::TYPE_POST_LOG,
                Content::TYPE_COMMENT => ArchiveUsage::TYPE_COMMENT_LOG,
            };

            ContentUtility::saveArchiveUsages($usageType, $logModel->id, $dtoWordBody->archives);
        }

        // extends
        if ($dtoWordBody->extends) {
            $usageType = match ($dtoWordBody->type) {
                Content::TYPE_POST => ExtendUsage::TYPE_POST_LOG,
                Content::TYPE_COMMENT => ExtendUsage::TYPE_COMMENT_LOG,
            };

            ContentUtility::saveExtendUsages($usageType, $logModel->id, $dtoWordBody->extends);
        }

        return $this->success([
            'type' => $dtoWordBody->type,
            'logId' => $logModel->id,
        ]);
    }

    // generateDraft
    public function generateDraft($wordBody)
    {
        $dtoWordBody = new GenerateDraftDTO($wordBody);

        $timezone = \request()->header('X-Fresns-Client-Timezone');
        $langTag = AppHelper::getLangTag();

        $editTimeLimit = match ($dtoWordBody->type) {
            Content::TYPE_POST => ConfigHelper::fresnsConfigByItemKey('post_edit_time_limit'),
            Content::TYPE_COMMENT => ConfigHelper::fresnsConfigByItemKey('comment_edit_time_limit'),
        };

        switch ($dtoWordBody->type) {
            case Content::TYPE_POST:
                // post
                $post = Post::where('pid', $dtoWordBody->fsid)->first();

                if (empty($post)) {
                    return $this->failure(
                        37400,
                        ConfigUtility::getCodeMessage(37400, 'Fresns', $langTag)
                    );
                }

                $author = PrimaryHelper::fresnsModelById('user', $post->user_id);
                if (! $author) {
                    return $this->failure(
                        35201,
                        ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
                    );
                }
                if (! $author->is_enabled) {
                    return $this->failure(
                        35202,
                        ConfigUtility::getCodeMessage(35202, 'Fresns', $langTag)
                    );
                }

                // check edit
                $checkEditCode = PermissionUtility::checkContentEdit('post', $post->created_at, $post->sticky_state, $post->digest_state);
                if ($checkEditCode) {
                    return $this->failure(
                        $checkEditCode,
                        ConfigUtility::getCodeMessage($checkEditCode, 'Fresns', $langTag)
                    );
                }

                $checkContentEditPerm = PermissionUtility::checkContentEditPerm($post->created_at, $editTimeLimit, $timezone, $langTag);
                $editableStatus = $checkContentEditPerm['editableStatus'];
                $editableTime = $checkContentEditPerm['editableTime'];
                $deadlineTime = $checkContentEditPerm['deadlineTime'];

                $logModel = ContentUtility::generatePostDraft($post);
                break;

            case Content::TYPE_COMMENT:
                // comment
                $comment = Comment::where('cid', $dtoWordBody->fsid)->first();

                if (empty($comment)) {
                    return $this->failure(
                        37500,
                        ConfigUtility::getCodeMessage(37500, 'Fresns', $langTag)
                    );
                }

                if ($comment->top_parent_id != 0) {
                    return $this->failure(
                        36314,
                        ConfigUtility::getCodeMessage(36314, 'Fresns', $langTag)
                    );
                }

                $author = PrimaryHelper::fresnsModelById('user', $comment->user_id);
                if (! $author) {
                    return $this->failure(
                        35201,
                        ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
                    );
                }
                if (! $author->is_enabled) {
                    return $this->failure(
                        35202,
                        ConfigUtility::getCodeMessage(35202, 'Fresns', $langTag)
                    );
                }

                // check edit
                $checkEditCode = PermissionUtility::checkContentEdit('comment', $comment->created_at, $comment->is_sticky, $comment->digest_state);
                if ($checkEditCode) {
                    return $this->failure(
                        $checkEditCode,
                        ConfigUtility::getCodeMessage($checkEditCode, 'Fresns', $langTag)
                    );
                }

                $checkContentEditPerm = PermissionUtility::checkContentEditPerm($comment->created_at, $editTimeLimit, $timezone, $langTag);
                $editableStatus = $checkContentEditPerm['editableStatus'];
                $editableTime = $checkContentEditPerm['editableTime'];
                $deadlineTime = $checkContentEditPerm['deadlineTime'];

                $logModel = ContentUtility::generateCommentDraft($comment);
                break;
        }

        return $this->success([
            'type' => $dtoWordBody->type,
            'logId' => $logModel->id,
            'editableStatus' => $editableStatus,
            'editableTime' => $editableTime,
            'deadlineTime' => $deadlineTime,
        ]);
    }

    // contentPublishByDraft
    public function contentPublishByDraft($wordBody)
    {
        $dtoWordBody = new ContentPublishByDraftDTO($wordBody);

        $logModel = match ($dtoWordBody->type) {
            Content::TYPE_POST => PostLog::with(['author'])->where('id', $dtoWordBody->logId)->first(),
            Content::TYPE_COMMENT => CommentLog::with(['author'])->where('id', $dtoWordBody->logId)->first(),
        };

        $langTag = AppHelper::getLangTag();

        if (empty($logModel)) {
            return $this->failure(
                38100,
                ConfigUtility::getCodeMessage(38100, 'Fresns', $langTag),
            );
        }

        if ($logModel->state == PostLog::STATE_SUCCESS) {
            return $this->failure(
                38104,
                ConfigUtility::getCodeMessage(38104, 'Fresns', $langTag),
            );
        }

        $author = $logModel->author;

        if (empty($author)) {
            return $this->failure(
                35201,
                ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
            );
        }
        if (! $author->is_enabled) {
            return $this->failure(
                35202,
                ConfigUtility::getCodeMessage(35202, 'Fresns', $langTag)
            );
        }

        switch ($dtoWordBody->type) {
            case Content::TYPE_POST:
                // post
                $postModel = PrimaryHelper::fresnsModelById('post', $logModel->post_id);
                if ($postModel) {
                    $checkEditCode = PermissionUtility::checkContentEdit('post', $postModel->created_at, $postModel->sticky_state, $postModel->digest_state);

                    if ($checkEditCode) {
                        return $this->failure(
                            $checkEditCode,
                            ConfigUtility::getCodeMessage($checkEditCode, 'Fresns', $langTag)
                        );
                    }
                }

                $post = ContentUtility::releasePost($logModel);

                $primaryId = $post->id;
                $fsid = $post->pid;
                break;

            case Content::TYPE_COMMENT:
                // comment
                $commentModel = PrimaryHelper::fresnsModelById('comment', $logModel->comment_id);
                if ($commentModel) {
                    $checkEditCode = PermissionUtility::checkContentEdit('comment', $commentModel->created_at, $commentModel->is_sticky, $commentModel->digest_state);

                    if ($checkEditCode) {
                        return $this->failure(
                            $checkEditCode,
                            ConfigUtility::getCodeMessage($checkEditCode, 'Fresns', $langTag)
                        );
                    }
                }

                $comment = ContentUtility::releaseComment($logModel);

                $primaryId = $comment->id;
                $fsid = $comment->pid;
                break;
        }

        return $this->success([
            'type' => $dtoWordBody->type,
            'id' => $primaryId,
            'fsid' => $fsid,
        ]);
    }

    // contentQuickPublish
    public function contentQuickPublish($wordBody)
    {
        $dtoWordBody = new ContentQuickPublishDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        $author = PrimaryHelper::fresnsModelByFsid('user', $dtoWordBody->uid);
        if (! $author) {
            return $this->failure(
                35201,
                ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
            );
        }
        if (! $author->is_enabled) {
            return $this->failure(
                35202,
                ConfigUtility::getCodeMessage(35202, 'Fresns', $langTag)
            );
        }

        $type = match ($dtoWordBody->type) {
            Content::TYPE_POST => 'post',
            Content::TYPE_COMMENT => 'comment',
        };

        // review
        if ($dtoWordBody->requireReview) {
            $wordBody['createType'] = 1;

            $reviewResp = \FresnsCmdWord::plugin('Fresns')->createDraft($wordBody);

            if ($reviewResp->isErrorResponse()) {
                return $reviewResp->errorResponse();
            }

            switch ($type) {
                case 'post':
                    PostLog::where('id', $reviewResp->getData('logId'))->update([
                        'state' => PostLog::STATE_UNDER_REVIEW,
                        'submit_at' => now(),
                    ]);

                    $usageType = ExtendUsage::TYPE_POST_LOG;
                    break;

                case 'comment':
                    CommentLog::where('id', $reviewResp->getData('logId'))->update([
                        'state' => CommentLog::STATE_UNDER_REVIEW,
                        'submit_at' => now(),
                    ]);

                    $usageType = ExtendUsage::TYPE_COMMENT_LOG;
                    break;
            }

            $logId = $reviewResp->getData('logId');

            // archives
            if ($dtoWordBody->archives) {
                ContentUtility::saveArchiveUsages($usageType, $logId, $dtoWordBody->archives);
            }

            // extends
            if ($dtoWordBody->extends) {
                ContentUtility::saveExtendUsages($usageType, $logId, $dtoWordBody->extends);
            }

            $reviewWordBody = [
                'type' => $dtoWordBody->type,
                'logId' => $logId,
            ];

            // review notice
            $contentReviewService = ConfigHelper::fresnsConfigByItemKey('content_review_service');
            \FresnsCmdWord::plugin($contentReviewService)->reviewNotice($reviewWordBody);

            return $this->success([
                'type' => $dtoWordBody->type,
                'logId' => $logId,
                'id' => null,
                'fsid' => null,
            ]);
        }

        // geotag or location info
        $geotag = PrimaryHelper::fresnsModelByFsid('geotag', $dtoWordBody->gtid);
        if (empty($geotag) && $dtoWordBody->locationInfo) {
            $geotag = ContentUtility::releaseLocationInfo($dtoWordBody->locationInfo);
        }

        switch ($type) {
            // post
            case 'post':
                $permissions['commentConfig']['policy'] = $dtoWordBody->commentPolicy;
                $permissions['commentConfig']['privacy'] = $dtoWordBody->commentPrivate ? 'private' : 'public';

                $post = Post::create([
                    'user_id' => $author->id,
                    'quoted_post_id' => PrimaryHelper::fresnsPrimaryId('post', $dtoWordBody->quotePid) ?? 0,
                    'group_id' => PrimaryHelper::fresnsPrimaryId('group', $dtoWordBody->gid) ?? 0,
                    'geotag_id' => $geotag?->id ?? 0,
                    'title' => $dtoWordBody->title ? Str::of($dtoWordBody->title)->trim() : null,
                    'content' => $dtoWordBody->content ? Str::of($dtoWordBody->content)->trim() : null,
                    'is_markdown' => $dtoWordBody->isMarkdown ?? 0,
                    'is_anonymous' => $dtoWordBody->isAnonymous ?? 0,
                    'permissions' => $permissions,
                ]);

                ContentUtility::handleAndSaveAllInteraction($post->content, Mention::TYPE_POST, $post->id, $post->user_id);
                InteractionUtility::publishStats('post', $post->id, 'increment');

                $usageType = ExtendUsage::TYPE_POST;

                $primaryId = $post->id;
                $fsid = $post->pid;
                break;

                // comment
            case 'comment':
                $commentTopParentId = 0;
                $parentComment = PrimaryHelper::fresnsModelByFsid('comment', $dtoWordBody->commentCid);
                if ($parentComment) {
                    $commentTopParentId = $parentComment->top_parent_id ?: $parentComment->id;
                }

                $post = PrimaryHelper::fresnsModelByFsid('post', $dtoWordBody->commentPid);
                $postPermissions = $post->permissions;
                $privacy = $postPermissions['commentConfig']['privacy'] ?? 'public';

                $privacyState = ($privacy == 'private') ? Comment::PRIVACY_PRIVATE_BY_POST : Comment::PRIVACY_PUBLIC;

                $comment = Comment::create([
                    'user_id' => $author->id,
                    'post_id' => $post->id,
                    'top_parent_id' => $commentTopParentId,
                    'parent_id' => $parentComment?->id ?? 0,
                    'geotag_id' => $geotag?->id ?? 0,
                    'content' => $dtoWordBody->content ? Str::of($dtoWordBody->content)->trim() : null,
                    'is_markdown' => $dtoWordBody->isMarkdown ?? 0,
                    'is_anonymous' => $dtoWordBody->isAnonymous ?? 0,
                    'privacy_state' => $privacyState,
                ]);

                ContentUtility::handleAndSaveAllInteraction($comment->content, Mention::TYPE_COMMENT, $comment->id, $comment->user_id);
                InteractionUtility::publishStats('comment', $comment->id, 'increment');

                $usageType = ExtendUsage::TYPE_COMMENT;

                $primaryId = $comment->id;
                $fsid = $comment->cid;
                break;
        }

        // archives
        if ($dtoWordBody->archives) {
            ContentUtility::saveArchiveUsages($usageType, $primaryId, $dtoWordBody->archives);
        }

        // extends
        if ($dtoWordBody->extends) {
            ContentUtility::saveExtendUsages($usageType, $primaryId, $dtoWordBody->extends);
        }

        // send notification
        InteractionUtility::sendPublishNotification($type, $primaryId);

        return $this->success([
            'type' => $dtoWordBody->type,
            'logId' => null,
            'id' => $primaryId,
            'fsid' => $fsid,
        ]);
    }

    // logicalDeletionContent
    public function logicalDeletionContent($wordBody)
    {
        $dtoWordBody = new LogicalDeletionContentDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        switch ($dtoWordBody->contentType) {
            case Content::TABLE_MAIN:
                // main
                $model = match ($dtoWordBody->type) {
                    Content::TYPE_POST => Post::where('pid', $dtoWordBody->contentFsid)->first(),
                    Content::TYPE_COMMENT => Comment::where('cid', $dtoWordBody->contentFsid)->first(),
                };

                if (empty($model)) {
                    return $this->failure(
                        36400,
                        ConfigUtility::getCodeMessage(36400, 'Fresns', $langTag)
                    );
                }

                // logs
                $logModels = match ($dtoWordBody->type) {
                    Content::TYPE_POST => PostLog::withTrashed()->where('post_id', $model->id)->get(),
                    Content::TYPE_COMMENT => CommentLog::withTrashed()->where('comment_id', $model->id)->get(),
                };

                foreach ($logModels as $log) {
                    \FresnsCmdWord::plugin('Fresns')->logicalDeletionContent([
                        'type' => $dtoWordBody->type,
                        'contentType' => Content::TABLE_LOG,
                        'contentLogId' => $log->id,
                    ]);
                }

                $type = match ($dtoWordBody->type) {
                    Content::TYPE_POST => 'post',
                    Content::TYPE_COMMENT => 'comment',
                };

                InteractionUtility::publishStats($type, $model->id, 'decrement');

                if ($dtoWordBody->type == Content::TYPE_POST) {
                    PostAuth::where('post_id', $model->id)->delete();
                    PostUser::where('post_id', $model->id)->delete();
                }

                $tableName = match ($dtoWordBody->type) {
                    Content::TYPE_POST => 'posts',
                    Content::TYPE_COMMENT => 'comments',
                };

                $usageType = match ($dtoWordBody->type) {
                    Content::TYPE_POST => OperationUsage::TYPE_POST,
                    Content::TYPE_COMMENT => OperationUsage::TYPE_COMMENT,
                };
                break;

            case Content::TABLE_LOG:
                // log
                $model = match ($dtoWordBody->type) {
                    Content::TYPE_POST => PostLog::where('id', $dtoWordBody->contentLogId)->first(),
                    Content::TYPE_COMMENT => CommentLog::where('id', $dtoWordBody->contentLogId)->first(),
                };

                if (empty($model)) {
                    return $this->failure(
                        36400,
                        ConfigUtility::getCodeMessage(36400, 'Fresns', $langTag)
                    );
                }

                $tableName = match ($dtoWordBody->type) {
                    Content::TYPE_POST => 'post_logs',
                    Content::TYPE_COMMENT => 'comment_logs',
                };

                $usageType = match ($dtoWordBody->type) {
                    Content::TYPE_POST => OperationUsage::TYPE_POST_LOG,
                    Content::TYPE_COMMENT => OperationUsage::TYPE_COMMENT_LOG,
                };
                break;
        }

        FileUsage::where('table_name', $tableName)->where('table_column', 'id')->where('table_id', $model->id)->delete();
        OperationUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->delete();
        ArchiveUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->delete();
        ExtendUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->delete();

        HashtagUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->delete();
        GeotagUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->delete();
        DomainLinkUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->delete();
        Mention::where('user_id', $model->user_id)->where('mention_type', $usageType)->where('mention_id', $model->id)->delete();

        $model->delete();

        return $this->success();
    }

    // physicalDeletionContent
    public function physicalDeletionContent($wordBody)
    {
        $dtoWordBody = new PhysicalDeletionContentDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        switch ($dtoWordBody->contentType) {
            case Content::TABLE_MAIN:
                // main
                $model = match ($dtoWordBody->type) {
                    Content::TYPE_POST => Post::withTrashed()->where('pid', $dtoWordBody->contentFsid)->first(),
                    Content::TYPE_COMMENT => Comment::withTrashed()->where('cid', $dtoWordBody->contentFsid)->first(),
                };

                if (empty($model)) {
                    return $this->failure(
                        36400,
                        ConfigUtility::getCodeMessage(36400, 'Fresns', $langTag)
                    );
                }

                // logs
                $logModels = match ($dtoWordBody->type) {
                    Content::TYPE_POST => PostLog::withTrashed()->where('post_id', $model->id)->get(),
                    Content::TYPE_COMMENT => CommentLog::withTrashed()->where('comment_id', $model->id)->get(),
                };

                foreach ($logModels as $log) {
                    \FresnsCmdWord::plugin('Fresns')->physicalDeletionContent([
                        'type' => $dtoWordBody->type,
                        'contentType' => Content::TABLE_LOG,
                        'contentLogId' => $log->id,
                    ]);
                }

                $type = match ($dtoWordBody->type) {
                    Content::TYPE_POST => 'post',
                    Content::TYPE_COMMENT => 'comment',
                };

                if (! $model->trashed()) {
                    InteractionUtility::publishStats($type, $model->id, 'decrement');
                }

                if ($dtoWordBody->type == Content::TYPE_POST) {
                    PostAuth::withTrashed()->where('post_id', $model->id)->forceDelete();
                    PostUser::withTrashed()->where('post_id', $model->id)->forceDelete();
                }

                $tableName = match ($dtoWordBody->type) {
                    Content::TYPE_POST => 'posts',
                    Content::TYPE_COMMENT => 'comments',
                };

                $usageType = match ($dtoWordBody->type) {
                    Content::TYPE_POST => OperationUsage::TYPE_POST,
                    Content::TYPE_COMMENT => OperationUsage::TYPE_COMMENT,
                };
                break;

            case Content::TABLE_LOG:
                // log
                $model = match ($dtoWordBody->type) {
                    Content::TYPE_POST => PostLog::withTrashed()->where('id', $dtoWordBody->contentLogId)->first(),
                    Content::TYPE_COMMENT => CommentLog::withTrashed()->where('id', $dtoWordBody->contentLogId)->first(),
                };

                if (empty($model)) {
                    return $this->failure(
                        36400,
                        ConfigUtility::getCodeMessage(36400, 'Fresns', $langTag)
                    );
                }

                $tableName = match ($dtoWordBody->type) {
                    Content::TYPE_POST => 'post_logs',
                    Content::TYPE_COMMENT => 'comment_logs',
                };

                $usageType = match ($dtoWordBody->type) {
                    Content::TYPE_POST => OperationUsage::TYPE_POST_LOG,
                    Content::TYPE_COMMENT => OperationUsage::TYPE_COMMENT_LOG,
                };
                break;
        }

        $fileIds = FileUsage::withTrashed()->where('table_name', $tableName)->where('table_column', 'id')->where('table_id', $model->id)->pluck('file_id')->toArray();
        FileUsage::withTrashed()->where('table_name', $tableName)->where('table_column', 'id')->where('table_id', $model->id)->forceDelete();

        OperationUsage::withTrashed()->where('usage_type', $usageType)->where('usage_id', $model->id)->forceDelete();
        ArchiveUsage::withTrashed()->where('usage_type', $usageType)->where('usage_id', $model->id)->forceDelete();
        ExtendUsage::withTrashed()->where('usage_type', $usageType)->where('usage_id', $model->id)->forceDelete();

        HashtagUsage::withTrashed()->where('usage_type', $usageType)->where('usage_id', $model->id)->forceDelete();
        GeotagUsage::withTrashed()->where('usage_type', $usageType)->where('usage_id', $model->id)->forceDelete();
        DomainLinkUsage::withTrashed()->where('usage_type', $usageType)->where('usage_id', $model->id)->forceDelete();
        Mention::withTrashed()->where('user_id', $model->user_id)->where('mention_type', $usageType)->where('mention_id', $model->id)->forceDelete();

        $model->forceDelete();

        $fileList = File::doesntHave('fileUsages')->whereIn('id', $fileIds)->get()->groupBy('type');

        $files = [
            File::TYPE_IMAGE => $fileList->get(File::TYPE_IMAGE)?->pluck('id')?->all() ?? [],
            File::TYPE_VIDEO => $fileList->get(File::TYPE_VIDEO)?->pluck('id')?->all() ?? [],
            File::TYPE_AUDIO => $fileList->get(File::TYPE_AUDIO)?->pluck('id')?->all() ?? [],
            File::TYPE_DOCUMENT => $fileList->get(File::TYPE_DOCUMENT)?->pluck('id')?->all() ?? [],
        ];

        foreach ($files as $type => $ids) {
            if (empty($ids)) {
                continue;
            }

            \FresnsCmdWord::plugin('Fresns')->physicalDeletionFiles([
                'type' => $type,
                'fileIdsOrFids' => $ids,
            ]);
        }

        return $this->success();
    }

    // addContentMoreInfo
    public function addContentMoreInfo($wordBody)
    {
        $dtoWordBody = new AddContentMoreInfoDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        $model = match ($dtoWordBody->type) {
            Content::TYPE_POST => Post::where('pid', $dtoWordBody->fsid)->first(),
            Content::TYPE_COMMENT => Comment::where('cid', $dtoWordBody->fsid)->first(),
        };

        $errorCode = match ($dtoWordBody->type) {
            Content::TYPE_POST => 37400,
            Content::TYPE_COMMENT => 37500,
        };

        if (empty($model)) {
            return $this->failure(
                $errorCode,
                ConfigUtility::getCodeMessage($errorCode, 'Fresns', $langTag)
            );
        }

        $moreInfo = $model->more_info ?? [];

        $newMoreInfo = Arr::add($moreInfo, $dtoWordBody->key, $dtoWordBody->value);

        $model->update([
            'more_info' => $newMoreInfo,
        ]);

        $typeName = match ($dtoWordBody->type) {
            Content::TYPE_POST => 'post',
            Content::TYPE_COMMENT => 'comment',
        };

        CacheHelper::clearDataCache($typeName, $dtoWordBody->fsid);

        return $this->success();
    }

    // setContentSticky
    public function setContentSticky($wordBody)
    {
        $dtoWordBody = new setContentStickyAndDigestDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        $primaryId = match ($dtoWordBody->type) {
            Content::TYPE_POST => PrimaryHelper::fresnsPrimaryId('post', $dtoWordBody->fsid),
            Content::TYPE_COMMENT => PrimaryHelper::fresnsPrimaryId('comment', $dtoWordBody->fsid),
        };

        $errorCode = match ($dtoWordBody->type) {
            Content::TYPE_POST => 37400,
            Content::TYPE_COMMENT => 37500,
        };

        if (empty($primaryId)) {
            return $this->failure(
                $errorCode,
                ConfigUtility::getCodeMessage($errorCode, 'Fresns', $langTag)
            );
        }

        $typeName = match ($dtoWordBody->type) {
            Content::TYPE_POST => 'post',
            Content::TYPE_COMMENT => 'comment',
        };

        InteractionUtility::markContentSticky($typeName, $primaryId, $dtoWordBody->state);

        CacheHelper::clearDataCache($typeName, $dtoWordBody->fsid);

        return $this->success();
    }

    // setContentDigest
    public function setContentDigest($wordBody)
    {
        $dtoWordBody = new SetContentStickyAndDigestDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        $primaryId = match ($dtoWordBody->type) {
            Content::TYPE_POST => PrimaryHelper::fresnsPrimaryId('post', $dtoWordBody->fsid),
            Content::TYPE_COMMENT => PrimaryHelper::fresnsPrimaryId('comment', $dtoWordBody->fsid),
        };

        $errorCode = match ($dtoWordBody->type) {
            Content::TYPE_POST => 37400,
            Content::TYPE_COMMENT => 37500,
        };

        if (empty($primaryId)) {
            return $this->failure(
                $errorCode,
                ConfigUtility::getCodeMessage($errorCode, 'Fresns', $langTag)
            );
        }

        $typeName = match ($dtoWordBody->type) {
            Content::TYPE_POST => 'post',
            Content::TYPE_COMMENT => 'comment',
        };

        InteractionUtility::markContentDigest($typeName, $primaryId, $dtoWordBody->state);

        CacheHelper::clearDataCache($typeName, $dtoWordBody->fsid);

        return $this->success();
    }

    // setContentCloseDelete
    public function setContentCloseDelete($wordBody)
    {
        $dtoWordBody = new SetContentCloseDeleteDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        $model = match ($dtoWordBody->type) {
            Content::TYPE_POST => Post::where('pid', $dtoWordBody->fsid)->first(),
            Content::TYPE_COMMENT => Comment::where('cid', $dtoWordBody->fsid)->first(),
        };

        $errorCode = match ($dtoWordBody->type) {
            Content::TYPE_POST => 37400,
            Content::TYPE_COMMENT => 37500,
        };

        if (empty($model)) {
            return $this->failure(
                $errorCode,
                ConfigUtility::getCodeMessage($errorCode, 'Fresns', $langTag)
            );
        }

        $permissions = $model->permissions;

        $permissions['canDelete'] = $dtoWordBody->canDelete;

        $model->update([
            'permissions' => $permissions,
        ]);

        $typeName = match ($dtoWordBody->type) {
            Content::TYPE_POST => 'post',
            Content::TYPE_COMMENT => 'comment',
        };
        CacheHelper::clearDataCache($typeName, $dtoWordBody->fsid);

        return $this->success();
    }

    // setPostAuth
    public function setPostAuth($wordBody)
    {
        $dtoWordBody = new SetPostAuthDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        $postId = PrimaryHelper::fresnsPrimaryId('post', $dtoWordBody->pid);
        if (empty($postId)) {
            return $this->failure(
                37400,
                ConfigUtility::getCodeMessage(37400, 'Fresns', $langTag)
            );
        }

        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);
        $roleId = $dtoWordBody->rid;
        if (empty($userId) && empty($roleId)) {
            return $this->failure(
                30001,
                ConfigUtility::getCodeMessage(30001, 'Fresns', $langTag)
            );
        }

        switch ($dtoWordBody->type) {
            case 'add':
                // add
                if ($userId) {
                    PostAuth::updateOrCreate([
                        'post_id' => $postId,
                        'auth_type' => PostAuth::TYPE_USER,
                        'auth_id' => $userId,
                    ]);
                }
                if ($roleId) {
                    PostAuth::updateOrCreate([
                        'post_id' => $postId,
                        'auth_type' => PostAuth::TYPE_ROLE,
                        'auth_id' => $roleId,
                    ]);
                }
                break;

            case 'remove':
                // remove
                if ($userId) {
                    $userAuth = PostAuth::where('post_id', $postId)->where('auth_type', PostAuth::TYPE_USER)->where('auth_id', $userId)->first();
                    $userAuth->delete();
                }
                if ($roleId) {
                    $roleAuth = PostAuth::where('post_id', $postId)->where('auth_type', PostAuth::TYPE_ROLE)->where('auth_id', $userId)->first();
                    $roleAuth->delete();
                }
                break;
        }

        $cacheKey = "fresns_user_post_auth_{$postId}_{$userId}";
        $cacheTag = 'fresnsUsers';

        CacheHelper::forgetFresnsKey($cacheKey, $cacheTag);

        return $this->success();
    }

    // setPostAffiliateUser
    public function setPostAffiliateUser($wordBody)
    {
        $dtoWordBody = new SetPostAffiliateUserDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        $postId = PrimaryHelper::fresnsPrimaryId('post', $dtoWordBody->pid);
        if (empty($postId)) {
            return $this->failure(
                37400,
                ConfigUtility::getCodeMessage(37400, 'Fresns', $langTag)
            );
        }

        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);
        if (empty($userId)) {
            return $this->failure(
                35201,
                ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
            );
        }

        switch ($dtoWordBody->type) {
            case 'add':
                // add
                PostUser::updateOrCreate([
                    'post_id' => $postId,
                    'user_id' => $userId,
                ], [
                    'app_fskey' => $dtoWordBody->fskey,
                    'more_info' => $dtoWordBody->moreInfo ?? null,
                ]);
                break;

            case 'remove':
                // remove
                $postUser = PostUser::where('post_id', $postId)->where('user_id', $userId)->first();
                $postUser->delete();
                break;
        }

        return $this->success();
    }
}
