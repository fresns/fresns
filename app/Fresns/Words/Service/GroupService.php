<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Service;

use App\Helpers\InteractiveHelper;
use App\Models\Group;
use App\Models\User;

class GroupService
{
    public function getGroupDetail($groupId, $langTag, $timezone)
    {
        $group = Group::withTrashed()->find($groupId);
        $parentGroup = Group::withTrashed()->find($groupId->parent_id);
        $user = User::withTrashed()->find($group->user_id);

        $groupInteractive = InteractiveHelper::fresnsGroupInteractive($langTag);
        $item['creator'] = $user->getUserProfile($timezone);

        $detail = array_merge($groupInfo, $groupInteractive, $item);

        return $detail;
    }
}
