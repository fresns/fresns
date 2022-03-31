<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Service;

use App\Helpers\InteractiveHelper;
use App\Models\Hashtag;
use App\Models\User;

class HashtagService
{
    public function getGroupDetail($hashtagId, $langTag, $timezone)
    {
        $hashtag = Hashtag::withTrashed()->find($hashtagId);
        $user = User::withTrashed()->find($hashtag->user_id);

        $hashtagInteractive = InteractiveHelper::fresnsHashtagInteractive($langTag);
        $item['creator'] = $user->getUserProfile($timezone);

        $detail = array_merge($groupInfo, $hashtagInteractive, $item);

        return $detail;
    }
}
