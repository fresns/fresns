<div class="col-lg-2 fresns-sidebar mt-3 mt-lg-0">
    <nav class="navbar navbar-expand-lg navbar-light flex-lg-column shadow" style="background-color:#e3f2fd;">
        <div class="container-fluid d-lg-flex flex-lg-column">
            <span class="navbar-brand">{{ __('FsLang::panel.menu_extends') }}</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav d-flex flex-column">
                    <li class="nav-item"><a class="nav-link {{ Route::is('panel.content-handler.*') ? 'active' : '' }}" href="{{ route('panel.content-handler.index') }}">{{ __('FsLang::panel.sidebar_extend_content_handler') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ (request()->route('usageType') == 'content-type') ? 'active' : '' }}" href="{{ route('panel.app-usages.index', ['usageType' => 'content-type']) }}">{{ __('FsLang::panel.sidebar_extend_content_type') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ (request()->route('usageType') == 'editor') ? 'active' : '' }}" href="{{ route('panel.app-usages.index', ['usageType' => 'editor']) }}">{{ __('FsLang::panel.sidebar_extend_editor') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ (request()->route('usageType') == 'manage') ? 'active' : '' }}" href="{{ route('panel.app-usages.index', ['usageType' => 'manage']) }}">{{ __('FsLang::panel.sidebar_extend_manage') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ (request()->route('usageType') == 'group') ? 'active' : '' }}" href="{{ route('panel.app-usages.index', ['usageType' => 'group']) }}">{{ __('FsLang::panel.sidebar_extend_group') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ (request()->route('usageType') == 'user-feature') ? 'active' : '' }}" href="{{ route('panel.app-usages.index', ['usageType' => 'user-feature']) }}">{{ __('FsLang::panel.sidebar_extend_user_feature') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ (request()->route('usageType') == 'user-profile') ? 'active' : '' }}" href="{{ route('panel.app-usages.index', ['usageType' => 'user-profile']) }}">{{ __('FsLang::panel.sidebar_extend_user_profile') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ (request()->route('usageType') == 'channel') ? 'active' : '' }}" href="{{ route('panel.app-usages.index', ['usageType' => 'channel']) }}">{{ __('FsLang::panel.sidebar_extend_channel') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ Route::is('panel.command-words.*') ? 'active' : '' }}" href="{{ route('panel.command-words.index') }}">{{ __('FsLang::panel.sidebar_extend_command_words') }}</a></li>
                </ul>
            </div>
        </div>
        <div class="fresns-copyright d-none d-lg-block">Powered by Fresns</div>
    </nav>
</div>
