<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Manage;

use App\Fresns\Words\Manage\DTO\CheckExtendPermDTO;
use App\Fresns\Words\Manage\DTO\GetPortalContentDTO;
use App\Fresns\Words\Manage\DTO\UpdatePortalContentDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Config;
use App\Utilities\PermissionUtility;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;

class Manage
{
    use CmdWordResponseTrait;

    // getPortalContent
    public function getPortalContent($wordBody)
    {
        $dtoWordBody = new GetPortalContentDTO($wordBody);

        $platformId = $dtoWordBody->platformId;

        $portalKey = "portal_{$platformId}";
        $langTag = $dtoWordBody->langTag ?? ConfigHelper::fresnsConfigDefaultLangTag();

        $portalConfig = Config::where('item_key', $portalKey)->first();

        $portal = StrHelper::languageContent($portalConfig?->item_value, $langTag);

        return $this->success([
            'content' => $portal,
        ]);
    }

    // updatePortalContent
    public function updatePortalContent($wordBody)
    {
        $dtoWordBody = new UpdatePortalContentDTO($wordBody);

        $platformId = $dtoWordBody->platformId;

        $portalKey = "portal_{$platformId}";

        $portalConfig = Config::where('item_key', $portalKey)->first();

        if (! $portalConfig) {
            $items = [
                'item_key' => $portalKey,
                'item_value' => null,
                'item_type' => 'object',
                'is_multilingual' => 1,
                'is_custom' => 1,
                'is_api' => 1,
            ];

            $portalConfig = Config::create($items);
        }

        $langTag = $dtoWordBody->langTag ?? ConfigHelper::fresnsConfigDefaultLangTag();

        $itemValue = $portalConfig->item_value;

        $itemValue[$langTag] = $dtoWordBody->content;

        $portalConfig->update([
            'item_value' => $itemValue,
        ]);

        CacheHelper::forgetFresnsConfigs($portalKey);

        return $this->success();
    }

    // checkExtendPerm
    public function checkExtendPerm($wordBody)
    {
        $dtoWordBody = new CheckExtendPermDTO($wordBody);

        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);
        $groupId = PrimaryHelper::fresnsPrimaryId('group', $dtoWordBody->gid);

        $checkPerm = PermissionUtility::checkExtendPerm($dtoWordBody->fskey, $dtoWordBody->type, $groupId, $userId);

        if (! $checkPerm) {
            return $this->failure(35301);
        }

        return $this->success();
    }
}
