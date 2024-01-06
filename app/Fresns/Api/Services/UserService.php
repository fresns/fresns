<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Exceptions\ApiException;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Utilities\ConfigUtility;
use App\Utilities\PermissionUtility;

class UserService
{
    // check content view permission
    public static function checkUserContentViewPerm(string $dateTime, ?int $authUserId = null)
    {
        if (empty($authUserId)) {
            return;
        }

        $modeConfig = ConfigHelper::fresnsConfigByItemKey('site_mode');
        if ($modeConfig == 'public') {
            return;
        }

        $checkUserRolePrivateWhitelist = PermissionUtility::checkUserRolePrivateWhitelist($authUserId);
        if ($checkUserRolePrivateWhitelist) {
            return;
        }

        $authUser = PrimaryHelper::fresnsModelById('user', $authUserId);

        $contentCreatedDatetime = strtotime($dateTime);
        $dateLimit = strtotime($authUser->expired_at);

        if ($contentCreatedDatetime > $dateLimit) {
            throw new ApiException(35304);
        }
    }

    // get content date limit
    public static function getContentDateLimit(?int $authUserId = null)
    {
        if (empty($authUserId)) {
            return null;
        }

        $modeConfig = ConfigHelper::fresnsConfigByItemKey('site_mode');

        if ($modeConfig == 'public') {
            return null;
        }

        $authUser = PrimaryHelper::fresnsModelById('user', $authUserId);

        return $authUser?->expired_at ?? now();
    }

    // check publish perm
    // $type = post / comment
    public function checkPublishPerm(string $type, int $authUserId, ?int $contentMainId = null, ?string $langTag = null, ?string $timezone = null)
    {
        // $contentMainId has a value indicating that it is a modify content, not restricted by the publish check.

        // Check publish limit
        $contentInterval = PermissionUtility::checkContentIntervalTime($authUserId, $type);
        if (! $contentInterval && ! $contentMainId) {
            throw new ApiException(36119);
        }
        $contentCount = PermissionUtility::checkContentPublishCountRules($authUserId, $type);
        if (! $contentCount && ! $contentMainId) {
            throw new ApiException(36120);
        }

        $publishConfig = ConfigUtility::getPublishConfigByType($authUserId, $type, $langTag, $timezone);

        // Check publication requirements
        if (! $publishConfig['perm']['publish']) {
            throw new ApiException(36104, 'Fresns', $publishConfig['perm']['tips']);
        }

        // Check additional requirements
        if ($publishConfig['limit']['status'] && $publishConfig['limit']['isInTime'] && $publishConfig['limit']['rule'] == 2) {
            throw new ApiException(36304);
        }
    }
}
