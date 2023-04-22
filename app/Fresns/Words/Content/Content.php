<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content;

use App\Fresns\Words\Content\DTO\ContentPublishByDraftDTO;
use App\Fresns\Words\Content\DTO\ContentQuickPublishDTO;
use App\Fresns\Words\Content\DTO\CreateDraftDTO;
use App\Fresns\Words\Content\DTO\GenerateDraftDTO;
use App\Fresns\Words\Content\DTO\LogicalDeletionContentDTO;
use App\Fresns\Words\Content\DTO\MapDTO;
use App\Fresns\Words\Content\DTO\PhysicalDeletionContentDTO;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\ArchiveUsage;
use App\Models\Comment;
use App\Models\CommentAppend;
use App\Models\CommentLog;
use App\Models\DomainLinkUsage;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\HashtagUsage;
use App\Models\Language;
use App\Models\Mention;
use App\Models\OperationUsage;
use App\Models\Post;
use App\Models\PostAllow;
use App\Models\PostAppend;
use App\Models\PostLog;
use App\Models\PostUser;
use App\Utilities\ConfigUtility;
use App\Utilities\ContentUtility;
use App\Utilities\InteractionUtility;
use App\Utilities\PermissionUtility;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Str;

class Content
{
    use CmdWordResponseTrait;

