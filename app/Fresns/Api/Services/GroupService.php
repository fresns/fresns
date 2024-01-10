<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Exceptions\ApiException;
use App\Helpers\PrimaryHelper;
use App\Models\Group;
use App\Utilities\PermissionUtility;

class GroupService
{
    // check content view permission
    public static function checkGroupContentViewPerm(string $dateTime, ?int $groupId = null, ?int $authUserId = null)
    {
        if (empty($groupId) || $groupId == 0) {
            return;
        }

        $group = PrimaryHelper::fresnsModelById('group', $groupId);

        if ($group->privacy == Group::PRIVACY_PUBLIC) {
            return;
        }

        if (empty($authUserId)) {
            throw new ApiException(37103);
        }

        $userRole = PermissionUtility::getUserMainRole($authUserId);
        $whitelistRoles = $group->permissions['private_whitelist_roles'] ?? [];

        if ($whitelistRoles && in_array($userRole['rid'], $whitelistRoles)) {
            return;
        }

        $follow = PrimaryHelper::fresnsFollowModelByType('group', $groupId, $authUserId);

        if (empty($follow)) {
            throw new ApiException(37103);
        }

        if ($group->private_end_after == Group::PRIVATE_OPTION_UNRESTRICTED) {
            return;
        }

        if ($group->private_end_after == Group::PRIVATE_OPTION_HIDE_ALL) {
            throw new ApiException(37105);
        }

        $contentCreatedDatetime = strtotime($dateTime);
        $dateLimit = strtotime($follow->expired_at);

        if ($contentCreatedDatetime > $dateLimit) {
            throw new ApiException(37106);
        }
    }
}
