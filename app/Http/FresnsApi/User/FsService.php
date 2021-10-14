<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\User;

use App\Helpers\DateHelper;
use App\Helpers\StrHelper;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguagesService;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRelsService;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRolesConfig;
use App\Http\FresnsDb\FresnsMembers\FresnsMembersConfig;
use App\Http\FresnsDb\FresnsPluginBadges\FresnsPluginBadgesService;
use App\Http\FresnsDb\FresnsPlugins\FresnsPluginsService;
use App\Http\FresnsDb\FresnsUserConnects\FresnsUserConnectsConfig;
use App\Http\FresnsDb\FresnsUsers\FresnsUsersConfig;
use App\Http\FresnsDb\FresnsUserWallets\FresnsUserWallets;
use Illuminate\Support\Facades\DB;

class FsService
{
}