    // createDraft
    public function createDraft($wordBody)
    {
        $dtoWordBody = new CreateDraftDTO($wordBody);
        $langTag = \request()->header('X-Fresns-Client-Lang-Tag', ConfigHelper::fresnsConfigDefaultLangTag());

        $creator = PrimaryHelper::fresnsModelByFsid('user', $dtoWordBody->uid);
        if (! $creator) {
            return $this->failure(
                35201,
                ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
            );
        }
        if (! $creator->is_enable) {
            return $this->failure(
                35202,
                ConfigUtility::getCodeMessage(35202, 'Fresns', $langTag)
            );
        }

        $isPluginEditor = 0;
        $editorUnikey = null;
        if ($dtoWordBody->editorUnikey) {
            $isPluginEditor = 1;
            $editorUnikey = $dtoWordBody->editorUnikey;
        }

        $content = null;
        if ($dtoWordBody->content) {
            $content = Str::of($dtoWordBody->content)->trim();
        }
        $isMarkdown = $dtoWordBody->isMarkdown ?? 0;
        $isAnonymous = $dtoWordBody->isAnonymous ?? 0;

        switch ($dtoWordBody->type) {
            case 1:
                // post
                $groupId = PrimaryHelper::fresnsGroupIdByGid($dtoWordBody->postGid);

                $title = null;
                if ($dtoWordBody->postTitle) {
                    $title = Str::of($dtoWordBody->postTitle)->trim();
                }

                $checkLog = PostLog::with(['fileUsages', 'extendUsages'])->where('user_id', $creator->id)->where('create_type', 1)->where('state', 1)->first();

                $logData = [
                    'user_id' => $creator->id,
                    'parent_post_id' => PrimaryHelper::fresnsPostIdByPid($dtoWordBody->postQuotePid),
                    'create_type' => $dtoWordBody->createType,
                    'is_plugin_editor' => $isPluginEditor,
                    'editor_unikey' => $editorUnikey,
                    'group_id' => $groupId ?? 0,
                    'title' => $title,
                    'content' => $content,
                    'is_markdown' => $isMarkdown,
                    'is_anonymous' => $isAnonymous,
                    'map_json' => $dtoWordBody->map ?? null,
                ];

                if (empty($checkLog)) {
                    $logModel = PostLog::create($logData);
                } else {
                    if (empty($checkLog->content) && empty($checkLog->files) && empty($checkLog->extends)) {
                        $checkLog->update($logData);
                        $logModel = $checkLog;
                    } else {
                        $logModel = PostLog::create($logData);
                    }
                }
                break;

            case 2:
                // comment
                $checkPost = Post::where('pid', $dtoWordBody->commentPid)->first();
                if (empty($checkPost)) {
                    return $this->failure(
                        37300,
                        ConfigUtility::getCodeMessage(37300, 'Fresns', $langTag)
                    );
                }

                $checkLog = CommentLog::with(['fileUsages', 'extendUsages'])->where('user_id', $creator->id)->where('create_type', 1)->where('state', 1)->first();

                $logData = [
                    'user_id' => $creator->id,
                    'post_id' => $checkPost->id,
                    'parent_comment_id' => PrimaryHelper::fresnsCommentIdByCid($checkPost->commentCid),
                    'create_type' => $dtoWordBody->createType,
                    'is_plugin_editor' => $isPluginEditor,
                    'editor_unikey' => $editorUnikey,
                    'content' => $content,
                    'is_markdown' => $isMarkdown,
                    'is_anonymous' => $isAnonymous,
                    'map_json' => $dtoWordBody->map ?? null,
                ];

                if (empty($checkLog)) {
                    $logModel = CommentLog::create($logData);
                } else {
                    if ($checkLog->post_id == $checkPost->id) {
                        $checkLog->update($logData);
                        $logModel = $checkLog;
                    } else {
                        if (empty($checkLog->content) && empty($checkLog->files) && empty($checkLog->extends)) {
                            $checkLog->update($logData);
                            $logModel = $checkLog;
                        } else {
                            $logModel = CommentLog::create($logData);
                        }
                    }
                }
                break;
        }

        // extends
        if ($dtoWordBody->extends) {
            $usageType = match ($dtoWordBody->type) {
                1 => ExtendUsage::TYPE_POST_LOG,
                2 => ExtendUsage::TYPE_COMMENT_LOG,
            };

            ContentUtility::saveExtendUsages($usageType, $logModel->id, $dtoWordBody->extends);
        }

        // archives
        if ($dtoWordBody->archives) {
            $usageType = match ($dtoWordBody->type) {
                1 => ArchiveUsage::TYPE_POST_LOG,
                2 => ArchiveUsage::TYPE_COMMENT_LOG,
            };

            ContentUtility::saveArchiveUsages($usageType, $logModel->id, $dtoWordBody->archives);
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

        $langTag = \request()->header('X-Fresns-Client-Lang-Tag', ConfigHelper::fresnsConfigDefaultLangTag());
        $timezone = \request()->header('X-Fresns-Client-Timezone', ConfigHelper::fresnsConfigDefaultTimezone());

        $editTimeLimit = match ($dtoWordBody->type) {
            1 => ConfigHelper::fresnsConfigByItemKey('post_edit_time_limit'),
            2 => ConfigHelper::fresnsConfigByItemKey('comment_edit_time_limit'),
        };

        switch ($dtoWordBody->type) {
            case 1:
                // post
                $post = PrimaryHelper::fresnsModelByFsid('post', $dtoWordBody->fsid);

                $creator = PrimaryHelper::fresnsModelById('user', $post->user_id);
                if (! $creator) {
                    return $this->failure(
                        35201,
                        ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
                    );
                }
                if (! $creator->is_enable) {
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

            case 2:
                // comment
                $comment = PrimaryHelper::fresnsModelByFsid('comment', $dtoWordBody->fsid);

                $creator = PrimaryHelper::fresnsModelById('user', $comment->user_id);
                if (! $creator) {
                    return $this->failure(
                        35201,
                        ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
                    );
                }
                if (! $creator->is_enable) {
                    return $this->failure(
                        35202,
                        ConfigUtility::getCodeMessage(35202, 'Fresns', $langTag)
                    );
                }

                if ($comment->top_parent_id != 0) {
                    return $this->failure(
                        36313,
                        ConfigUtility::getCodeMessage(36313, 'Fresns', $langTag)
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
            1 => PostLog::where('id', $dtoWordBody->logId)->first(),
            2 => CommentLog::where('id', $dtoWordBody->logId)->first(),
        };

        $langTag = \request()->header('X-Fresns-Client-Lang-Tag', ConfigHelper::fresnsConfigDefaultLangTag());

        if (empty($logModel)) {
            return $this->failure(
                38100,
                ConfigUtility::getCodeMessage(38100, 'Fresns', $langTag),
            );
        }

        if ($logModel->state == 2) {
            return $this->failure(
                38103,
                ConfigUtility::getCodeMessage(38103, 'Fresns', $langTag),
            );
        }

        if ($logModel->state == 3) {
            return $this->failure(
                38104,
                ConfigUtility::getCodeMessage(38104, 'Fresns', $langTag),
            );
        }

        $creator = PrimaryHelper::fresnsModelById('user', $logModel->user_id);
        if (! $creator) {
            return $this->failure(
                35201,
                ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
            );
        }
        if (! $creator->is_enable) {
            return $this->failure(
                35202,
                ConfigUtility::getCodeMessage(35202, 'Fresns', $langTag)
            );
        }

        switch ($dtoWordBody->type) {
            case 1:
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

            case 2:
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
        $langTag = \request()->header('X-Fresns-Client-Lang-Tag', ConfigHelper::fresnsConfigDefaultLangTag());

        $creator = PrimaryHelper::fresnsModelByFsid('user', $dtoWordBody->uid);
        if (! $creator) {
            return $this->failure(
                35201,
                ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
            );
        }
        if (! $creator->is_enable) {
            return $this->failure(
                35202,
                ConfigUtility::getCodeMessage(35202, 'Fresns', $langTag)
            );
        }

        $type = match ($dtoWordBody->type) {
            1 => 'post',
            2 => 'comment',
        };

        // map
        if ($dtoWordBody->map) {
            new MapDTO($dtoWordBody->map);
        }

        // review
        if ($dtoWordBody->requireReview) {
            $wordBody['createType'] = 1;
            $wordBody['editorUnikey'] = null;

            $reviewResp = \FresnsCmdWord::plugin('Fresns')->createDraft($wordBody);

            if ($reviewResp->isErrorResponse()) {
                return $reviewResp->errorResponse();
            }

            switch ($type) {
                case 'post':
                    PostLog::where('id', $reviewResp->getData('logId'))->update([
                        'state' => 2,
                        'submit_at' => now(),
                    ]);

                    $usageType = ExtendUsage::TYPE_POST_LOG;
                    break;

                case 'comment':
                    CommentLog::where('id', $reviewResp->getData('logId'))->update([
                        'state' => 2,
                        'submit_at' => now(),
                    ]);

                    $usageType = ExtendUsage::TYPE_COMMENT_LOG;
                    break;
            }

            $logId = $reviewResp->getData('logId');

            // extends
            if ($dtoWordBody->extends) {
                ContentUtility::saveExtendUsages($usageType, $logId, $dtoWordBody->extends);
            }

            // archives
            if ($dtoWordBody->archives) {
                ContentUtility::saveArchiveUsages($usageType, $logId, $dtoWordBody->archives);
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

        switch ($type) {
            // post
            case 'post':
                $post = Post::create([
                    'user_id' => $creator->id,
                    'parent_id' => PrimaryHelper::fresnsPostIdByPid($dtoWordBody->postQuotePid) ?? 0,
                    'group_id' => PrimaryHelper::fresnsGroupIdByGid($dtoWordBody->postGid) ?? 0,
                    'title' => $dtoWordBody->postTitle ? Str::of($dtoWordBody->postTitle)->trim() : null,
                    'content' => $dtoWordBody->content ? Str::of($dtoWordBody->content)->trim() : null,
                    'is_markdown' => $dtoWordBody->isMarkdown ?? 0,
                    'is_anonymous' => $dtoWordBody->isAnonymous ?? 0,
                    'map_longitude' => $dtoWordBody->map['latitude'] ?? null,
                    'map_latitude' => $dtoWordBody->map['longitude'] ?? null,
                ]);

                PostAppend::create([
                    'post_id' => $post->id,
                    'is_comment' => $dtoWordBody->postIsComment ?? 1,
                    'is_comment_public' => $dtoWordBody->postIsCommentPublic ?? 1,
                    'map_id' => $dtoWordBody->map['mapId'] ?? null,
                    'map_json' => $dtoWordBody->map ?? null,
                    'map_continent_code' => $dtoWordBody->map['continentCode'] ?? null,
                    'map_country_code' => $dtoWordBody->map['countryCode'] ?? null,
                    'map_region_code' => $dtoWordBody->map['regionCode'] ?? null,
                    'map_city_code' => $dtoWordBody->map['cityCode'] ?? null,
                    'map_zip' => $dtoWordBody->map['zip'] ?? null,
                    'map_poi_id' => $dtoWordBody->map['poiId'] ?? null,
                ]);

                ContentUtility::handleAndSaveAllInteraction($post->content, Mention::TYPE_POST, $post->id, $post->user_id);
                InteractionUtility::publishStats('post', $post->id, 'increment');

                $creator->update([
                    'last_post_at' => now(),
                ]);

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

                $comment = Comment::create([
                    'user_id' => $creator->id,
                    'post_id' => $post->id,
                    'top_parent_id' => $commentTopParentId,
                    'parent_id' => $parentComment?->id ?? 0,
                    'content' => $dtoWordBody->content ? Str::of($dtoWordBody->content)->trim() : null,
                    'is_markdown' => $dtoWordBody->isMarkdown ?? 0,
                    'is_anonymous' => $dtoWordBody->isAnonymous ?? 0,
                    'map_longitude' => $dtoWordBody->map['latitude'] ?? null,
                    'map_latitude' => $dtoWordBody->map['longitude'] ?? null,
                ]);

                CommentAppend::create([
                    'comment_id' => $comment->id,
                    'map_id' => $dtoWordBody->map['mapId'] ?? null,
                    'map_json' => $dtoWordBody->map ?? null,
                    'map_continent_code' => $dtoWordBody->map['continentCode'] ?? null,
                    'map_country_code' => $dtoWordBody->map['countryCode'] ?? null,
                    'map_region_code' => $dtoWordBody->map['regionCode'] ?? null,
                    'map_city_code' => $dtoWordBody->map['cityCode'] ?? null,
                    'map_zip' => $dtoWordBody->map['zip'] ?? null,
                    'map_poi_id' => $dtoWordBody->map['poiId'] ?? null,
                ]);

                ContentUtility::handleAndSaveAllInteraction($comment->content, Mention::TYPE_COMMENT, $comment->id, $comment->user_id);
                InteractionUtility::publishStats('comment', $comment->id, 'increment');

                $creator->update([
                    'last_comment_at' => now(),
                ]);

                $post->update([
                    'latest_comment_at' => now(),
                ]);

                if ($comment->parent_id) {
                    ContentUtility::parentCommentLatestCommentTime($comment->parent_id);
                }

                $usageType = ExtendUsage::TYPE_COMMENT;

                $primaryId = $comment->id;
                $fsid = $comment->cid;
                break;
        }

        // extends
        if ($dtoWordBody->extends) {
            ContentUtility::saveExtendUsages($usageType, $primaryId, $dtoWordBody->extends);
        }

        // archives
        if ($dtoWordBody->archives) {
            ContentUtility::saveArchiveUsages($usageType, $primaryId, $dtoWordBody->archives);
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

        switch ($dtoWordBody->contentType) {
            case 1:
                // main
                $model = match ($dtoWordBody->type) {
                    1 => Post::where('pid', $dtoWordBody->contentFsid)->first(),
                    2 => Comment::where('cid', $dtoWordBody->contentFsid)->first(),
                };

                $modelAppend = match ($dtoWordBody->type) {
                    1 => PostAppend::where('post_id', $model->id)->first(),
                    2 => CommentAppend::where('comment_id', $model->id)->first(),
                };

                $type = match ($dtoWordBody->type) {
                    1 => 'post',
                    2 => 'comment',
                };

                InteractionUtility::publishStats($type, $model->id, 'decrement');

                if ($dtoWordBody->type == 1) {
                    PostAllow::where('post_id', $model->id)->delete();
                    PostUser::where('post_id', $model->id)->delete();
                    Language::where('table_name', 'post_appends')->where('table_column', 'allow_btn_name')->where('table_id', $model->id)->delete();
                    Language::where('table_name', 'post_appends')->where('table_column', 'user_list_name')->where('table_id', $model->id)->delete();
                    Language::where('table_name', 'post_appends')->where('table_column', 'comment_btn_name')->where('table_id', $model->id)->delete();
                }

                $tableName = match ($dtoWordBody->type) {
                    1 => 'posts',
                    2 => 'comments',
                };

                $usageType = match ($dtoWordBody->type) {
                    1 => OperationUsage::TYPE_POST,
                    2 => OperationUsage::TYPE_COMMENT,
                };
                break;

            case 2:
                // log
                $model = match ($dtoWordBody->type) {
                    1 => PostLog::where('id', $dtoWordBody->contentLogId)->first(),
                    2 => CommentLog::where('id', $dtoWordBody->contentLogId)->first(),
                };

                $tableName = match ($dtoWordBody->type) {
                    1 => 'post_logs',
                    2 => 'comment_logs',
                };

                $usageType = match ($dtoWordBody->type) {
                    1 => OperationUsage::TYPE_POST_LOG,
                    2 => OperationUsage::TYPE_COMMENT_LOG,
                };
                break;
        }

        FileUsage::where('table_name', $tableName)->where('table_column', 'id')->where('table_id', $model->id)->delete();
        OperationUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->delete();
        ArchiveUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->delete();
        ExtendUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->delete();

        HashtagUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->delete();
        DomainLinkUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->delete();
        Mention::where('user_id', $model->user_id)->where('mention_type', $usageType)->where('mention_id', $model->id)->delete();

        $modelAppend->delete();
        $model->delete();

        return $this->success();
    }

    // physicalDeletionContent
    public function physicalDeletionContent($wordBody)
    {
        $dtoWordBody = new PhysicalDeletionContentDTO($wordBody);

        switch ($dtoWordBody->contentType) {
            case 1:
                // main
                $model = match ($dtoWordBody->type) {
                    1 => Post::where('pid', $dtoWordBody->contentFsid)->first(),
                    2 => Comment::where('cid', $dtoWordBody->contentFsid)->first(),
                };

                $modelAppend = match ($dtoWordBody->type) {
                    1 => PostAppend::where('post_id', $model->id)->first(),
                    2 => CommentAppend::where('comment_id', $model->id)->first(),
                };

                $type = match ($dtoWordBody->type) {
                    1 => 'post',
                    2 => 'comment',
                };

                InteractionUtility::publishStats($type, $model->id, 'decrement');

                if ($dtoWordBody->type == 1) {
                    PostAllow::where('post_id', $model->id)->forceDelete();
                    PostUser::where('post_id', $model->id)->forceDelete();
                    Language::where('table_name', 'post_appends')->where('table_column', 'allow_btn_name')->where('table_id', $model->id)->forceDelete();
                    Language::where('table_name', 'post_appends')->where('table_column', 'user_list_name')->where('table_id', $model->id)->forceDelete();
                    Language::where('table_name', 'post_appends')->where('table_column', 'comment_btn_name')->where('table_id', $model->id)->forceDelete();
                }

                $tableName = match ($dtoWordBody->type) {
                    1 => 'posts',
                    2 => 'comments',
                };

                $usageType = match ($dtoWordBody->type) {
                    1 => OperationUsage::TYPE_POST,
                    2 => OperationUsage::TYPE_COMMENT,
                };
                break;

            case 2:
                // log
                $model = match ($dtoWordBody->type) {
                    1 => PostLog::where('id', $dtoWordBody->contentLogId)->first(),
                    2 => CommentLog::where('id', $dtoWordBody->contentLogId)->first(),
                };

                $tableName = match ($dtoWordBody->type) {
                    1 => 'post_logs',
                    2 => 'comment_logs',
                };

                $usageType = match ($dtoWordBody->type) {
                    1 => OperationUsage::TYPE_POST_LOG,
                    2 => OperationUsage::TYPE_COMMENT_LOG,
                };
                break;
        }

        $fileIds = FileUsage::where('table_name', $tableName)->where('table_column', 'id')->where('table_id', $model->id)->pluck('file_id')->toArray();
        FileUsage::where('table_name', $tableName)->where('table_column', 'id')->where('table_id', $model->id)->forceDelete();

        OperationUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->forceDelete();
        ArchiveUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->forceDelete();
        ExtendUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->forceDelete();

        HashtagUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->forceDelete();
        DomainLinkUsage::where('usage_type', $usageType)->where('usage_id', $model->id)->forceDelete();
        Mention::where('user_id', $model->user_id)->where('mention_type', $usageType)->where('mention_id', $model->id)->forceDelete();

        $modelAppend->forceDelete();
        $model->forceDelete();

        $fileList = File::doesntHave('fileUsages')->whereIn('id', $fileIds)->get()->groupBy('type');

        $files[File::TYPE_IMAGE] = $fileList->get(File::TYPE_IMAGE)?->pluck('id')?->all() ?? null;
        $files[File::TYPE_IMAGE] = $fileList->get(File::TYPE_VIDEO)?->pluck('id')?->all() ?? null;
        $files[File::TYPE_IMAGE] = $fileList->get(File::TYPE_AUDIO)?->pluck('id')?->all() ?? null;
        $files[File::TYPE_IMAGE] = $fileList->get(File::TYPE_DOCUMENT)?->pluck('id')?->all() ?? null;

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
}
