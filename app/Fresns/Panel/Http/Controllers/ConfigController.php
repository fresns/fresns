<?php

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function update(Config $config, Request $request)
    {
        if (!$request->item_value) {
            $config->setDefaultValue();
        } else {
            $config->item_value = $request->item_value;
        }

        $config->save();

        return $this->updateSuccess();
    }
}
