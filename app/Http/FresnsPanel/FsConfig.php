<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsPanel;

use App\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    const ENABLE_FALSE = 0;

    const NOTICE_URL = 'https://fresns.org/news.json';
    const VERSION_URL = 'https://fresns.org/version.json';

    const PLUGIN_TYPE1 = 1;
    const PLUGIN_TYPE2 = 2;
    const PLUGIN_TYPE3 = 3;
    const PLUGIN_TYPE4 = 4;

    const BACKEND_PATH_NOT = [
        'login', 'dashboard', 'settings', 'keys', 'admins', 'websites', 'apps', 'plugins',
    ];

    const LANGUAGE_MAP = [
        'en' => 'English - English',
        'es' => 'Español - Spanish',
        'fr' => 'Français - French',
        'de' => 'Deutsch - German',
        'ja' => '日本語 - Japanese',
        'ko' => '한국어 - Korean',
        'ru' => 'Русский - Russian',
        'pt' => 'Português - Portuguese',
        'id' => 'Bahasa Indonesia - Indonesian',
        'hi' => 'हिन्दी - Hindi',
        'zh-Hans' => '简体中文 - Chinese (Simplified)',
        'zh-Hant' => '繁體中文 - Chinese (Traditional)',
    ];
}
