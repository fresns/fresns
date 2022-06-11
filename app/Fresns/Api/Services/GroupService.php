<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\InteractiveHelper;
use App\Models\ExtendLinked;
use App\Models\Group;
use App\Models\IconLinked;
use App\Models\TipLinked;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractiveUtility;
use App\Utilities\PermissionUtility;

class GroupService
{
    public function groupList(Group $group, string $langTag, string $timezone, ?int $authUserId = null)
    {
        $groupInfo = $group->getGroupInfo($langTag);

        $item['icons'] = ExtendUtility::getIcons(IconLinked::TYPE_GROUP, $group->id, $langTag);

        $item['publishRule'] = PermissionUtility::checkUserGroupPublishPerm($group->id, $authUserId);
        $item['admins'] = $group->getGroupAdmins($langTag, $timezone);

        $interactiveConfig = InteractiveHelper::fresnsGroupInteractive($langTag);
        $interactiveStatus = InteractiveUtility::checkInteractiveStatus(InteractiveUtility::TYPE_GROUP, $group->id, $authUserId);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus);

        $data = array_merge($groupInfo, $item);

        return $data;
    }

    public function groupDetail(Group $group, string $langTag, string $timezone, ?int $authUserId = null)
    {
        $groupInfo = $group->getGroupInfo($langTag);

        $item['icons'] = ExtendUtility::getIcons(IconLinked::TYPE_GROUP, $group->id, $langTag);
        $item['tips'] = ExtendUtility::getTips(TipLinked::TYPE_GROUP, $group->id, $langTag);
        $item['extends'] = ExtendUtility::getExtends(ExtendLinked::TYPE_GROUP, $group->id, $langTag);

        $creator = $group->creator;

        $item['creator'] = null;
        if (! empty($creator)) {
            $userProfile = $creator->getUserProfile($langTag, $timezone);
            $userMainRole = $creator->getUserMainRole($langTag, $timezone);
            $item['creator'] = array_merge($userProfile, $userMainRole);
        }

        $item['publishRule'] = PermissionUtility::checkUserGroupPublishPerm($group->id, $authUserId);
        $item['admins'] = $group->getGroupAdmins($langTag, $timezone);

        $interactiveConfig = InteractiveHelper::fresnsGroupInteractive($langTag);
        $interactiveStatus = InteractiveUtility::checkInteractiveStatus(InteractiveUtility::TYPE_GROUP, $group->id, $authUserId);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus);

        $data = array_merge($groupInfo, $item);

        return $data;
    }
}
