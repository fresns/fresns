<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\InteractionHelper;
use App\Models\ArchiveUsage;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\Hashtag;
use App\Models\OperationUsage;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractionUtility;
use Illuminate\Support\Facades\Cache;

class HashtagService
{
    public function hashtagData(?Hashtag $hashtag, string $langTag, string $timezone, ?int $authUserId = null)
    {
        if (! $hashtag) {
            return null;
        }

        $cacheKey = "fresns_api_hashtag_{$hashtag->slug}_{$langTag}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);

        $data = Cache::remember($cacheKey, $cacheTime, function () use ($hashtag, $langTag) {
            $hashtagInfo = $hashtag->getHashtagInfo($langTag);

            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_HASHTAG, $hashtag->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_HASHTAG, $hashtag->id, $langTag);
            $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_HASHTAG, $hashtag->id, $langTag);

            return array_merge($hashtagInfo, $item);
        });

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'hashtag_liker_count',
            'hashtag_disliker_count',
            'hashtag_follower_count',
            'hashtag_blocker_count',
        ]);
        $data['likeCount'] = $configKeys['hashtag_liker_count'] ? $hashtag->like_count : null;
        $data['dislikeCount'] = $configKeys['hashtag_disliker_count'] ? $hashtag->dislike_count : null;
        $data['followCount'] = $configKeys['hashtag_follower_count'] ? $hashtag->follow_count : null;
        $data['blockCount'] = $configKeys['hashtag_blocker_count'] ? $hashtag->block_count : null;

        $interactionConfig = InteractionHelper::fresnsHashtagInteraction($langTag);
        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_HASHTAG, $hashtag->id, $authUserId);
        $data['interaction'] = array_merge($interactionConfig, $interactionStatus);

        return HashtagService::handleHashtagDate($data, $timezone, $langTag);
    }

    // handle hashtag data date
    public static function handleHashtagDate(?array $hashtagData, string $timezone, string $langTag)
    {
        if (empty($hashtagData)) {
            return $hashtagData;
        }

        $hashtagData['createDate'] = DateHelper::fresnsDateTimeByTimezone($hashtagData['createDate'], $timezone, $langTag);

        $hashtagData['interaction']['followExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($hashtagData['interaction']['followExpiryDateTime'], $timezone, $langTag);

        return $hashtagData;
    }
}
