<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content;

use App\Fresns\Words\Content\DTO\ContentPublishByDraftDTO;
use App\Fresns\Words\Content\DTO\CreateDraftDTO;
use App\Fresns\Words\Content\DTO\GenerateDraftDTO;
use App\Fresns\Words\Content\DTO\LogicalDeletionContentDTO;
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
use App\Utilities\InteractiveUtility;
use App\Utilities\PermissionUtility;
use App\Utilities\ValidationUtility;
use Carbon\Carbon;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Str;

class Content
{
    use CmdWordResponseTrait;

    // createDraft
    public function createDraft($wordBody)
    {
        $dtoWordBody = new CreateDraftDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $authUser = PrimaryHelper::fresnsModelByFsid('user', $dtoWordBody->uid);
        if (! $authUser) {
            return $this->failure(
                35201,
                ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
            );
        }
        if ($authUser->is_enable == 0) {
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
            // post
            case 1:
                $groupId = PrimaryHelper::fresnsGroupIdByGid($dtoWordBody->postGid);

                $title = null;
                if ($dtoWordBody->postTitle) {
                    $title = Str::of($dtoWordBody->postTitle)->trim();
                }

                $checkLog = PostLog::with(['files', 'extends'])->where('user_id', $authUser->id)->where('create_type', 1)->where('state', 1)->first();

                $logData = [
                    'user_id' => $authUser->id,
                    'create_type' => $dtoWordBody->createType,
                    'is_plugin_editor' => $isPluginEditor,
                    'editor_unikey' => $editorUnikey,
                    'group_id' => $groupId,
                    'title' => $title,
                    'content' => $content,
                    'is_markdown' => $isMarkdown,
                    'is_anonymous' => $isAnonymous,
                    'map_json' => $dtoWordBody->mapJson ?? null,
                ];

                if (! $checkLog) {
                    $logModel = PostLog::create($logData);
                } else if (! $checkLog->content && ! $checkLog->files && ! $checkLog->extends) {
                    $logModel = $checkLog->update($logData);
                } else {
                    $logModel = PostLog::create($logData);
                }
            break;

            // comment
            case 2:
                $postId = PrimaryHelper::fresnsPostIdByPid($dtoWordBody->pid);

                if (empty($postId)) {
                    return $this->failure(
                        37300,
                        ConfigUtility::getCodeMessage(37300, 'Fresns', $langTag)
                    );
                }

                $checkLog = CommentLog::with(['files', 'extends'])->where('user_id', $authUser->id)->where('create_type', 1)->where('state', 1)->first();

                $logData = [
                    'user_id' => $authUser->id,
                    'create_type' => $dtoWordBody->createType,
                    'is_plugin_editor' => $isPluginEditor,
                    'editor_unikey' => $editorUnikey,
                    'content' => $content,
                    'is_markdown' => $isMarkdown,
                    'is_anonymous' => $isAnonymous,
                    'map_json' => $dtoWordBody->mapJson ?? null,
                ];

                if (! $checkLog) {
                    $logModel = CommentLog::create($logData);
                }

                if (! $checkLog->content && ! $checkLog->files && ! $checkLog->extends) {
                    $logModel = $checkLog->update($logData);
                } else {
                    $logModel = CommentLog::create($logData);
                }
            break;
        }

        if ($dtoWordBody->eid) {
            $extendId = PrimaryHelper::fresnsExtendIdByEid($dtoWordBody->eid);

            if ($extendId) {
                $usageType = match ($dtoWordBody->type) {
                    1 => ExtendUsage::TYPE_POST_LOG,
                    2 => ExtendUsage::TYPE_COMMENT_LOG,
                };

                ExtendUsage::create([
                    'usage_type' => $usageType,
                    'usage_id' => $logModel->id,
                    'extend_id' => $extendId,
                    'plugin_unikey' => 'Fresns',
                ]);
            }
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

        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());
        $timezone = \request()->header('timezone', ConfigHelper::fresnsConfigDefaultTimezone());

        switch ($dtoWordBody->type) {
            // post
            case 1:
                $post = PrimaryHelper::fresnsModelByFsid('post', $dtoWordBody->fsid);

                $creator = PrimaryHelper::fresnsModelById('user', $post->user_id);
                if (! $creator) {
                    return $this->failure(
                        35201,
                        ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
                    );
                }
                if ($creator->is_enable == 0) {
                    return $this->failure(
                        35202,
                        ConfigUtility::getCodeMessage(35202, 'Fresns', $langTag)
                    );
                }

                $editConfig = ConfigHelper::fresnsConfigByItemKeys([
                    'post_edit',
                    'post_edit_time_limit',
                    'post_edit_sticky_limit',
                    'post_edit_digest_limit',
                ]);

                if (! $editConfig['post_edit']) {
                    return $this->failure(
                        36305,
                        ConfigUtility::getCodeMessage(36305, 'Fresns', $langTag)
                    );
                }

                $timeDiff = Carbon::parse($post->created_at)->diffInMinutes(now());

                if ($timeDiff > $editConfig['post_edit_time_limit']) {
                    return $this->failure(
                        36309,
                        ConfigUtility::getCodeMessage(36309, 'Fresns', $langTag)
                    );
                }

                if (! $editConfig['post_edit_sticky_limit'] && $post->sticky_state != 1) {
                    return $this->failure(
                        36307,
                        ConfigUtility::getCodeMessage(36307, 'Fresns', $langTag)
                    );
                }

                if (! $editConfig['post_edit_digest_limit'] && $post->digest_state != 1) {
                    return $this->failure(
                        36308,
                        ConfigUtility::getCodeMessage(36308, 'Fresns', $langTag)
                    );
                }

                $checkContentEditPerm = PermissionUtility::checkContentEditPerm($post->created_at, $editConfig['post_edit_time_limit'], $timezone, $langTag);
                $editableStatus = $checkContentEditPerm['editableStatus'];
                $editableTime = $checkContentEditPerm['editableTime'];
                $deadlineTime = $checkContentEditPerm['deadlineTime'];

                $logModel = ContentUtility::generatePostDraft($post);
            break;

            // comment
            case 2:
                $comment = PrimaryHelper::fresnsModelByFsid('comment', $dtoWordBody->fsid);

                $creator = PrimaryHelper::fresnsModelById('user', $comment->user_id);
                if (! $creator) {
                    return $this->failure(
                        35201,
                        ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
                    );
                }
                if ($creator->is_enable == 0) {
                    return $this->failure(
                        35202,
                        ConfigUtility::getCodeMessage(35202, 'Fresns', $langTag)
                    );
                }

                if (! empty($comment->top_parent_id) || $comment->top_parent_id == 0) {
                    return $this->failure(
                        36313,
                        ConfigUtility::getCodeMessage(36313, 'Fresns', $langTag)
                    );
                }

                $editConfig = ConfigHelper::fresnsConfigByItemKeys([
                    'comment_edit',
                    'comment_edit_time_limit',
                    'comment_edit_sticky_limit',
                    'comment_edit_digest_limit',
                ]);

                if (! $editConfig['comment_edit']) {
                    return $this->failure(
                        36306,
                        ConfigUtility::getCodeMessage(36306, 'Fresns', $langTag)
                    );
                }

                $timeDiff = Carbon::parse($comment->created_at)->diffInMinutes(now());

                if ($timeDiff > $editConfig['comment_edit_time_limit']) {
                    return $this->failure(
                        36309,
                        ConfigUtility::getCodeMessage(36309, 'Fresns', $langTag)
                    );
                }

                if (! $editConfig['comment_edit_sticky_limit'] && $comment->sticky_state != 1) {
                    return $this->failure(
                        36307,
                        ConfigUtility::getCodeMessage(36307, 'Fresns', $langTag)
                    );
                }

                if (! $editConfig['comment_edit_digest_limit'] && $comment->digest_state != 1) {
                    return $this->failure(
                        36308,
                        ConfigUtility::getCodeMessage(36308, 'Fresns', $langTag)
                    );
                }

                $checkContentEditPerm = PermissionUtility::checkContentEditPerm($comment->created_at, $editConfig['comment_edit_time_limit'], $timezone, $langTag);
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

        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        if (empty($logModel)) {
            return $this->failure([
                38100,
                ConfigUtility::getCodeMessage(38100, 'Fresns', $langTag),
            ]);
        }

        if ($logModel->state == 2) {
            return $this->failure([
                38103,
                ConfigUtility::getCodeMessage(38103, 'Fresns', $langTag),
            ]);
        }

        if ($logModel->state == 3) {
            return $this->failure([
                38104,
                ConfigUtility::getCodeMessage(38104, 'Fresns', $langTag),
            ]);
        }

        $creator = PrimaryHelper::fresnsModelById('user', $logModel->user_id);
        if (! $creator) {
            return $this->failure(
                35201,
                ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
            );
        }
        if ($creator->is_enable == 0) {
            return $this->failure(
                35202,
                ConfigUtility::getCodeMessage(35202, 'Fresns', $langTag)
            );
        }

        switch ($dtoWordBody->type) {
            // post
            case 1:
                $post = ContentUtility::releasePost($logModel);

                $primaryId = $post->id;
                $fsid = $post->pid;
            break;

            // comment
            case 2:
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

    // contentDirectPublish
    public function contentDirectPublish($wordBody)
    {
        $wordBody['createType'] = 1;
        $wordBody['editorUnikey'] = null;

        $dtoWordBody = new CreateDraftDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $authUser = PrimaryHelper::fresnsModelByFsid('user', $dtoWordBody->uid);
        if (! $authUser) {
            return $this->failure(
                35201,
                ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
            );
        }
        if ($authUser->is_enable == 0) {
            return $this->failure(
                35202,
                ConfigUtility::getCodeMessage(35202, 'Fresns', $langTag)
            );
        }

        $type = match ($dtoWordBody->type) {
            1 => 'post',
            2 => 'comment',
        };

        $editorConfig = ConfigHelper::fresnsConfigByItemKeys([
            "{$type}_editor_title_required",
            "{$type}_editor_title_length",
            "{$type}_editor_group_required",
            "{$type}_editor_content_length",
            'content_review_service',
        ]);

        if ($dtoWordBody->content) {
            $content = Str::of($dtoWordBody->content)->trim();

            $contentLength = Str::length($content);
            if ($contentLength > $editorConfig["{$type}_editor_content_length"]) {
                return $this->failure(
                    38205,
                    ConfigUtility::getCodeMessage(38205, 'Fresns', $langTag)
                );
            }

            $checkBanWords = ValidationUtility::contentBanWords($content);
            if (! $checkBanWords) {
                return $this->failure(
                    38207,
                    ConfigUtility::getCodeMessage(38207, 'Fresns', $langTag)
                );
            }
        } else {
            return $this->failure(
                38204,
                ConfigUtility::getCodeMessage(38204, 'Fresns', $langTag)
            );
        }

        switch ($type) {
            // post
            case 'post':
                if ($editorConfig['post_editor_group_required'] && ! $dtoWordBody->postGid) {
                    return $this->failure(
                        38208,
                        ConfigUtility::getCodeMessage(38208, 'Fresns', $langTag)
                    );
                }

                if ($editorConfig['post_editor_title_required'] && ! $dtoWordBody->postTitle) {
                    return $this->failure(
                        38202,
                        ConfigUtility::getCodeMessage(38202, 'Fresns', $langTag)
                    );
                }

                $group = PrimaryHelper::fresnsModelByFsid('group', $dtoWordBody->postGid);

                $title = null;
                if ($dtoWordBody->postTitle) {
                    $title = Str::of($dtoWordBody->postTitle)->trim();

                    $titleLength = Str::length($title);
                    if ($titleLength > $editorConfig['post_editor_title_length']) {
                        return $this->failure(
                            38203,
                            ConfigUtility::getCodeMessage(38203, 'Fresns', $langTag)
                        );
                    }

                    $checkTitleBanWords = ValidationUtility::contentBanWords($title);
                    if (! $checkTitleBanWords) {
                        return $this->failure(
                            38206,
                            ConfigUtility::getCodeMessage(38206, 'Fresns', $langTag)
                        );
                    }
                }
            break;

            // comment
            case 'comment':
                $post = PrimaryHelper::fresnsModelByFsid('post', $dtoWordBody->commentPid);
                $group = PrimaryHelper::fresnsModelById('group', $post->group_id);
                $parentComment = PrimaryHelper::fresnsModelByFsid('comment', $dtoWordBody->commentCid);

                $topParentId = null;
                if (! $parentComment) {
                    $topParentId = $parentComment?->top_parent_id ?? null;
                }
            break;
        }

        if ($group) {
            $checkGroupPublishPerm = PermissionUtility::checkUserGroupPublishPerm($group->id, $group->permissions, $authUser->id);
        } else {
            $checkGroupPublishPerm = [
                'allowPost' => true,
                'reviewPost' => false,
                'allowComment' => true,
                'reviewComment' => false,
            ];
        }

        switch ($type) {
            // post
            case 'post':
                if (! $checkGroupPublishPerm['allowPost']) {
                    return $this->failure(
                        36311,
                        ConfigUtility::getCodeMessage(36311, 'Fresns', $langTag)
                    );
                }

                $checkReview = ValidationUtility::contentReviewWords($content);

                if ($checkGroupPublishPerm['reviewComment'] || ! $checkReview) {
                    $reviewResp = \FresnsCmdWord::plugin('Fresns')->createDraft($wordBody);

                    if ($reviewResp->isErrorResponse()) {
                        return $reviewResp->errorResponse();
                    }

                    PostLog::where('id', $reviewResp->getData('logId'))->update([
                        'state' => 2,
                        'submit_at' => now(),
                    ]);

                    return $reviewResp->getOrigin();
                }

                $post = Post::create([
                    'user_id' => $authUser->id,
                    'group_id' => $group?->id ?? null,
                    'title' => $title,
                    'content' => $content,
                    'is_markdown' => $dtoWordBody->isMarkdown ?? 0,
                    'is_anonymous' => $dtoWordBody->isAnonymous ?? 0,
                    'map_id' => $dtoWordBody->mapJson['mapId'] ?? null,
                    'map_longitude' => $dtoWordBody->mapJson['latitude'] ?? null,
                    'map_latitude' => $dtoWordBody->mapJson['longitude'] ?? null,
                ]);

                PostAppend::create([
                    'post_id' => $post->id,
                    'is_comment' => $dtoWordBody->postIsComment ?? 1,
                    'is_comment_public' => $dtoWordBody->postIsCommentPublic ?? 1,
                    'map_json' => $dtoWordBody->mapJson ?? null,
                    'map_scale' => $dtoWordBody->mapJson['scale'] ?? null,
                    'map_continent_code' => $dtoWordBody->mapJson['continentCode'] ?? null,
                    'map_country_code' => $dtoWordBody->mapJson['countryCode'] ?? null,
                    'map_region_code' => $dtoWordBody->mapJson['regionCode'] ?? null,
                    'map_city_code' => $dtoWordBody->mapJson['cityCode'] ?? null,
                    'map_city' => $dtoWordBody->mapJson['city'] ?? null,
                    'map_zip' => $dtoWordBody->mapJson['zip'] ?? null,
                    'map_poi' => $dtoWordBody->mapJson['poi'] ?? null,
                    'map_poi_id' => $dtoWordBody->mapJson['poiId'] ?? null,
                ]);

                $primaryId = $post->id;
                $fsid = $post->pid;
            break;

            // comment
            case 'comment':
                if (! $checkGroupPublishPerm['allowComment']) {
                    return $this->failure(
                        36312,
                        ConfigUtility::getCodeMessage(36312, 'Fresns', $langTag)
                    );
                }

                $checkReview = ValidationUtility::contentReviewWords($content);

                if ($checkGroupPublishPerm['reviewComment'] || ! $checkReview) {
                    $reviewResp = \FresnsCmdWord::plugin('Fresns')->createDraft($wordBody);

                    if ($reviewResp->isErrorResponse()) {
                        return $reviewResp->errorResponse();
                    }

                    CommentLog::where('id', $reviewResp->getData('logId'))->update([
                        'state' => 2,
                        'submit_at' => now(),
                    ]);

                    return $reviewResp->getOrigin();
                }

                $comment = Comment::create([
                    'user_id' => $authUser->id,
                    'post_id' => $post->id,
                    'top_parent_id' => $topParentId,
                    'parent_id' => $parentComment?->id ?? null,
                    'content' => $content,
                    'is_markdown' => $dtoWordBody->isMarkdown ?? 0,
                    'is_anonymous' => $dtoWordBody->isAnonymous ?? 0,
                    'map_id' => $dtoWordBody->mapJson['mapId'] ?? null,
                    'map_longitude' => $dtoWordBody->mapJson['latitude'] ?? null,
                    'map_latitude' => $dtoWordBody->mapJson['longitude'] ?? null,
                ]);

                CommentAppend::create([
                    'comment_id' => $comment->id,
                    'map_json' => $dtoWordBody->mapJson ?? null,
                    'map_scale' => $dtoWordBody->mapJson['scale'] ?? null,
                    'map_continent_code' => $dtoWordBody->mapJson['continentCode'] ?? null,
                    'map_country_code' => $dtoWordBody->mapJson['countryCode'] ?? null,
                    'map_region_code' => $dtoWordBody->mapJson['regionCode'] ?? null,
                    'map_city_code' => $dtoWordBody->mapJson['cityCode'] ?? null,
                    'map_city' => $dtoWordBody->mapJson['city'] ?? null,
                    'map_zip' => $dtoWordBody->mapJson['zip'] ?? null,
                    'map_poi' => $dtoWordBody->mapJson['poi'] ?? null,
                    'map_poi_id' => $dtoWordBody->mapJson['poiId'] ?? null,
                ]);

                $primaryId = $comment->id;
                $fsid = $comment->cid;
            break;
        }

        if ($dtoWordBody->eid) {
            $extendId = PrimaryHelper::fresnsExtendIdByEid($dtoWordBody->eid);

            if ($extendId) {
                $usageType = match ($dtoWordBody->type) {
                    1 => ExtendUsage::TYPE_POST,
                    2 => ExtendUsage::TYPE_COMMENT,
                };

                ExtendUsage::create([
                    'usage_type' => $usageType,
                    'usage_id' => $primaryId,
                    'extend_id' => $extendId,
                    'plugin_unikey' => 'Fresns',
                ]);
            }
        }

        return $this->success([
            'type' => $dtoWordBody->type,
            'id' => $primaryId,
            'fsid' => $fsid,
        ]);
    }

    // logicalDeletionContent
    public function logicalDeletionContent($wordBody)
    {
        $dtoWordBody = new LogicalDeletionContentDTO($wordBody);

        switch ($dtoWordBody->contentType) {
                // main
            case 1:
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

                InteractiveUtility::publishStats($type, $model->id, 'decrement');

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

                // log
            case 2:
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
                // main
            case 1:
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

                InteractiveUtility::publishStats($type, $model->id, 'decrement');

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

                // log
            case 2:
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
