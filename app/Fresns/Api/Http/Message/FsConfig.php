<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Message;

use App\Fresns\Api\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Notification Form Type
    const SOURCE_TYPE_1 = 1;
    const SOURCE_TYPE_2 = 2;
    const SOURCE_TYPE_3 = 3;
    const SOURCE_TYPE_4 = 4;
    const SOURCE_TYPE_5 = 5;
    const SOURCE_TYPE_6 = 6;

    // Reading Status
    const NO_READ = 1;
    const READED = 2;

    // Avatar
    const DEFAULT_AVATAR = 'default_avatar';
    const ANONYMOUS_AVATAR = 'anonymous_avatar';
    const DEACTIVATE_AVATAR = 'deactivate_avatar';

    // Config
    const DIALOG_STATUS = 'dialog_status';
    const SITE_MODEL = 'site_mode';
    const PRIVATE = 'private';
    const OBJECT_SUCCESS = 2;
}
