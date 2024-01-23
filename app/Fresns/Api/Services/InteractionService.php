<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Exceptions\ApiException;
use App\Helpers\ConfigHelper;
use App\Models\UserFollow;
use App\Models\UserLike;
use App\Utilities\DetailUtility;

class InteractionService
{
    const TYPE_USER = 1;
    const TYPE_GROUP = 2;
    const TYPE_HASHTAG = 3;
    const TYPE_GEOTAG = 4;
    const TYPE_POST = 5;
    const TYPE_COMMENT = 6;

    // check interaction setting
    public static function checkInteractionSetting(string $markType, string $contentType)
    {
        $markType = match ($markType) {
            'like' => 'like',
            'dislike' => 'dislike',
            'follow' => 'follow',
            'block' => 'block',
            'likers' => 'like',
            'dislikers' => 'dislike',
            'followers' => 'follow',
            'blockers' => 'block',
        };

        $setKey = "{$markType}_{$contentType}_public_record";

        $interactionSet = ConfigHelper::fresnsConfigByItemKey($setKey);

        if ($contentType == 'user') {
            if ($interactionSet != 3) {
                throw new ApiException(36201);
            }

            return;
        }

        if (! $interactionSet) {
            throw new ApiException(36201);
        }
    }

    // check my interaction setting
    public static function checkMyInteractionSetting(string $markType, string $contentType)
    {
        $markType = match ($markType) {
            'like' => 'like',
            'dislike' => 'dislike',
            'follow' => 'follow',
            'block' => 'block',
            'likers' => 'like',
            'dislikers' => 'dislike',
            'followers' => 'follow',
            'blockers' => 'block',
        };

        $setKey = "{$markType}_{$contentType}_public_record";

        $interactionSet = ConfigHelper::fresnsConfigByItemKey($setKey);

        if ($contentType == 'user') {
            if ($interactionSet == 1) {
                throw new ApiException(36201);
            }

            return;
        }

        if (! $interactionSet) {
            throw new ApiException(36201);
        }
    }

    // get the users who marked it
    public function getUsersWhoMarkIt(string $getType, string $markType, int $markId, string $orderDirection, string $langTag, ?string $timezone = null, ?int $authUserId = null)
    {
        switch ($getType) {
            case 'likers':
                $interactionQuery = UserLike::markType(UserLike::MARK_TYPE_LIKE)->where('like_id', $markId);
                break;

            case 'dislikers':
                $interactionQuery = UserLike::markType(UserLike::MARK_TYPE_DISLIKE)->where('like_id', $markId);
                break;

            case 'followers':
                $interactionQuery = UserFollow::markType(UserFollow::MARK_TYPE_FOLLOW)->where('follow_id', $markId);
                break;

            case 'blockers':
                $interactionQuery = UserFollow::markType(UserFollow::MARK_TYPE_BLOCK)->where('follow_id', $markId);
                break;
        }

        $interactionData = $interactionQuery->with('creator')
            ->type($markType)
            ->orderBy('created_at', $orderDirection)
            ->paginate(\request()->get('pageSize', 15));

        $userOptions = [
            'viewType' => 'list',
            'isLiveStats' => false,
            'filter' => [
                'type' => \request()->get('filterType'),
                'keys' => \request()->get('filterKeys'),
            ],
        ];

        $paginateData = [];
        foreach ($interactionData as $interaction) {
            if (empty($interaction?->creator)) {
                continue;
            }

            $paginateData[] = DetailUtility::userDetail($interaction->creator, $langTag, $timezone, $authUserId, $userOptions);
        }

        return [
            'paginateData' => $paginateData,
            'interactionData' => $interactionData,
        ];
    }
}
