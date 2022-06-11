<div class="col-lg-2 fresns-sidebar mt-3 mt-lg-0">
    <nav class="navbar navbar-expand-lg navbar-light flex-lg-column shadow" style="background-color:#e3f2fd;">
        <div class="container-fluid d-lg-flex flex-lg-column">
            <span class="navbar-brand">{{ __('FsLang::panel.menu_systems') }}</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav d-flex flex-column">
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.languages.*') ? 'active' : ''}}" href="{{ route('panel.languages.index') }}">{{ __('FsLang::panel.sidebar_languages') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.general.*') ? 'active' : ''}}" href="{{ route('panel.general.index') }}">{{ __('FsLang::panel.sidebar_general') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.policy.*') ? 'active' : ''}}" href="{{ route('panel.policy.index') }}">{{ __('FsLang::panel.sidebar_policy') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.send.*') ? 'active' : ''}}" href="{{ route('panel.send.index') }}">{{ __('FsLang::panel.sidebar_send') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.user.*') ? 'active' : ''}}" href="{{ route('panel.user.index') }}">{{ __('FsLang::panel.sidebar_user') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.wallet.*') ? 'active' : ''}}" href="{{ route('panel.wallet.index')}}">{{ __('FsLang::panel.sidebar_wallet') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.storage.*') ? 'active' : ''}}" href="{{ route('panel.storage.image.index') }}">{{ __('FsLang::panel.sidebar_storage') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.maps.*') ? 'active' : ''}}" href="{{ route('panel.maps.index') }}">{{ __('FsLang::panel.sidebar_maps') }}</a></li>
                    <li class="nav-item d-block d-lg-none my-3 text-secondary">Powered by Fresns</li>
                </ul>
            </div>
        </div>
        <div class="fresns-copyright d-none d-lg-block">Powered by Fresns</div>
    </nav>
</div>
