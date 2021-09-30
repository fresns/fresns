<!doctype html>
<html lang="{{ $lang }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Fresns" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fresns Console</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/css/bootstrap-icons.css">
    <link rel="stylesheet" href="/static/css/console.css">
</head>

<body>

<header>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color:#7952b3;">
        <div class="container-lg">
            <a class="navbar-brand" href="/fresns/dashboard"><img src="/static/images/fresns-logo-white.png" height="40"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" id="language" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-translate"></i> {{ $lang_desc }}</button>
                    <ul class="dropdown-menu">
                        <li><button class="dropdown-item" type="button" href="en">English - English</button></li>
                        <li><button class="dropdown-item" type="button" href="es">Español - Spanish</button></li>
                        <li><button class="dropdown-item" type="button" href="fr">Français - French</button></li>
                        <li><button class="dropdown-item" type="button" href="de">Deutsch - German</button></li>
                        <li><button class="dropdown-item" type="button" href="ja">日本語 - Japanese</button></li>
                        <li><button class="dropdown-item" type="button" href="ko">한국어 - Korean</button></li>
                        <li><button class="dropdown-item" type="button" href="ru">Русский - Russian</button></li>
                        <li><button class="dropdown-item" type="button" href="pt">Português - Portuguese</button></li>
                        <li><button class="dropdown-item" type="button" href="id">Bahasa Indonesia - Indonesian</button></li>
                        <li><button class="dropdown-item" type="button" href="hi">हिन्दी - Hindi</button></li>
                        <li><button class="dropdown-item" type="button" href="zh-Hans">简体中文 - Chinese (Simplified)</button></li>
                        <li><button class="dropdown-item" type="button" href="zh-Hant">繁體中文 - Chinese (Traditional)</button></li>
                    </ul>
                    <a class="btn btn-warning btn-sm ms-1" href="/fresns/logout" role="button" data-bs-toggle="tooltip" data-bs-placement="right" title="@lang('fresns.logout')"><i class="bi bi-power"></i></a>
                </div>
                <ul class="navbar-nav d-flex flex-row ms-auto">
                    <li class="px-2">
                        <a href="/fresns/dashboard" class="nav-link d-flex flex-column align-items-center {{ $choose == 'dashboard' ? 'active' : '' }}">
                            <i class="bi bi-speedometer2 fresns-nav-icon"></i>
                            <span class="fresns-nav-name">@lang('fresns.menuDashboard')</span>
                        </a>
                    </li>
                    <li class="px-2">
                        <a href="/fresns/settings" class="nav-link d-flex flex-column align-items-center {{ $choose == 'settings' ? 'active' : '' }}">
                            <i class="bi bi-gear fresns-nav-icon"></i>
                            <span class="fresns-nav-name">@lang('fresns.menuSettings')</span>
                        </a>
                    </li>
                    <li class="px-2">
                        <a href="/fresns/keys" class="nav-link d-flex flex-column align-items-center {{ $choose == 'keys' ? 'active' : '' }}">
                            <i class="bi bi-key fresns-nav-icon"></i>
                            <span class="fresns-nav-name">@lang('fresns.menuKeys')</span>
                        </a>
                    </li>
                    <li class="px-2">
                        <a href="/fresns/admins" class="nav-link d-flex flex-column align-items-center {{ $choose == 'admins' ? 'active' : '' }}">
                            <i class="bi bi-sliders fresns-nav-icon"></i>
                            <span class="fresns-nav-name">@lang('fresns.menuAdmins')</span>
                        </a>
                    </li>
                    <li class="px-2">
                        <a href="/fresns/websites" class="nav-link d-flex flex-column align-items-center {{ $choose == 'websites' ? 'active' : '' }}">
                            <i class="bi bi-laptop fresns-nav-icon"></i>
                            <span class="fresns-nav-name">@lang('fresns.menuWebsites')</span>
                        </a>
                    </li>
                    <li class="px-2">
                        <a href="/fresns/apps" class="nav-link d-flex flex-column align-items-center {{ $choose == 'apps' ? 'active' : '' }}">
                            <i class="bi bi-phone fresns-nav-icon"></i>
                            <span class="fresns-nav-name">@lang('fresns.menuApps')</span>
                        </a>
                    </li>
                    <li class="px-2">
                        <a href="/fresns/plugins" class="nav-link d-flex flex-column align-items-center {{ $choose == 'plugins' ? 'active' : '' }}">
                            <i class="bi bi-journal-code fresns-nav-icon"></i>
                            <span class="fresns-nav-name">@lang('fresns.menuPlugins')</span>
                        </a>
                    </li>
                    <li class="px-2">
                        <a href="/fresns/iframe?url=https://apps.fresns.org?lang={{$lang}}" class="nav-link d-flex flex-column align-items-center">
                            <i class="bi bi-shop-window fresns-nav-icon"></i>
                            <span class="fresns-nav-name">@lang('fresns.fresnsAppStore')</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>