<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Service;

use App\Helpers\InteractiveHelper;
use App\Models\User;

class UserService
{
    public function detail($userId, $langTag, $timezone)
    {
        $user = User::withTrashed()->find($userId);

        $userProfile = $user->getUserProfile($timezone);
        $userMainRole = $user->getUserMainRole($timezone, $langTag);
        $userInteractive = InteractiveHelper::fresnsUserInteractive($langTag);

        $item['roles'] = $user->getUserRoles($timezone, $langTag);
        $item['icons'] = $user->getUserIcons($timezone, $langTag);
        $item['stats'] = $user->getUserStats($langTag);
        $item['draftCount'] = $user->getUserDrafts();

        $detail = array_merge($userProfile, $userMainRole, $item, $userInteractive);

        return $detail;
    }
}
