<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Helpers;

use Illuminate\Support\Facades\App;

class LangHelper
{
    // Initialize language information
    public static function initLocale()
    {
        // Language Tags (langTag)
        // Leave blank to output the default language content
        // If no default language is queried, the first entry is output
        $locale = request()->input('lang', 'zh-Hans');
        App::setLocale($locale);
    }
}
