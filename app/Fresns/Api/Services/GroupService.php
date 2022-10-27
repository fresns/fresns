<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\InteractiveHelper;
use App\Models\ArchiveUsage;
use App\Models\ExtendUsage;
use App\Models\Group;
use App\Models\OperationUsage;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractiveUtility;
use App\Utilities\PermissionUtility;

class GroupService
{
    public function groupData(?Group $group, string $langTag, string $timezone, ?int $authUserId = null)
    {
        if (! $group) {
            return null;
        }

        $groupInfo = $group->getGroupInfo($langTag);

        $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_GROUP, $group->id, $langTag);
        $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_GROUP, $group->id, $langTag);
        $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_GROUP, $group->id, $langTag);

        $creator = $group->creator;

        $item['creator'] = null;
        if (! empty($creator)) {
            $userProfile = $creator->getUserProfile($langTag, $timezone);
            $userMainRole = $creator->getUserMainRole($langTag, $timezone);
            $item['creator'] = array_merge($userProfile, $userMainRole);
        }

        $item['publishRule'] = PermissionUtility::checkUserGroupPublishPerm($group->id, $group->permissions, $authUserId);

        $adminList = null;
        foreach ($group->admins as $admin) {
            $userProfile = $admin->getUserProfile($timezone);
            $userMainRole = $admin->getUserMainRole($langTag, $timezone);

            $adminList[] = array_merge($userProfile, $userMainRole);
        }
        $item['admins'] = $adminList;

        $interactiveConfig = InteractiveHelper::fresnsGroupInteractive($langTag);
        $interactiveStatus = InteractiveUtility::getInteractiveStatus(InteractiveUtility::TYPE_GROUP, $group->id, $authUserId);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus);

        $data = array_merge($groupInfo, $item);

        return $data;
    }
}
