<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Traits;

trait HookServiceTrait
{
    // Hook functions: service (initializing)
    public function hookInit()
    {
        return true;
    }

    // Hook functions: tree service (Before the list, initialize the query criteria)
    public function hookListTreeBefore()
    {
        return true;
    }
}
