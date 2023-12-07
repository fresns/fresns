<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Detail;

use App\Fresns\Api\Services\AccountService;
use App\Fresns\Api\Services\CommentService;
use App\Fresns\Api\Services\GroupService;
use App\Fresns\Api\Services\HashtagService;
use App\Fresns\Api\Services\PostService;
use App\Fresns\Api\Services\UserService;
use App\Fresns\Api\Traits\ApiHeaderTrait;
use App\Fresns\Words\Detail\DTO\GetAccountDetailDTO;
use App\Fresns\Words\Detail\DTO\GetCommentDetailDTO;
use App\Fresns\Words\Detail\DTO\GetGroupDetailDTO;
use App\Fresns\Words\Detail\DTO\GetHashtagDetailDTO;
use App\Fresns\Words\Detail\DTO\GetPostDetailDTO;
use App\Fresns\Words\Detail\DTO\GetUserDetailDTO;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Account;
use App\Models\Comment;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use App\Utilities\ConfigUtility;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;

class Detail
{
    use ApiHeaderTrait;
    use CmdWordResponseTrait;

    // getAccountDetail
    public function getAccountDetail($wordBody)
    {
        $dtoWordBody = new GetAccountDetailDTO($wordBody);
        $langTag = $dtoWordBody->langTag ?? $this->langTag();

        $account = Account::where('aid', $dtoWordBody->aid)->first();

        if (empty($account)) {
            return $this->failure(
                31502,
                ConfigUtility::getCodeMessage(31502, 'Fresns', $langTag)
            );
        }

        $timezone = $dtoWordBody->timezone ?? $this->timezone();

        $service = new AccountService();
        $detail = $service->accountData($account, $langTag, $timezone);

        return $this->success($detail);
    }

    // getUserDetail
    public function getUserDetail($wordBody)
    {
        $dtoWordBody = new GetUserDetailDTO($wordBody);

        $user = User::where('uid', $dtoWordBody->uid)->first();

        if (empty($user)) {
            return $this->failure(
                31602,
                ConfigUtility::getCodeMessage(31602, 'Fresns', $this->langTag())
            );
        }

        $langTag = $dtoWordBody->langTag ?? $this->langTag();
        $timezone = $dtoWordBody->timezone ?? $this->timezone();
        $authUserId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->authUid);

        $service = new UserService();
        $detail = $service->userData($user, 'detail', $langTag, $timezone, $authUserId);

        return $this->success($detail);
    }

    // getGroupDetail
    public function getGroupDetail($wordBody)
    {
        $dtoWordBody = new GetGroupDetailDTO($wordBody);

        $group = Group::where('gid', $dtoWordBody->gid)->first();

        if (empty($group)) {
            return $this->failure(
                37100,
                ConfigUtility::getCodeMessage(37100, 'Fresns', $this->langTag())
            );
        }

        if (! $group->is_enabled) {
            return $this->failure(
                37101,
                ConfigUtility::getCodeMessage(37101, 'Fresns', $this->langTag())
            );
        }

        $langTag = $dtoWordBody->langTag ?? $this->langTag();
        $timezone = $dtoWordBody->timezone ?? $this->timezone();
        $authUserId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->authUid);

        $service = new GroupService();
        $detail = $service->groupData($group, $langTag, $timezone, $authUserId);

        return $this->success($detail);
    }

    // getHashtagDetail
    public function getHashtagDetail($wordBody)
    {
        $dtoWordBody = new GetHashtagDetailDTO($wordBody);

        $hid = StrHelper::slug($dtoWordBody->hid);

        $hashtag = Hashtag::where('slug', $hid)->first();

        if (empty($hashtag)) {
            return $this->failure(
                37200,
                ConfigUtility::getCodeMessage(37200, 'Fresns', $this->langTag())
            );
        }

        if (! $hashtag->is_enabled) {
            return $this->failure(
                37201,
                ConfigUtility::getCodeMessage(37201, 'Fresns', $this->langTag())
            );
        }

        $langTag = $dtoWordBody->langTag ?? $this->langTag();
        $timezone = $dtoWordBody->timezone ?? $this->timezone();
        $authUserId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->authUid);

        $service = new HashtagService();
        $detail = $service->hashtagData($hashtag, $langTag, $timezone, $authUserId);

        return $this->success($detail);
    }

    // getPostDetail
    public function getPostDetail($wordBody)
    {
        $dtoWordBody = new GetPostDetailDTO($wordBody);

        $post = Post::with(['author'])->where('pid', $dtoWordBody->pid)->first();

        if (empty($post)) {
            return $this->failure(
                37400,
                ConfigUtility::getCodeMessage(37400, 'Fresns', $this->langTag())
            );
        }

        // check author
        if (empty($post?->author)) {
            return $this->failure(
                35203,
                ConfigUtility::getCodeMessage(35203, 'Fresns', $this->langTag())
            );
        }

        $authUserId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->authUid);

        if (! $post->is_enabled && $post->user_id != $authUserId) {
            return $this->failure(
                37401,
                ConfigUtility::getCodeMessage(37401, 'Fresns', $this->langTag())
            );
        }

        $langTag = $dtoWordBody->langTag ?? $this->langTag();
        $timezone = $dtoWordBody->timezone ?? $this->timezone();
        $type = $dtoWordBody->type ?? 'detail';

        $service = new PostService();
        $detail = $service->postData($post, $type, $langTag, $timezone, $authUserId, $dtoWordBody->mapId, $dtoWordBody->mapLng, $dtoWordBody->mapLat, $dtoWordBody->outputPreview, false);

        return $this->success($detail);
    }

    // getCommentDetail
    public function getCommentDetail($wordBody)
    {
        $dtoWordBody = new GetCommentDetailDTO($wordBody);

        $comment = Comment::with(['author'])->where('cid', $dtoWordBody->cid)->first();

        if (empty($comment)) {
            return $this->failure(
                37500,
                ConfigUtility::getCodeMessage(37500, 'Fresns', $this->langTag())
            );
        }

        // check author
        if (empty($comment?->author)) {
            return $this->failure(
                35203,
                ConfigUtility::getCodeMessage(35203, 'Fresns', $this->langTag())
            );
        }

        $authUserId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->authUid);

        if (! $comment->is_enabled && $comment->user_id != $authUserId) {
            return $this->failure(
                37501,
                ConfigUtility::getCodeMessage(37501, 'Fresns', $this->langTag())
            );
        }

        $langTag = $dtoWordBody->langTag ?? $this->langTag();
        $timezone = $dtoWordBody->timezone ?? $this->timezone();
        $type = $dtoWordBody->type ?? 'detail';

        $service = new CommentService();

        $commentConfig = [
            'mapId' => $dtoWordBody->mapId,
            'longitude' => $dtoWordBody->mapLng,
            'latitude' => $dtoWordBody->mapLat,
            'outputSubComments' => $dtoWordBody->outputSubComments,
            'outputReplyToPost' => $dtoWordBody->outputReplyToPost,
            'outputReplyToComment' => $dtoWordBody->outputReplyToComment,
            'whetherToFilter' => false,
        ];

        $detail = $service->commentData(
            $comment,
            $type,
            $langTag,
            $timezone,
            $authUserId,
            $commentConfig['mapId'],
            $commentConfig['longitude'],
            $commentConfig['latitude'],
            $commentConfig['outputSubComments'],
            $commentConfig['outputReplyToPost'],
            $commentConfig['outputReplyToComment'],
            $commentConfig['whetherToFilter'],
        );

        return $this->success($detail);
    }
}
