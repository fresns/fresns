<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Fresns\Api\Traits\ApiHeaderTrait;
use App\Fresns\Api\Traits\ApiResponseTrait;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use ApiHeaderTrait;
    use ApiResponseTrait;
}
