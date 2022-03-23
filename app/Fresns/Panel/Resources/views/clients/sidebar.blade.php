<div class="col-lg-2 fresns-sidebar mt-3 mt-lg-0">
    <nav class="navbar navbar-expand-lg navbar-light flex-lg-column shadow" style="background-color:#e3f2fd;">
        <div class="container-fluid d-lg-flex flex-lg-column">
            <span class="navbar-brand">{{ __('FsLang::panel.menu_clients') }}</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav d-flex flex-column">
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.menus.*') ? 'active' : '' }}" href="{{ route('panel.menus.index') }}">{{ __('FsLang::panel.sidebar_menus') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.columns.*') ? 'active' : '' }}" href="{{ route('panel.columns.index') }}">{{ __('FsLang::panel.sidebar_columns') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.language.packs.*') ? 'active' : '' }}" href="{{ route('panel.language.packs.index') }}">{{ __('FsLang::panel.sidebar_language_packs') }}</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.keys.*') ? 'active' : '' }} " href="{{ route('panel.keys.index') }}">{{ __('FsLang::panel.sidebar_keys') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.engines.*', 'panel.themes.*') ? 'active' : '' }}" href="{{ route('panel.engines.index') }}">{{ __('FsLang::panel.sidebar_website') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.apps.*') ? 'active' : '' }}" href="{{ route('panel.apps.index') }}">{{ __('FsLang::panel.sidebar_apps') }}</a></li>
                </ul>
            </div>
        </div>
        <div class="fresns-copyright d-none d-lg-block">Powered by Fresns</div>
    </nav>
</div>
