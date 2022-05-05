<div class="col-lg-2 fresns-sidebar mt-3 mt-lg-0">
    <nav class="navbar navbar-expand-lg navbar-light flex-lg-column shadow" style="background-color:#e3f2fd;">
        <div class="container-fluid d-lg-flex flex-lg-column">
            <span class="navbar-brand">{{ __('FsLang::panel.menu_dashboard') }}</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav d-flex flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ \Route::is('panel.dashboard*') ? 'active' : '' }} " href="{{ route('panel.dashboard') }}">{{ __('FsLang::panel.sidebar_home') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ \Route::is('panel.upgrades*') ? 'active' : '' }} " href="{{ route('panel.upgrades') }}">
                            {{ __('FsLang::panel.sidebar_upgrades') }}
                            @if($pluginUpgradeCount > 0)
                                <span class="badge rounded-pill bg-danger">{{ $pluginUpgradeCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ \Route::is('panel.admins*') ? 'active' : '' }}" href="{{ route('panel.admins.index') }}">{{ __('FsLang::panel.sidebar_admins') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ \Route::is('panel.settings*') ? 'active' : '' }}" href="{{ route('panel.settings') }}">{{ __('FsLang::panel.sidebar_settings') }}</a>
                    </li>
                    <li class="nav-item d-block d-lg-none my-3 text-secondary">Powered by Fresns</li>
                </ul>
            </div>
        </div>
        <div class="fresns-copyright d-none d-lg-block">Powered by Fresns</div>
    </nav>
</div>