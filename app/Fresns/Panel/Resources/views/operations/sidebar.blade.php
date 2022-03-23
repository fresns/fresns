<div class="col-lg-2 fresns-sidebar mt-3 mt-lg-0">
    <nav class="navbar navbar-expand-lg navbar-light flex-lg-column shadow" style="background-color:#e3f2fd;">
        <div class="container-fluid d-lg-flex flex-lg-column">
            <span class="navbar-brand">{{ __('FsLang::panel.menu_operations') }}</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav d-flex flex-column">
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.rename.*') ? 'active' : '' }}" href="{{ route('panel.rename.index') }}">{{ __('FsLang::panel.sidebar_rename') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.interactive.*') ? 'active' : '' }}" href="{{ route('panel.interactive.index') }}">{{ __('FsLang::panel.sidebar_interaction') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.stickers.*') ? 'active' : '' }}" href="{{ route('panel.stickers.index') }}">{{ __('FsLang::panel.sidebar_stickers') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.publish.post.*', 'panel.publish.comment.*') ? 'active' : '' }}" href="{{ route('panel.publish.post.index') }}">{{ __('FsLang::panel.sidebar_publish') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.block-words.*') ? 'active' : '' }}" href="{{ route('panel.block-words.index') }}">{{ __('FsLang::panel.sidebar_block_words') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.roles.*') ? 'active' : '' }}" href="{{ route('panel.roles.index') }}">{{ __('FsLang::panel.sidebar_roles') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ \Route::is('panel.groups.*') ? 'active' : '' }}" href="{{ route('panel.groups.index') }}">{{ __('FsLang::panel.sidebar_groups') }}</a></li>
                    <li class="nav-item d-block d-lg-none my-3 text-secondary">Powered by Fresns</li>
                </ul>
            </div>
        </div>
        <div class="fresns-copyright d-none d-lg-block">Powered by Fresns</div>
    </nav>
</div>
