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
use App\Models\Group;
use App\Utilities\ConfigUtility;
use App\Utilities\InteractionUtility;
use App\Utilities\PermissionUtility;

class ContentService
{
    // get content date limit
    public static function getContentDateLimit(?int $authUserId = null, ?string $authUserExpiredDatetime = null): ?string
    {
        // Not logged in is not handled because the route middleware already handles it.
        if (empty($authUserId)) {
            return null;
        }

        $modeConfig = ConfigHelper::fresnsConfigByItemKey('site_mode');
        if ($modeConfig == 'public') {
            return null;
        }

        return $authUserExpiredDatetime ?? now();
    }

    // check content view permission
    public static function checkUserContentViewPerm(string $dateTime, ?int $authUserId = null, ?string $authUserExpiredDatetime = null): void
    {
        // Not logged in is not handled because the route middleware already handles it.
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

        $contentCreatedDatetime = strtotime($dateTime);
        $dateLimit = strtotime($authUserExpiredDatetime);

        if ($contentCreatedDatetime > $dateLimit) {
            throw new ApiException(35304);
        }
    }

    // check content view permission
    public static function checkGroupContentViewPerm(string $dateTime, ?int $groupId = null, ?int $authUserId = null): void
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

        if ($whitelistRoles && in_array($userRole['id'], $whitelistRoles)) {
            return;
        }

        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_GROUP, $groupId, $authUserId);

        if (! $interactionStatus['followStatus']) {
            throw new ApiException(37103);
        }

        if ($group->private_end_after == Group::PRIVATE_OPTION_UNRESTRICTED) {
            return;
        }

        if ($group->private_end_after == Group::PRIVATE_OPTION_HIDE_ALL) {
            throw new ApiException(37105);
        }

        $contentCreatedDatetime = strtotime($dateTime);
        $dateLimit = strtotime($interactionStatus['followExpiryDateTime']);

        if ($contentCreatedDatetime > $dateLimit) {
            throw new ApiException(37106);
        }
    }

    // check publish perm
    // $type = post / comment
    public function checkPublishPerm(string $type, int $authUserId, ?int $contentMainId = null, ?string $langTag = null, ?string $timezone = null): void
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

        $publishConfig = ConfigUtility::getPublishConfigByType($type, $authUserId, $langTag, $timezone);

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
