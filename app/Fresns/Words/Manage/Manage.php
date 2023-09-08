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
use App\Models\Config;
use App\Models\Language;
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

        $portal = Language::where('table_name', 'configs')
            ->where('table_column', 'item_value')
            ->where('table_key', $portalKey)
            ->where('lang_tag', $langTag)
            ->first()?->lang_content ?? null;

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
        $langTag = $dtoWordBody->langTag ?? ConfigHelper::fresnsConfigDefaultLangTag();

        Config::withTrashed()->updateOrCreate([
            'item_key' => $portalKey,
        ], [
            'item_value' => null,
            'item_type' => 'string',
            'item_tag' => 'client',
            'is_multilingual' => 1,
            'is_custom' => 1,
            'is_api' => 1,
            'deleted_at' => null,
        ]);

        Language::withTrashed()->updateOrCreate([
            'table_name' => 'configs',
            'table_column' => 'item_value',
            'table_key' => $portalKey,
            'lang_tag' => $langTag,
        ], [
            'table_id' => null,
            'lang_content' => $dtoWordBody->content,
            'deleted_at' => null,
        ]);

        CacheHelper::forgetFresnsConfigs($portalKey);

        return $this->success();
    }

    // checkExtendPerm
    public function checkExtendPerm($wordBody)
    {
        $dtoWordBody = new CheckExtendPermDTO($wordBody);

        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);
        $groupId = PrimaryHelper::fresnsGroupIdByGid($dtoWordBody->gid);

        $checkPerm = PermissionUtility::checkExtendPerm($dtoWordBody->fskey, $dtoWordBody->type, $groupId, $userId);

        if (! $checkPerm) {
            return $this->failure(35301);
        }

        return $this->success();
    }
}
