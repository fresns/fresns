<div class="col-lg-2 fresns-sidebar mt-3 mt-lg-0">
    <nav class="navbar navbar-expand-lg navbar-light flex-lg-column shadow" style="background-color:#e3f2fd;">
        <div class="container-fluid d-lg-flex flex-lg-column">
            <span class="navbar-brand">{{ __('FsLang::panel.menu_extends') }}</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav d-flex flex-column">
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.editor.*') ? 'active' : '' }}" href="{{ route('panel.editor.index') }}">{{ __('FsLang::panel.sidebar_extend_editor') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.content-type.*') ? 'active' : '' }}" href="{{ route('panel.content-type.index') }}">{{ __('FsLang::panel.sidebar_extend_content_type') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.content-handler.*') ? 'active' : '' }}" href="{{ route('panel.content-handler.index') }}">{{ __('FsLang::panel.sidebar_extend_content_handler') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.manage.*') ? 'active' : '' }}" href="{{ route('panel.manage.index') }}">{{ __('FsLang::panel.sidebar_extend_manage') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.group.*') ? 'active' : '' }}" href="{{ route('panel.group.index') }}">{{ __('FsLang::panel.sidebar_extend_group') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.user-feature.*') ? 'active' : '' }}" href="{{ route('panel.user-feature.index') }}">{{ __('FsLang::panel.sidebar_extend_user_feature') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.user-profile.*') ? 'active' : '' }}" href="{{ route('panel.user-profile.index') }}">{{ __('FsLang::panel.sidebar_extend_user_profile') }}</a></li>
                </ul>
            </div>
        </div>
        <div class="fresns-copyright d-none d-lg-block">Powered by Fresns</div>
    </nav>
</div>
