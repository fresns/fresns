<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
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
use App\Utilities\ArrUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractionUtility;
use App\Utilities\SubscribeUtility;

class HashtagService
{
    public function hashtagData(?Hashtag $hashtag, string $langTag, string $timezone, ?int $authUserId = null)
    {
        if (! $hashtag) {
            return null;
        }

        $cacheKey = "fresns_api_hashtag_{$hashtag->slug}_{$langTag}";
        $cacheTag = 'fresnsHashtags';

        $data = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($data)) {
            $hashtagInfo = $hashtag->getHashtagInfo($langTag);

            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_HASHTAG, $hashtag->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_HASHTAG, $hashtag->id, $langTag);
            $item['extends'] = ExtendUtility::getContentExtends(ExtendUsage::TYPE_HASHTAG, $hashtag->id, $langTag);

            $data = array_merge($hashtagInfo, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($data, $cacheKey, $cacheTag, null, $cacheTime);
        }

        $interactionConfig = InteractionHelper::fresnsHashtagInteraction($langTag);
        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_HASHTAG, $hashtag->id, $authUserId);
        $data['interaction'] = array_merge($interactionConfig, $interactionStatus);

        SubscribeUtility::notifyViewContent('hashtag', $hashtag->slug, null, $authUserId);

        $hashtagData = self::handleHashtagCount($hashtag, $data);
        $result = self::handleHashtagDate($hashtagData, $timezone, $langTag);

        // filter
        $filterKeys = \request()->get('whitelistKeys') ?? \request()->get('blacklistKeys');
        $filter = [
            'type' => \request()->get('whitelistKeys') ? 'whitelist' : 'blacklist',
            'keys' => array_filter(explode(',', $filterKeys)),
        ];

        if (empty($filter['keys'])) {
            return $result;
        }

        $currentRouteName = \request()->route()->getName();
        $filterRouteList = [
            'api.hashtag.list',
            'api.hashtag.detail',
        ];

        if (! in_array($currentRouteName, $filterRouteList)) {
            return $result;
        }

        return ArrUtility::filter($result, $filter['type'], $filter['keys']);
    }

    // handle hashtag data count
    public static function handleHashtagCount(?Hashtag $hashtag, ?array $hashtagData)
    {
        if (empty($hashtag) || empty($hashtagData)) {
            return $hashtagData;
        }

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'hashtag_liker_count',
            'hashtag_disliker_count',
            'hashtag_follower_count',
            'hashtag_blocker_count',
        ]);

        $hashtagData['viewCount'] = $hashtag->view_count;
        $hashtagData['likeCount'] = $configKeys['hashtag_liker_count'] ? $hashtag->like_count : null;
        $hashtagData['dislikeCount'] = $configKeys['hashtag_disliker_count'] ? $hashtag->dislike_count : null;
        $hashtagData['followCount'] = $configKeys['hashtag_follower_count'] ? $hashtag->follow_count : null;
        $hashtagData['blockCount'] = $configKeys['hashtag_blocker_count'] ? $hashtag->block_count : null;
        $hashtagData['postCount'] = $hashtag->post_count;
        $hashtagData['postDigestCount'] = $hashtag->post_digest_count;
        $hashtagData['commentCount'] = $hashtag->comment_count;
        $hashtagData['commentDigestCount'] = $hashtag->comment_digest_count;

        return $hashtagData;
    }

    // handle hashtag data date
    public static function handleHashtagDate(?array $hashtagData, string $timezone, string $langTag)
    {
        if (empty($hashtagData)) {
            return $hashtagData;
        }

        $hashtagData['createdDatetime'] = DateHelper::fresnsDateTimeByTimezone($hashtagData['createdDatetime'], $timezone, $langTag);

        $hashtagData['interaction']['followExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($hashtagData['interaction']['followExpiryDateTime'], $timezone, $langTag);

        return $hashtagData;
    }
}
