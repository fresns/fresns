<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\Center\Base;

use App\Helpers\FileHelper;
use Illuminate\Support\Facades\File;

/**
 * Class PluginConst
 * Plugin constants and path-related information.
 */
class PluginConst
{
    const PLUGIN_IMAGE_NAME = 'fresns.png';

    const PLUGIN_SKIP_DIR_ARR = ['.', '..'];

    const PLUGIN_TYPE_ENGINE = 1;
    const PLUGIN_TYPE_EXTENSION = 2;
    const PLUGIN_TYPE_MOBILE = 3;
    const PLUGIN_TYPE_CONTROLLER_PANEL = 4;
    const PLUGIN_TYPE_THEME = 5;
}
