<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function update(Config $config, Request $request)
    {
        if (! $request->item_value) {
            $config->setDefaultValue();
        } else {
            $config->item_value = $request->item_value;
        }

        $config->save();

        return $this->updateSuccess();
    }
}
