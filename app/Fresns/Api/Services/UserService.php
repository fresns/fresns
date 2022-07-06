<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Exceptions\ApiException;
use App\Fresns\Api\Traits\ApiHeaderTrait;
use App\Helpers\InteractiveHelper;
use App\Models\ArchiveUsage;
use App\Models\ExtendUsage;
use App\Models\OperationUsage;
use App\Models\User;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractiveUtility;
use App\Utilities\PermissionUtility;

class UserService
{
    use ApiHeaderTrait;

    public function userList(?User $user, string $langTag, string $timezone, ?int $authUserId = null)
    {
        if (! $user) {
            return null;
        }

        $userProfile = $user->getUserProfile($langTag, $timezone);
        $userMainRole = $user->getUserMainRole($langTag, $timezone);

        $item['stats'] = $user->getUserStats($langTag);
        $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_USER, $user->id, $langTag);

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
        $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_POST, $user->id, $langTag);
        $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_USER, $user->id, $langTag);
        $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_USER, $user->id, $langTag);
        $item['roles'] = $user->getUserRoles($langTag, $timezone);

        $interactiveConfig = InteractiveHelper::fresnsUserInteractive($langTag);
        $interactiveStatus = InteractiveUtility::checkInteractiveStatus(InteractiveUtility::TYPE_USER, $user->id, $authUserId);
        $followMeStatus['followMeStatus'] = InteractiveUtility::checkUserFollowMe($user->id, $authUserId);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus, $followMeStatus);

        $item['dialog'] = PermissionUtility::checkUserDialogPerm($user->id, $authUserId, $langTag);

        $data = array_merge($userProfile, $userMainRole, $item);

        return $data;
    }

    // check content view perm permission
    public static function checkUserContentViewPerm(string $dateTime)
    {
        $userContentViewPerm = self::userContentViewPerm();

        if ($userContentViewPerm['type'] == 2) {
            $dateLimit = strtotime($userContentViewPerm['dateLimit']);
            $contentCreateTime = strtotime($dateTime);

            if ($dateLimit < $contentCreateTime) {
                throw new ApiException(35304);
            }
        }
    }
}
