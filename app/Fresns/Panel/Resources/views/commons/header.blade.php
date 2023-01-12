<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fresns-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('panel.dashboard') }}"><img src="{{ @asset('/static/images/panel-logo.png') }}" alt="Fresns" height="30"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#headerNavbar" aria-controls="headerNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="headerNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ \Route::is([
                            'panel.dashboard*',
                            'panel.upgrades*',
                            'panel.admins*',
                            'panel.settings*',
                        ]) ? 'active' : '' }}" href="{{ route('panel.dashboard') }}">{{ __('FsLang::panel.menu_dashboard') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ \Route::is([
                            'panel.languages.*',
                            'panel.general.*',
                            'panel.policy.*',
                            'panel.send.*',
                            'panel.user.*',
                            'panel.wallet.*',
                            'panel.storage.*',
                            'panel.maps.*',
                        ]) ? 'active' : ''}}" href="{{ route('panel.languages.index') }}">{{ __('FsLang::panel.menu_systems') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ \Route::is([
                            'panel.rename.*',
                            'panel.interaction.*',
                            'panel.stickers.*',
                            'panel.publish.post.*',
                            'panel.publish.comment.*',
                            'panel.block-words.*',
                            'panel.groups.*',
                            'panel.roles.*'
                        ]) ? 'active' : ''}}" href="{{ route('panel.rename.index' )}}">{{ __('FsLang::panel.menu_operations') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ \Route::is([
                            'panel.editor.*',
                            'panel.content-type.*',
                            'panel.content-handler.*',
                            'panel.manage.*',
                            'panel.group.*',
                            'panel.user-feature.*',
                            'panel.user-profile.*'
                        ]) ? 'active' : '' }}" href="{{ route('panel.editor.index') }}">{{ __('FsLang::panel.menu_extends') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ \Route::is([
                            'panel.menus.*',
                            'panel.columns.*',
                            'panel.paths.*',
                            'panel.language.packs.*',
                            'panel.code.messages.*',
                            'panel.website.*',
                            'panel.app.*',
                        ]) ? 'active' : '' }}" href="{{ route('panel.menus.index') }}">{{ __('FsLang::panel.menu_clients') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ \Route::is([
                            'panel.plugins.*',
                            'panel.panels.*',
                            'panel.engines.*',
                            'panel.themes.*',
                            'panel.keys.*',
                            'panel.iframe.*',
                        ]) ? 'active' : '' }} " href="{{ route('panel.plugins.index') }}">{{ __('FsLang::panel.menu_app_center') }}</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <!--lang-->
                    <div class="btn-group d-flex flex-column">
                        <button type="button" class="btn btn-outline-light btn-sm dropdown-toggle me-4" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-command"></i> {{ __('FsLang::panel.operation') }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ $siteUrl ?? '/' }}" target="_blank"><i class="bi bi-house-door"></i> {{ __('FsLang::panel.site_home') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('panel.caches.index') }}"><i class="bi bi-database"></i> {{ __('FsLang::panel.button_clear_cache') }}</a></li>
                            <li><a class="dropdown-item" href="#panelLangModal" data-bs-toggle="modal"><i class="bi bi-translate"></i> {{ __('FsLang::panel.switch_language') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{route('panel.logout')}}" method="POST" id="panle_logout">
                                    @csrf
                                    <a class="dropdown-item" href="#" onclick="$('#panle_logout').submit()"><i class="bi bi-power"></i> {{ __('FsLang::panel.logout') }}</a>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Panel Lang Modal -->
<div class="modal fade" id="panelLangModal" tabindex="-1" aria-labelledby="panelLangModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="panelLangModalLabel"><i class="bi bi-translate"></i> {{ __('FsLang::panel.switch_language') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush">
                    {{-- {{ $langs[\App::getLocale()] ?? '' }} --}}
                    @foreach($langs as $code => $lang)
                        <a href="?lang={{$code}}" class="list-group-item list-group-item-action @if ($code == \App::getLocale()) active @endif">{{ $lang }}</a>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
