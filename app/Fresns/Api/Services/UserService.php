<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Models\User;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractiveUtility;
use App\Helpers\InteractiveHelper;
use App\Models\ExtendLinked;
use App\Models\IconLinked;
use App\Models\TipLinked;

class UserService
{
    public function userList(User $user, string $langTag, string $timezone, ?int $authUserId = null)
    {
        $userProfile = $user->getUserProfile($langTag, $timezone);
        $userMainRole = $user->getUserMainRole($langTag, $timezone);

        $item['stats'] = $user->getUserStats($langTag);
        $item['archives'] = $user->getUserArchives($langTag);
        $item['icons'] = ExtendUtility::getIcons(IconLinked::TYPE_USER, $user->id, $langTag);

        $interactiveConfig = InteractiveHelper::fresnsUserInteractive($langTag);
        $interactiveStatus = InteractiveUtility::checkInteractiveStatus(InteractiveUtility::TYPE_USER, $user->id, $authUserId);
        $followMeStatus['followMeStatus'] = InteractiveUtility::checkUserFollowMe($user->id, $authUserId);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus, $followMeStatus);

        $data = array_merge($userProfile, $userMainRole, $item);

        return $data;
    }

    public function userDetail(User $user, string $langTag, string $timezone, ?int $authUserId = null)
    {
        $userProfile = $user->getUserProfile($langTag, $timezone);
        $userMainRole = $user->getUserMainRole($langTag, $timezone);

        $item['stats'] = $user->getUserStats($langTag);
        $item['archives'] = $user->getUserArchives($langTag);
        $item['icons'] = ExtendUtility::getIcons(IconLinked::TYPE_USER, $user->id, $langTag);
        $item['tips'] = ExtendUtility::getTips(TipLinked::TYPE_USER, $user->id, $langTag);
        $item['extends'] = ExtendUtility::getExtends(ExtendLinked::TYPE_USER, $user->id, $langTag);
        $item['roles'] = $user->getUserRoles($langTag, $timezone);

        $interactiveConfig = InteractiveHelper::fresnsUserInteractive($langTag);
        $interactiveStatus = InteractiveUtility::checkInteractiveStatus(InteractiveUtility::TYPE_USER, $user->id, $authUserId);
        $followMeStatus['followMeStatus'] = InteractiveUtility::checkUserFollowMe($user->id, $authUserId);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus, $followMeStatus);

        $data = array_merge($userProfile, $userMainRole, $item);

        return $data;
    }
}
