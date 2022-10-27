<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Exceptions\ApiException;
use App\Helpers\InteractiveHelper;
use App\Models\ArchiveUsage;
use App\Models\ExtendUsage;
use App\Models\OperationUsage;
use App\Models\User;
use App\Utilities\ContentUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractiveUtility;
use App\Utilities\PermissionUtility;
use Illuminate\Support\Arr;

class UserService
{
    public function userData(?User $user, string $langTag, string $timezone, ?int $authUserId = null)
    {
        if (! $user) {
            return null;
        }

        $userProfile = $user->getUserProfile($langTag, $timezone);
        $userMainRole = $user->getUserMainRole($langTag, $timezone);

        $userProfile['nickname'] = ContentUtility::replaceBlockWords('user', $userProfile['nickname']);
        $userProfile['bio'] = ContentUtility::replaceBlockWords('user', $userProfile['bio']);

        $item['stats'] = $user->getUserStats($langTag);
        $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_POST, $user->id, $langTag);
        $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_USER, $user->id, $langTag);
        $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_USER, $user->id, $langTag);
        $item['roles'] = $user->getUserRoles($langTag, $timezone);

        if ($item['operations']['diversifyImages']) {
            $decorate = Arr::pull($item['operations']['diversifyImages'], 'code', 'decorate');
            $verifiedIcon = Arr::pull($item['operations']['diversifyImages'], 'code', 'verified');

            $userProfile['decorate'] = $decorate['imageUrl'] ?? null;
            $userProfile['verifiedIcon'] = $verifiedIcon['imageUrl'] ?? null;
        }

        $interactiveConfig = InteractiveHelper::fresnsUserInteractive($langTag);
        $interactiveStatus = InteractiveUtility::getInteractiveStatus(InteractiveUtility::TYPE_USER, $user->id, $authUserId);
        $followMeStatus['followMeStatus'] = InteractiveUtility::checkUserFollow(InteractiveUtility::TYPE_USER, $authUserId, $user->id);
        $blockMeStatus['blockMeStatus'] = InteractiveUtility::checkUserBlock(InteractiveUtility::TYPE_USER, $authUserId, $user->id);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus, $followMeStatus, $blockMeStatus);

        $item['dialog'] = PermissionUtility::checkUserDialogPerm($user->id, $authUserId, $langTag);

        $data = array_merge($userProfile, $userMainRole, $item);

        return $data;
    }

    // check content view perm permission
    public static function checkUserContentViewPerm(string $dateTime, ?int $authUserId = null)
    {
        $userContentViewPerm = PermissionUtility::getUserContentViewPerm($authUserId);

        if ($userContentViewPerm['type'] == 2) {
            $dateLimit = strtotime($userContentViewPerm['dateLimit']);
            $contentCreateTime = strtotime($dateTime);

            if ($dateLimit < $contentCreateTime) {
                throw new ApiException(35304);
            }
        }
    }
}
