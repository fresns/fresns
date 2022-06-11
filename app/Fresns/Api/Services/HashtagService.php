<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\InteractiveHelper;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractiveUtility;
use App\Models\ExtendLinked;
use App\Models\Hashtag;
use App\Models\IconLinked;
use App\Models\TipLinked;

class HashtagService
{
    public function hashtagList(Hashtag $hashtag, string $langTag, ?int $authUserId = null)
    {
        $hashtagInfo = $hashtag->getHashtagInfo($langTag);

        $item['icons'] = ExtendUtility::getIcons(IconLinked::TYPE_HASHTAG, $hashtag->id, $langTag);

        $interactiveConfig = InteractiveHelper::fresnsHashtagInteractive($langTag);
        $interactiveStatus = InteractiveUtility::checkInteractiveStatus(InteractiveUtility::TYPE_HASHTAG, $hashtag->id, $authUserId);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus);

        $data = array_merge($hashtagInfo, $item);

        return $data;
    }

    public function hashtagDetail(Hashtag $hashtag, string $langTag, ?int $authUserId = null)
    {
        $hashtagInfo = $hashtag->getHashtagInfo($langTag);

        $item['icons'] = ExtendUtility::getIcons(IconLinked::TYPE_HASHTAG, $hashtag->id, $langTag);
        $item['tips'] = ExtendUtility::getTips(TipLinked::TYPE_HASHTAG, $hashtag->id, $langTag);
        $item['extends'] = ExtendUtility::getExtends(ExtendLinked::TYPE_HASHTAG, $hashtag->id, $langTag);

        $interactiveConfig = InteractiveHelper::fresnsHashtagInteractive($langTag);
        $interactiveStatus = InteractiveUtility::checkInteractiveStatus(InteractiveUtility::TYPE_HASHTAG, $hashtag->id, $authUserId);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus);

        $data = array_merge($hashtagInfo, $item);

        return $data;
    }
}
