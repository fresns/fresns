<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content;

use App\Fresns\Words\Content\DTO\GenerateDraftFromMainTableDTO;
use App\Fresns\Words\Content\DTO\LogicalDeletionContentDTO;
use App\Fresns\Words\Content\DTO\PhysicalDeletionContentDTO;
use App\Fresns\Words\Content\DTO\ReleaseContentDTO;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;

class Content
{
    use CmdWordResponseTrait;
}
