<div class="col-lg-2 fresns-sidebar mt-3 mt-lg-0">
    <nav class="navbar navbar-expand-lg navbar-light flex-lg-column shadow" style="background-color:#e3f2fd;">
        <div class="container-fluid d-lg-flex flex-lg-column">
            <span class="navbar-brand">{{ __('FsLang::panel.menu_plugins') }}</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav d-flex flex-column">
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.list.*') ? 'active' : '' }}" href="{{ route('panel.list.index') }}">{{ __('FsLang::panel.sidebar_plugins') }}</a></li>
                    <li><hr class="dropdown-divider"></li>
                    @foreach ($enablePlugins as $plugin)
                        @if ($plugin->setting_path)
                            <li class="nav-item"><a href="{{ route('panel.iframe.plugin', ['url' => $plugin->setting_path]) }}" class="nav-link">{{ $plugin->name }}</a></li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="fresns-copyright d-none d-lg-block">Powered by Fresns</div>
    </nav>
</div>
